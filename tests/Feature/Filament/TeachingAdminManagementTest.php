<?php

use App\Filament\Resources\CertificateEligibilities\Pages\ListCertificateEligibilities;
use App\Filament\Resources\ExamAttempts\Pages\EditExamAttempt;
use App\Filament\Resources\ExamAttempts\RelationManagers\AnswersRelationManager;
use App\Filament\Resources\Meetings\Pages\CreateMeeting;
use App\Filament\Resources\Meetings\Pages\EditMeeting;
use App\Filament\Resources\Meetings\Pages\ListMeetings;
use App\Filament\Resources\Meetings\RelationManagers\AttendeesRelationManager;
use App\Filament\Resources\QuestionBankQuestions\Pages\CreateQuestionBankQuestion;
use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\GradeBook;
use App\Models\Attendance;
use App\Models\CertificateEligibility;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\Meeting;
use App\Models\QuestionBankQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use App\Notifications\MeetingScheduledNotification;
use App\Services\CertificateService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('loads every teaching admin list and create page', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with([
    'trainings' => '/admin/trainings',
    'create training' => '/admin/trainings/create',
    'exams' => '/admin/exams',
    'create exam' => '/admin/exams/create',
    'submissions' => '/admin/exam-attempts',
    'question bank' => '/admin/questions',
    'create question' => '/admin/questions/create',
    'certificates' => '/admin/certificates',
]);

it('loads the training edit and grade book pages', function () {
    $training = Training::factory()->create();

    $this->get("/admin/trainings/{$training->id}/edit")->assertSuccessful();
    $this->get("/admin/trainings/{$training->id}/grade-book")->assertSuccessful();
});

it('loads the exam edit page', function () {
    $exam = Exam::factory()->create();

    $this->get("/admin/exams/{$exam->id}/edit")->assertSuccessful();
});

it('creates a training with an uploaded thumbnail', function () {
    Storage::fake('public');

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Advanced Blue Team Operations',
            'category' => 'network_security',
            'description' => 'Hands-on defensive security training.',
            'difficulty' => 'advanced',
            'duration_hours' => 8,
            'instructor_id' => $this->admin->id,
            'is_published' => true,
            'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 800, 600),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $training = Training::where('title', 'Advanced Blue Team Operations')->first();

    expect($training)->not->toBeNull()
        ->and($training->thumbnail)->not->toBeNull();

    Storage::disk('public')->assertExists($training->thumbnail);
});

it('creates a multiple choice question with options and an image', function () {
    Storage::fake('public');

    Livewire::test(CreateQuestionBankQuestion::class)
        ->fillForm([
            'type' => 'multiple_choice',
            'marks' => 5,
            'question_text' => 'Which header mitigates XSS?',
            'image' => UploadedFile::fake()->image('question.png', 600, 400),
        ])
        ->set('data.options', [
            'option-a' => ['option_text' => 'Content-Security-Policy', 'is_correct' => true],
            'option-b' => ['option_text' => 'X-Powered-By', 'is_correct' => false],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $question = QuestionBankQuestion::where('question_text', 'Which header mitigates XSS?')->first();

    expect($question)->not->toBeNull()
        ->and($question->type)->toBe('multiple_choice')
        ->and($question->user_id)->toBe($this->admin->id)
        ->and($question->options()->count())->toBe(2)
        ->and($question->image)->not->toBeNull();

    Storage::disk('public')->assertExists($question->image);
});

it('creates a true or false question with generated options', function () {
    Livewire::test(CreateQuestionBankQuestion::class)
        ->fillForm([
            'type' => 'true_false',
            'marks' => 2,
            'question_text' => 'TLS provides confidentiality.',
            'correct_answer' => 'true',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $question = QuestionBankQuestion::where('question_text', 'TLS provides confidentiality.')->first();

    expect($question)->not->toBeNull()
        ->and($question->type)->toBe('true_false')
        ->and($question->options()->count())->toBe(2)
        ->and($question->options()->where('is_correct', true)->first()->option_text)->toBe('True');
});

it('loads the exam attempt review page and grades an answer manually', function () {
    $question = QuestionBankQuestion::factory()->shortAnswer()->create(['marks' => 10]);
    $exam = Exam::factory()->create(['passing_score' => 50]);
    $examQuestion = ExamQuestion::factory()->create([
        'exam_id' => $exam->id,
        'question_bank_question_id' => $question->id,
        'custom_marks' => null,
    ]);

    $attempt = ExamAttempt::factory()->completed()->create([
        'exam_id' => $exam->id,
        'total_score' => 0,
    ]);

    $answer = ExamAnswer::factory()->pendingGrading()->create([
        'exam_attempt_id' => $attempt->id,
        'exam_question_id' => $examQuestion->id,
        'answer_text' => 'Confidentiality, integrity and availability.',
    ]);

    Livewire::test(EditExamAttempt::class, ['record' => $attempt->getRouteKey()])
        ->assertSuccessful();

    Livewire::test(AnswersRelationManager::class, [
        'ownerRecord' => $attempt,
        'pageClass' => EditExamAttempt::class,
    ])
        ->callTableAction('edit', $answer, data: [
            'marks_awarded' => 8,
            'is_correct' => true,
        ])
        ->assertHasNoTableActionErrors();

    $answer->refresh();

    expect($answer->marks_awarded)->toBe(8)
        ->and($answer->is_correct)->toBeTrue();

    Livewire::test(EditExamAttempt::class, ['record' => $attempt->getRouteKey()])
        ->callAction('recalculate')
        ->assertHasNoActionErrors();

    expect($attempt->refresh()->total_score)->toBe(8)
        ->and($attempt->passed)->toBeTrue();
});

it('saves a score through the grade book', function () {
    $training = Training::factory()->create();
    $member = User::factory()->create();

    $enrollment = TrainingEnrollment::create([
        'training_id' => $training->id,
        'user_id' => $member->id,
        'status' => 'enrolled',
        'progress_percentage' => 0,
        'enrolled_at' => now(),
        'score' => null,
    ]);

    Livewire::test(GradeBook::class, ['record' => $training->id])
        ->assertSuccessful()
        ->callTableAction('update_score', $enrollment, data: [
            'score' => 88,
        ])
        ->assertHasNoTableActionErrors();

    expect($enrollment->refresh()->score)->toBe(88);
});

it('downloads a certificate pdf from the admin panel', function () {
    $attempt = ExamAttempt::factory()->passed()->create();
    $eligibility = CertificateEligibility::factory()->create([
        'exam_attempt_id' => $attempt->id,
        'user_id' => $attempt->user_id,
        'exam_id' => $attempt->exam_id,
        'eligible' => true,
    ]);

    $response = app(CertificateService::class)->downloadPdf($eligibility);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/pdf');
});

it('revokes a certificate from the admin panel', function () {
    $eligibility = CertificateEligibility::factory()->create(['eligible' => true]);

    Livewire::test(ListCertificateEligibilities::class)
        ->callTableAction('revoke', $eligibility)
        ->assertHasNoTableActionErrors();

    expect($eligibility->refresh()->eligible)->toBeFalse();
});

it('creates a recurring teaching session with a recurrence rule', function () {
    Livewire::test(CreateMeeting::class)
        ->fillForm([
            'title' => 'Weekly Linux Class',
            'type' => 'teaching_session',
            'scheduled_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'duration_minutes' => 90,
            'created_by' => $this->admin->id,
            'is_recurring' => true,
            'recurrence_pattern' => 'weekly',
            'recurrence_interval' => 1,
            'recurrence_ends_at' => now()->addMonths(2)->format('Y-m-d'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $meeting = Meeting::where('title', 'Weekly Linux Class')->first();

    expect($meeting)->not->toBeNull()
        ->and($meeting->is_recurring)->toBeTrue();

    $recurrence = $meeting->recurrence()->first();

    expect($recurrence)->not->toBeNull()
        ->and($recurrence->pattern)->toBe('weekly')
        ->and($recurrence->interval)->toBe(1)
        ->and($recurrence->ends_at)->not->toBeNull();
});

it('records past attendance from the meeting edit page', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'attendance_open' => false,
        'ended_at' => null,
    ]);
    $member = User::factory()->create();

    Livewire::test(EditMeeting::class, ['record' => $meeting->getRouteKey()])
        ->callAction('backfill_attendance', data: [
            'present_ids' => [$member->id],
            'late_ids' => [],
        ])
        ->assertHasNoActionErrors();

    expect($meeting->fresh()->attendance_open)->toBeFalse()
        ->and($meeting->fresh()->ended_at)->not->toBeNull();

    $this->assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $member->id,
        'status' => 'present',
    ]);
});

it('records attendance while creating a past class', function () {
    $member = User::factory()->create();

    Livewire::test(CreateMeeting::class)
        ->fillForm([
            'title' => 'Last Week Linux Class',
            'type' => 'teaching_session',
            'scheduled_at' => now()->subWeek()->format('Y-m-d H:i:s'),
            'created_by' => $this->admin->id,
            'is_recurring' => false,
            'present_ids' => [$member->id],
            'late_ids' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $meeting = Meeting::where('title', 'Last Week Linux Class')->first();

    expect($meeting)->not->toBeNull()
        ->and($meeting->attendance_open)->toBeFalse()
        ->and($meeting->ended_at)->not->toBeNull();

    $this->assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $member->id,
        'status' => 'present',
    ]);
});

it('bulk-selects year one members on the create attendance picker', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    Livewire::test(CreateMeeting::class)
        ->fillForm([
            'title' => 'Year One Catch-up Class',
            'type' => 'teaching_session',
            'scheduled_at' => now()->subWeek()->format('Y-m-d H:i:s'),
            'created_by' => $this->admin->id,
            'is_recurring' => false,
        ])
        ->callFormComponentAction('present_ids', 'present_ids_year_1')
        ->call('create')
        ->assertHasNoFormErrors();

    $meeting = Meeting::where('title', 'Year One Catch-up Class')->first();

    expect($meeting)->not->toBeNull();

    $this->assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $yearOne->id,
        'status' => 'present',
    ]);

    expect(Attendance::where('meeting_id', $meeting->id)->where('user_id', $yearThree->id)->exists())->toBeFalse();
});

it('changes an attendee status from the attendees tab', function () {
    $meeting = Meeting::factory()->teachingSession()->create(['created_by' => $this->admin->id]);
    $member = User::factory()->create();
    $attendance = Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $member->id,
        'status' => 'present',
        'checked_in_at' => now()->subDay(),
    ]);

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $meeting,
        'pageClass' => EditMeeting::class,
    ])
        ->callTableAction('change_status', $attendance, data: ['status' => 'late'])
        ->assertHasNoTableActionErrors();

    expect($attendance->fresh()->status)->toBe('late');
});

it('duplicates a meeting for another day from the meetings list', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'title' => 'Bash Basics',
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
    ]);

    Livewire::test(ListMeetings::class)
        ->callTableAction('reuse_for_another_day', $meeting, data: [
            'scheduled_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        ])
        ->assertHasNoTableActionErrors();

    $clone = Meeting::where('id', '!=', $meeting->id)->where('title', 'Bash Basics')->first();

    expect($clone)->not->toBeNull()
        ->and($clone->scheduled_at->isFuture())->toBeTrue()
        ->and($clone->meeting_code)->not->toBe($meeting->meeting_code);
});

it('notifies only the targeted audience when a class is scheduled', function () {
    Notification::fake();

    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    Livewire::test(CreateMeeting::class)
        ->fillForm([
            'title' => 'Year One Intro Class',
            'type' => 'teaching_session',
            'scheduled_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'created_by' => $this->admin->id,
            'is_recurring' => false,
            'audience' => 'year_1',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($yearOne, MeetingScheduledNotification::class);
    Notification::assertNotSentTo($yearThree, MeetingScheduledNotification::class);
});

it('does not create a recurrence rule for one-off meetings', function () {
    Livewire::test(CreateMeeting::class)
        ->fillForm([
            'title' => 'One-off Workshop',
            'type' => 'workshop',
            'scheduled_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'created_by' => $this->admin->id,
            'is_recurring' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $meeting = Meeting::where('title', 'One-off Workshop')->first();

    expect($meeting)->not->toBeNull()
        ->and($meeting->recurrence()->count())->toBe(0);
});
