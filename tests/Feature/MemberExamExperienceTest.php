<?php

use App\Livewire\ExamResult;
use App\Livewire\ExamTake;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBankQuestion;
use App\Models\User;
use App\Notifications\ExamGradedNotification;
use App\Notifications\ExamPublishedNotification;
use App\Services\ExamAttemptService;
use App\Services\ExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function memberExamMember(): User
{
    $user = User::factory()->create([
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(2),
    ]);

    $user->assignRole(Role::firstOrCreate(['name' => 'member']));

    return $user;
}

function memberExamWithQuestions(int $count = 1, array $overrides = []): Exam
{
    $creator = User::factory()->create();
    Role::firstOrCreate(['name' => 'admin']);
    $creator->assignRole('admin');

    $exam = Exam::factory()->published()->create(array_merge([
        'user_id' => $creator->id,
        'duration_minutes' => 60,
        'passing_score' => 50,
    ], $overrides));

    for ($i = 0; $i < $count; $i++) {
        $question = QuestionBankQuestion::factory()->multipleChoice()->create([
            'user_id' => $creator->id,
            'marks' => 10,
        ]);

        $exam->examQuestions()->create([
            'question_bank_question_id' => $question->id,
            'order' => $i,
            'custom_marks' => 10,
        ]);
    }

    return $exam;
}

// ─── Access ───────────────────────────────────────────────

it('shows published exams to members and hides drafts', function () {
    $member = memberExamMember();

    $published = memberExamWithQuestions(1);
    $draft = Exam::factory()->create(['status' => 'draft']);

    $this->actingAs($member)
        ->get(route('exams.index'))
        ->assertSuccessful()
        ->assertSee($published->title)
        ->assertDontSee($draft->title);
});

it('lets a member take a published exam', function () {
    $member = memberExamMember();
    $exam = memberExamWithQuestions(1);

    Livewire::actingAs($member)
        ->test(ExamTake::class, ['exam' => $exam])
        ->assertOk();
});

it('blocks members from taking a draft exam', function () {
    $member = memberExamMember();
    $draft = Exam::factory()->create(['status' => 'draft']);

    $this->actingAs($member)
        ->get(route('exams.take', $draft))
        ->assertForbidden();
});

it('blocks members from taking an archived exam', function () {
    $member = memberExamMember();
    $archived = Exam::factory()->create(['status' => 'archived']);

    $this->actingAs($member)
        ->get(route('exams.take', $archived))
        ->assertForbidden();
});

// ─── Notifications ───────────────────────────────────────

it('notifies members when a draft exam is published', function () {
    Notification::fake();

    $member = memberExamMember();
    $exam = Exam::factory()->create(['status' => 'draft']);

    $exam->update(['status' => 'published']);

    Notification::assertSentTo($member, ExamPublishedNotification::class, 1);
});

it('notifies members when an exam is created as published', function () {
    Notification::fake();

    $member = memberExamMember();

    Exam::factory()->published()->create();

    Notification::assertSentTo($member, ExamPublishedNotification::class, 1);
});

it('does not re-notify when a published exam is edited', function () {
    Notification::fake();

    memberExamMember();
    $exam = Exam::factory()->published()->create();
    Notification::assertSentTo(
        User::role('member')->first(),
        ExamPublishedNotification::class,
        1
    );

    $exam->update(['title' => 'Updated title']);

    Notification::assertSentTo(
        User::role('member')->first(),
        ExamPublishedNotification::class,
        1
    );
});

it('sends exactly one published notification through the service toggle', function () {
    Notification::fake();

    $member = memberExamMember();
    $exam = Exam::factory()->create(['status' => 'draft']);

    app(ExamService::class)->toggleStatus($exam);

    Notification::assertSentTo($member, ExamPublishedNotification::class, 1);
});

// ─── Taking & submitting ─────────────────────────────────

it('lets a member answer and submit an exam end to end', function () {
    Notification::fake();

    $member = memberExamMember();
    $exam = memberExamWithQuestions(1);
    $question = $exam->questions()->first();
    $correctOption = $question->question->options()->where('is_correct', true)->first();

    Livewire::actingAs($member)
        ->test(ExamTake::class, ['exam' => $exam])
        ->call('saveAnswer', $question->id, null, $correctOption->id)
        ->assertSet('answers', [(string) $question->id => ['text' => null, 'option_id' => $correctOption->id]])
        ->call('submitExam')
        ->assertRedirect();

    $attempt = ExamAttempt::where('exam_id', $exam->id)
        ->where('user_id', $member->id)
        ->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->is_completed)->toBeTrue()
        ->and($attempt->total_score)->toBe(10)
        ->and($attempt->passed)->toBeTrue();

    Notification::assertSentTo($member, ExamGradedNotification::class, 1);
});

it('redirects a member to their result once already submitted', function () {
    $member = memberExamMember();
    $exam = memberExamWithQuestions(1);

    $attempt = app(ExamAttemptService::class)->startAttempt($exam, $member);
    app(ExamAttemptService::class)->submitAttempt($attempt);

    Livewire::actingAs($member)
        ->test(ExamTake::class, ['exam' => $exam])
        ->assertRedirect(route('exams.result', $attempt->id));
});

it('shows the result to the owner and hides it from others', function () {
    $owner = memberExamMember();
    $other = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);

    $exam = memberExamWithQuestions(1);
    $attempt = ExamAttempt::factory()->completed()->create([
        'exam_id' => $exam->id,
        'user_id' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(ExamResult::class, ['attempt' => $attempt])
        ->assertOk();

    $this->actingAs($other)
        ->get(route('exams.result', $attempt))
        ->assertForbidden();
});

// ─── Timer ───────────────────────────────────────────────

it('counts down the remaining time from when the attempt started', function () {
    $member = memberExamMember();
    $exam = memberExamWithQuestions(1, ['duration_minutes' => 60]);

    $attempt = app(ExamAttemptService::class)->startAttempt($exam, $member);
    $service = app(ExamAttemptService::class);

    expect($service->remainingSeconds($attempt))
        ->toBeGreaterThanOrEqual(3595)
        ->toBeLessThanOrEqual(3600);

    $this->travel(120)->seconds();

    expect($service->remainingSeconds($attempt->fresh()))
        ->toBeGreaterThanOrEqual(3474)
        ->toBeLessThanOrEqual(3480);

    $this->travel(3600)->seconds();

    expect($service->remainingSeconds($attempt->fresh()))->toBe(0);
});

it('auto-submits an expired attempt', function () {
    $member = memberExamMember();
    $exam = memberExamWithQuestions(1, ['duration_minutes' => 1]);

    $attempt = app(ExamAttemptService::class)->startAttempt($exam, $member);

    $this->travel(120)->seconds();

    app(ExamAttemptService::class)->submitAttempt($attempt);

    expect($attempt->fresh()->is_completed)->toBeTrue();
});

it('blocks saving an answer after the timer expires', function () {
    $member = memberExamMember();
    $exam = memberExamWithQuestions(1, ['duration_minutes' => 1]);

    $service = app(ExamAttemptService::class);
    $attempt = $service->startAttempt($exam, $member);
    $question = $exam->questions()->first();
    $correctOption = $question->question->options()->where('is_correct', true)->first();

    $this->travel(120)->seconds();

    expect(fn () => $service->saveAnswer($attempt, $question, [
        'selected_option_id' => $correctOption->id,
    ]))->toThrow(RuntimeException::class);
});
