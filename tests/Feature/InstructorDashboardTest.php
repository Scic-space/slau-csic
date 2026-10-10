<?php

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Meeting;
use App\Models\TeachingMaterial;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('renders the instructor dashboard for authenticated user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertOk();
});

it('forbids members without teaching permission from the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $this->actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertForbidden();
});

it('forbids members from the teaching components directly', function (string $component) {
    $user = User::factory()->create();
    $user->assignRole('member');

    Livewire::actingAs($user)
        ->test($component)
        ->assertForbidden();
})->with([
    \App\Livewire\InstructorDashboard::class,
    \App\Livewire\InstructorTrainings::class,
    \App\Livewire\InstructorSessions::class,
    \App\Livewire\InstructorMaterials::class,
]);

it('redirects guests from instructor dashboard', function () {
    $this->get(route('instructor.dashboard'))
        ->assertRedirect('/auth/login');
});

it('shows stats on instructor dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $training = Training::factory()->create(['instructor_id' => $user->id]);

    actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertOk()
        ->assertSee('Teaching Dashboard')
        ->assertSee('Trainings');
});

it('renders the instructor trainings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user)
        ->get(route('instructor.trainings'))
        ->assertOk()
        ->assertSee('My Trainings');
});

it('only shows trainings for the current instructor', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $myTraining = Training::factory()->create(['instructor_id' => $user->id, 'title' => 'My Training']);
    $otherTraining = Training::factory()->create(['title' => 'Other Training']);

    actingAs($user)
        ->get(route('instructor.trainings'))
        ->assertSee('My Training')
        ->assertDontSee('Other Training');
});

it('renders the instructor sessions page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user)
        ->get(route('instructor.sessions'))
        ->assertOk()
        ->assertSee('Teaching Sessions');
});

it('only shows sessions created by the current instructor', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $mySession = Meeting::factory()->create([
        'type' => 'teaching_session',
        'created_by' => $user->id,
        'title' => 'My Session',
    ]);
    $otherUser = User::factory()->create();

    $otherSession = Meeting::factory()->create([
        'type' => 'teaching_session',
        'created_by' => $otherUser->id,
        'title' => 'Other Session',
    ]);

    actingAs($user);

    $sessions = \App\Models\Meeting::teachingSessions()
        ->where('created_by', $user->id)
        ->pluck('title');

    expect($sessions)->toHaveCount(1);
    expect($sessions)->toContain('My Session');
    expect($sessions)->not->toContain('Other Session');
});

it('renders the instructor materials page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user)
        ->get(route('instructor.materials'))
        ->assertOk()
        ->assertSee('Course Materials');
});

it('creates a teaching material via livewire', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('openCreateForm')
        ->set('formTitle', 'Test Material')
        ->set('formType', 'document')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teaching_materials', [
        'title' => 'Test Material',
        'uploaded_by' => $user->id,
    ]);
});

it('validates required fields when creating material', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('openCreateForm')
        ->set('formTitle', '')
        ->call('save')
        ->assertHasErrors(['formTitle']);
});

it('deletes a teaching material via livewire', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $material = TeachingMaterial::create([
        'title' => 'To Delete',
        'type' => 'document',
        'uploaded_by' => $user->id,
    ]);

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('delete', $material->id);

    $this->assertDatabaseMissing('teaching_materials', ['id' => $material->id]);
});

it('searches materials by title', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    TeachingMaterial::create(['title' => 'Python Basics', 'type' => 'document', 'uploaded_by' => $user->id]);
    TeachingMaterial::create(['title' => 'Java Guide', 'type' => 'document', 'uploaded_by' => $user->id]);

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->set('search', 'Python')
        ->assertSee('Python Basics')
        ->assertDontSee('Java Guide');
});

it('counts exams and submitted attempts on the teaching dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $exam = Exam::factory()->published()->create(['user_id' => $user->id]);

    ExamAttempt::factory()->completed()->create(['exam_id' => $exam->id]);

    otherInstructorExamAttempt();

    Livewire::actingAs($user)
        ->test(\App\Livewire\InstructorDashboard::class)
        ->assertViewHas('stats', fn ($stats) => $stats['exams'] === 1 && $stats['exam_attempts'] === 1)
        ->assertSee('Total Exams')
        ->assertSee('Exam Attempts');
});

it('shows global teaching counts for admins who did not author the content', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $instructor = User::factory()->create();
    $exam = Exam::factory()->published()->create(['user_id' => $instructor->id]);
    ExamAttempt::factory()->completed()->create(['exam_id' => $exam->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\InstructorDashboard::class)
        ->assertViewHas('stats', fn ($stats) => $stats['exams'] === 1 && $stats['exam_attempts'] === 1);
});

it('counts exams with ungraded short answers as pending grading', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $exam = Exam::factory()->published()->create(['user_id' => $user->id]);

    $question = \App\Models\QuestionBankQuestion::factory()->shortAnswer()->create(['user_id' => $user->id]);

    $examQuestion = $exam->examQuestions()->create([
        'question_bank_question_id' => $question->id,
        'order' => 0,
        'custom_marks' => 10,
    ]);

    $attempt = ExamAttempt::factory()->completed()->create(['exam_id' => $exam->id]);
    $attempt->answers()->create([
        'exam_question_id' => $examQuestion->id,
        'answer_text' => 'Use a password manager.',
        'is_correct' => null,
        'marks_awarded' => 0,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\InstructorDashboard::class)
        ->assertViewHas('stats', fn ($stats) => $stats['pending_exam_grading'] === 1)
        ->assertSee('Pending Grading');
});

it('graded short answers are not counted as pending grading', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('teacher.dashboard.view');

    $exam = Exam::factory()->published()->create(['user_id' => $user->id]);

    $question = \App\Models\QuestionBankQuestion::factory()->shortAnswer()->create(['user_id' => $user->id]);

    $examQuestion = $exam->examQuestions()->create([
        'question_bank_question_id' => $question->id,
        'order' => 0,
        'custom_marks' => 10,
    ]);

    $attempt = ExamAttempt::factory()->completed()->create(['exam_id' => $exam->id]);
    $attempt->answers()->create([
        'exam_question_id' => $examQuestion->id,
        'answer_text' => 'Use a password manager.',
        'is_correct' => true,
        'marks_awarded' => 10,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\InstructorDashboard::class)
        ->assertViewHas('stats', fn ($stats) => $stats['pending_exam_grading'] === 0);
});

function otherInstructorExamAttempt(): void
{
    $other = User::factory()->create();
    $exam = Exam::factory()->published()->create(['user_id' => $other->id]);
    ExamAttempt::factory()->completed()->create(['exam_id' => $exam->id]);
}
