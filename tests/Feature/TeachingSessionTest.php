<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\Meeting;
use App\Models\MeetingFeedback;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('shows a teaching session detail page to attendees', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'title' => 'Network Security Fundamentals',
        'scheduled_at' => now()->subDay(),
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now()->subDay(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Network Security Fundamentals')
        ->assertSee('Teaching Session');
});

it('shows upcoming classes to members who have not attended yet', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->addWeek(),
        'ended_at' => null,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk();
});

it('forbids the completed class page for members who did not attend', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subWeek(),
        'ended_at' => now()->subWeek(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertForbidden();
});

it('uses browser history for the back button with a dashboard fallback', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->addWeek(),
        'ended_at' => null,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('history.back', false)
        ->assertDontSee('url()->previous()', false);
});

it('allows the creator and admins to view any class page', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->user->id,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk();

    $admin = User::factory()->create();
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole($adminRole);

    actingAs($admin)
        ->get(route('classes.show', $meeting->id))
        ->assertOk();
});

it('allows the assigned instructor to view the completed class page', function () {
    $instructor = User::factory()->create();
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subWeek(),
        'ended_at' => now()->subWeek(),
        'instructor_id' => $instructor->id,
    ]);

    actingAs($instructor)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee($instructor->name);
});

it('renders the rich description as formatted text', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'description' => '<p><strong>Topic:</strong> Bash</p>',
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('<strong>Topic:</strong> Bash', false);
});

it('shows the average rating once feedback exists', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
    ]);
    MeetingFeedback::create(['meeting_id' => $meeting->id, 'user_id' => $this->user->id, 'rating' => 5]);
    MeetingFeedback::create(['meeting_id' => $meeting->id, 'user_id' => User::factory()->create()->id, 'rating' => 4]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('4.5 (2)');
});

it('lets an absent member request an attendance correction once', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole($adminRole);

    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subWeek(),
        'ended_at' => now()->subWeek(),
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'absent',
        'is_auto_absent' => true,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TeachingSessionShow::class, ['meeting' => $meeting])
        ->call('toggleCorrectionForm')
        ->set('correctionMessage', 'I sat at the back.')
        ->call('requestCorrection')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('attendance_correction_requests', [
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);

    Notification::assertSentTo($admin, \App\Notifications\AttendanceCorrectionRequestedNotification::class);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TeachingSessionShow::class, ['meeting' => $meeting])
        ->call('requestCorrection')
        ->assertHasNoErrors();

    expect(AttendanceCorrectionRequest::where('meeting_id', $meeting->id)->where('user_id', $this->user->id)->count())->toBe(1);
});

it('submits session feedback through the star rating', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TeachingSessionShow::class, ['meeting' => $meeting])
        ->call('setRating', 4)
        ->set('comment', 'Great class')
        ->call('submitFeedback')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('meeting_feedback', [
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'rating' => 4,
        'comment' => 'Great class',
    ]);
});

it('shows attendance status when user attended', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
        'check_in_method' => 'qr_code',
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('You attended this session');
});

it('shows the correction option to members marked absent', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subWeek(),
        'ended_at' => now()->subWeek(),
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'absent',
        'is_auto_absent' => true,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('I was there');
});

it('shows linked training when meeting has one', function () {
    $training = Training::factory()->published()->create(['title' => 'Ethical Hacking']);
    $meeting = Meeting::factory()->teachingSession()->create([
        'training_id' => $training->id,
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Linked Training')
        ->assertSee('Ethical Hacking');
});

it('returns 404 for non-teaching session meetings', function () {
    $meeting = Meeting::factory()->create(['type' => 'general']);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertNotFound();
});

it('shows agenda items when present', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    $meeting->agendaItems()->create([
        'title' => 'Introduction to Kali Linux',
        'duration_minutes' => 30,
        'sort_order' => 1,
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Agenda')
        ->assertSee('Introduction to Kali Linux');
});
