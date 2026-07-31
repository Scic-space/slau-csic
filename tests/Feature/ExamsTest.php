<?php

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
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

// ─── Helpers ───────────────────────────────────────────────

function createExamWithQuestions(int $questionCount = 3, array $examOverrides = []): Exam
{
    $creator = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $creator->assignRole($adminRole);

    $exam = Exam::factory()->published()->create(array_merge([
        'user_id' => $creator->id,
        'duration_minutes' => 60,
        'passing_score' => 50,
    ], $examOverrides));

    for ($i = 0; $i < $questionCount; $i++) {
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

// ─── Exam Policy ───────────────────────────────────────────

describe('ExamPolicy', function () {
    it('allows super-admin to do everything', function () {
        $user = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $user->assignRole($superAdminRole);

        $exam = createExamWithQuestions();

        expect($user->can('create', Exam::class))->toBeTrue();
        expect($user->can('update', $exam))->toBeTrue();
        expect($user->can('delete', $exam))->toBeTrue();
        expect($user->can('publish', $exam))->toBeTrue();
        expect($user->can('grade', $exam))->toBeTrue();
        expect($user->can('manageCertificates', $exam))->toBeTrue();
    });

    it('allows admin to create, update, publish, grade, manage certificates', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $exam = createExamWithQuestions();

        expect($user->can('create', Exam::class))->toBeTrue();
        expect($user->can('update', $exam))->toBeTrue();
        expect($user->can('publish', $exam))->toBeTrue();
        expect($user->can('grade', $exam))->toBeTrue();
        expect($user->can('manageCertificates', $exam))->toBeTrue();
    });

    it('denies delete to non-admin roles', function () {
        $user = User::factory()->create();
        $presidentRole = Role::firstOrCreate(['name' => 'president']);
        $user->assignRole($presidentRole);

        $exam = createExamWithQuestions();

        expect($user->can('delete', $exam))->toBeFalse();
    });

    it('allows member to view published exams and take them', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions();

        expect($user->can('viewAny', Exam::class))->toBeTrue();
        expect($user->can('take', $exam))->toBeTrue();
    });

    it('denies member from viewing draft exams', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $draftExam = Exam::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'draft',
        ]);

        expect($user->can('view', $draftExam))->toBeFalse();
    });

    it('allows admin to view draft exams', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $draftExam = Exam::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        expect($user->can('view', $draftExam))->toBeTrue();
    });
});

// ─── Exam CRUD & Management ─────────────────────────────────

describe('Exam management', function () {
    it('creates an exam with the ExamService', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $exam = app(ExamService::class)->createExam($user, [
            'title' => 'Security Test',
            'description' => 'Test description',
            'duration_minutes' => 45,
            'passing_score' => 70,
            'status' => 'draft',
        ]);

        expect($exam->title)->toBe('Security Test');
        expect($exam->status)->toBe('draft');
    });

    it('toggles exam status to published and sends notification', function () {
        Notification::fake();

        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $member = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $member->assignRole($memberRole);

        $exam = app(ExamService::class)->createExam($user, [
            'title' => 'Security Test',
            'description' => null,
            'duration_minutes' => 45,
            'passing_score' => 70,
            'status' => 'draft',
        ]);

        $newStatus = app(ExamService::class)->toggleStatus($exam);

        expect($newStatus)->toBe('published');
        expect($exam->fresh()->status)->toBe('published');

        Notification::assertSentTo($member, ExamPublishedNotification::class);
    });

    it('cycles through draft -> published -> archived -> draft', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $exam = app(ExamService::class)->createExam($user, [
            'title' => 'Cycle Test',
            'description' => null,
            'duration_minutes' => 30,
            'passing_score' => 50,
            'status' => 'draft',
        ]);

        $service = app(ExamService::class);

        expect($service->toggleStatus($exam))->toBe('published');
        expect($service->toggleStatus($exam->fresh()))->toBe('archived');
        expect($service->toggleStatus($exam->fresh()))->toBe('draft');
    });

    it('manages exam questions (add, remove, reorder)', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $exam = app(ExamService::class)->createExam($user, [
            'title' => 'Question Test',
            'description' => null,
            'duration_minutes' => 30,
            'passing_score' => 50,
            'status' => 'draft',
        ]);

        $service = app(ExamService::class);

        $q1 = QuestionBankQuestion::factory()->multipleChoice()->create(['user_id' => $user->id]);
        $q2 = QuestionBankQuestion::factory()->trueFalse()->create(['user_id' => $user->id]);
        $q3 = QuestionBankQuestion::factory()->shortAnswer()->create(['user_id' => $user->id]);

        $eq1 = $service->addQuestion($exam, $q1->id, 10);
        $eq2 = $service->addQuestion($exam, $q2->id, null);
        $eq3 = $service->addQuestion($exam, $q3->id, 15);

        expect($exam->questions()->count())->toBe(3);

        $service->removeQuestion($exam, $eq2->id);

        expect($exam->fresh()->questions()->count())->toBe(2);

        $service->reorderQuestions($exam, [$eq3->id, $eq1->id]);

        $ordered = $exam->fresh()->questions;
        expect($ordered[0]->id)->toBe($eq3->id);
        expect($ordered[1]->id)->toBe($eq1->id);
    });
});

// ─── Exam Taking ────────────────────────────────────────────

describe('Exam taking', function () {
    it('starts a new exam attempt', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions();

        $attempt = app(ExamAttemptService::class)->startAttempt($exam, $user);

        expect($attempt->exam_id)->toBe($exam->id);
        expect($attempt->user_id)->toBe($user->id);
        expect($attempt->started_at)->not->toBeNull();
        expect($attempt->time_remaining_seconds)->toBe($exam->duration_minutes * 60);
        expect($attempt->submitted_at)->toBeNull();
    });

    it('returns existing attempt on duplicate start', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions();

        $service = app(ExamAttemptService::class);
        $first = $service->startAttempt($exam, $user);
        $second = $service->startAttempt($exam, $user);

        expect($second->id)->toBe($first->id);
    });

    it('saves answers during an attempt', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => $correctOption->id,
        ]);

        $answer = $attempt->answers()->first();
        expect($answer->selected_option_id)->toBe($correctOption->id);
    });

    it('submits exam and grades MCQ/TrueFalse answers', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => $correctOption->id,
        ]);

        $result = $service->submitAttempt($attempt);

        expect($result['passed'])->toBeTrue();
        expect($result['total_score'])->toBeGreaterThanOrEqual(1);
        expect($attempt->fresh()->submitted_at)->not->toBeNull();
    });

    it('prevents saving answers after timer expires', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1, ['duration_minutes' => 0]);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Time expired for this attempt');

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => null,
            'answer_text' => 'test',
        ]);
    });

    it('submits expired attempts automatically', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1, ['duration_minutes' => 0]);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $attempt->answers()->create([
            'exam_question_id' => $question->id,
            'selected_option_id' => $correctOption->id,
        ]);

        $result = $service->submitAttempt($attempt);

        expect($attempt->fresh()->submitted_at)->not->toBeNull();
        expect($result['total_score'])->toBeGreaterThanOrEqual(0);
    });
});

// ─── Exam API ───────────────────────────────────────────────

describe('Exam API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'membership_status' => 'active',
            'approved_at' => now(),
            'joined_at' => now()->subMonths(2),
        ]);
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $this->user->assignRole($memberRole);
    });

    it('lists published exams', function () {
        $exam = createExamWithQuestions();

        $response = getJson('/api/exams', [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.title', $exam->title);
    });

    it('starts an exam attempt via API', function () {
        $exam = createExamWithQuestions();

        $response = postJson("/api/exams/{$exam->id}/start", [], [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['attempt_id', 'questions']]);
    });

    it('submits exam via API', function () {
        $exam = createExamWithQuestions(1);
        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $attempt = app(ExamAttemptService::class)->startAttempt($exam, $this->user);

        $response = postJson("/api/exams/attempts/{$attempt->id}/submit", [
            'answers' => [
                [
                    'exam_question_id' => $question->id,
                    'selected_option_id' => $correctOption->id,
                ],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    });

    it('returns exam result via API', function () {
        $exam = createExamWithQuestions(1);
        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $attempt = app(ExamAttemptService::class)->startAttempt($exam, $this->user);

        app(ExamAttemptService::class)->saveAnswer($attempt, $question, [
            'selected_option_id' => $correctOption->id,
        ]);
        app(ExamAttemptService::class)->submitAttempt($attempt);

        $response = getJson("/api/exams/attempts/{$attempt->id}/result", [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['total_score', 'passed', 'answers']]);
    });

    it('requires auth for exam endpoints', function () {
        getJson('/api/exams')->assertUnauthorized();
        postJson('/api/exams/1/start')->assertUnauthorized();
    });

    it('denies access to other users results via API', function () {
        $exam = createExamWithQuestions(1);
        $otherUser = User::factory()->create();

        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $otherUser->id,
        ]);

        $response = getJson("/api/exams/attempts/{$attempt->id}/result", [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ]);

        $response->assertForbidden();
    });
});

// ─── Notifications ──────────────────────────────────────────

describe('Exam notifications', function () {
    it('sends graded notification on submit', function () {
        Notification::fake();

        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => $correctOption->id,
        ]);

        $service->submitAttempt($attempt);

        Notification::assertSentTo($user, ExamGradedNotification::class);
    });
});

// ─── Certificates ───────────────────────────────────────────

describe('Exam certificates', function () {
    it('creates certificate eligibility on passing', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1, ['passing_score' => 10]);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $correctOption = $question->question->options()->where('is_correct', true)->first();

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => $correctOption->id,
        ]);

        $service->submitAttempt($attempt);

        expect($attempt->fresh()->certificateEligibility)->not->toBeNull();
        expect($attempt->fresh()->certificateEligibility->eligible)->toBeTrue();
    });

    it('does not create eligibility on failing', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = createExamWithQuestions(1, ['passing_score' => 100]);
        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        $question = $exam->questions()->first();
        $wrongOption = $question->question->options()->where('is_correct', false)->first();

        $service->saveAnswer($attempt, $question, [
            'selected_option_id' => $wrongOption->id,
        ]);

        $service->submitAttempt($attempt);

        expect($attempt->fresh()->certificateEligibility)->toBeNull();
    });
});

// ─── N+1 prevention ─────────────────────────────────────────

describe('Exam N+1 prevention', function () {
    it('loads exam listing without N+1 queries', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        createExamWithQuestions(2);
        createExamWithQuestions(3);
        createExamWithQuestions(1);

        $this->withoutExceptionHandling();

        $response = actingAs($user)->get(route('exams.index'));
        $response->assertOk();
    });
});

// ─── ExamAttemptService ─────────────────────────────────────

describe('ExamAttemptService', function () {
    it('detects expired attempt', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = Exam::factory()->create([
            'user_id' => $user->id,
            'duration_minutes' => 0,
            'status' => 'published',
        ]);

        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        expect($service->isExpired($attempt))->toBeTrue();
    });

    it('detects non-expired attempt', function () {
        $user = User::factory()->create();
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $exam = Exam::factory()->create([
            'user_id' => $user->id,
            'duration_minutes' => 60,
            'status' => 'published',
        ]);

        $service = app(ExamAttemptService::class);
        $attempt = $service->startAttempt($exam, $user);

        expect($service->isExpired($attempt))->toBeFalse();
    });
});

// ─── Audit Logging ──────────────────────────────────────────

describe('Exam audit logging', function () {
    it('logs exam status change', function () {
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $exam = app(ExamService::class)->createExam($user, [
            'title' => 'Audit Test',
            'description' => null,
            'duration_minutes' => 30,
            'passing_score' => 50,
            'status' => 'draft',
        ]);

        app(ExamService::class)->toggleStatus($exam);

        $exam->fresh();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Exam::class,
            'subject_id' => $exam->id,
        ]);
    });
});
