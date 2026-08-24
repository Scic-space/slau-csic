<?php

use App\Models\Event;
use App\Models\StaffNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Guest-only pages ───────────────────────────────────────────────

it('renders the login page', function () {
    $response = $this->get(route('auth.login'));

    $response->assertInertia(fn ($page) => $page
        ->component('auth/Login')
    );
});

it('redirects authenticated users away from login', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('auth.login'));

    $response->assertRedirect(route('dashboard'));
});

it('renders the register page', function () {
    $response = $this->get(route('auth.register'));

    $response->assertInertia(fn ($page) => $page
        ->component('auth/Register')
        ->where('faculties', fn ($faculties) => collect($faculties)->isNotEmpty()
            && collect($faculties)->every(fn ($faculty) => isset($faculty['name'], $faculty['programs'])))
    );
});

it('renders the verification page for unverified users', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertInertia(fn ($page) => $page
            ->component('auth/VerifyEmail')
            ->where('auth.user.email', $user->email));
});

// ─── Public pages ───────────────────────────────────────────────────

it('renders the events index page', function () {
    Event::factory()->count(3)->create(['status' => 'published', 'is_public' => true]);

    Livewire::test(\App\Livewire\EventListing::class)
        ->assertStatus(200);
});

it('renders the event show page', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $response = $this->get(route('events.show', $event));

    $response->assertSeeLivewire(App\Livewire\EventDetails::class);
});

it('returns 404 for unpublished events on show page', function () {
    $event = Event::factory()->create(['status' => 'draft']);

    $response = $this->get(route('events.show', $event));

    $response->assertNotFound();
});

it('renders the members directory page', function () {
    User::factory()->count(3)->create([
        'membership_status' => 'active',
        'approved_at' => now(),
        'privacy_settings' => ['show_profile' => true],
    ]);

    $this->get(route('members.index'))
        ->assertSuccessful()
        ->assertSeeLivewire(\App\Livewire\MemberDirectory::class)
        ->assertSee('Total Members')
        ->assertSee('person_check')
        ->assertSee('rounded-sm');
});

it('renders the announcements page', function () {
    $this->get(route('announcements.index'))
        ->assertSeeLivewire(\App\Livewire\AnnouncementListing::class);
});

it('renders the my-transactions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('my-transactions'))
        ->assertSeeLivewire(\App\Livewire\MyTransactions::class);
});

// ─── Authenticated pages ────────────────────────────────────────────

it('renders the dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

it('renders the profile edit page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSeeLivewire(\App\Livewire\MemberProfile::class);
});

it('renders the my-events page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\MyEvents::class)
        ->assertStatus(200);
});

it('renders the calendar page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('events.calendar'))
        ->assertSeeLivewire(\App\Livewire\EventCalendar::class);
});

it('renders the attendance calendar page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('attendance.calendar'))
        ->assertSeeLivewire(\App\Livewire\AttendanceCalendar::class);
});

it('renders the competitions index page', function () {
    $this->get(route('competitions.index'))
        ->assertSeeLivewire(\App\Livewire\CompetitionListing::class);
});

it('renders the fines page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('fines.index'))
        ->assertSeeLivewire(\App\Livewire\MyFines::class);
});

it('renders the event edit page for the organizer', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => 'published',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\EventEdit::class, ['event' => $event])
        ->assertStatus(200);
});

it('denies access to event edit page for non-organizers', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\EventEdit::class, ['event' => $event])
        ->assertForbidden();
});

it('renders the exams listing page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\ExamListing::class)
        ->assertStatus(200);
});

it('renders the exams certificates page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\ExamCertificates::class)
        ->assertStatus(200);
});

it('renders the instructor materials page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\InstructorMaterials::class)
        ->assertStatus(200);
});

// ─── Auth redirects ─────────────────────────────────────────────────

it('redirects guests to login from dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from profile', function () {
    $response = $this->get(route('profile.edit'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from my-events', function () {
    $response = $this->get(route('my-events'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from fines', function () {
    $response = $this->get(route('fines.index'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from event edit', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $response = $this->get(route('events.edit', $event));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from exams', function () {
    $response = $this->get(route('exams.index'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from calendar', function () {
    $response = $this->get(route('events.calendar'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from attendance calendar', function () {
    $response = $this->get(route('attendance.calendar'));

    $response->assertRedirect(route('auth.login'));
});

it('redirects guests to login from my-transactions', function () {
    $response = $this->get(route('my-transactions'));

    $response->assertRedirect(route('auth.login'));
});

// ─── Profile update ─────────────────────────────────────────────────

it('updates the user profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->name)->toBe('Updated Name');
});

it('renders the notification center page', function () {
    $user = User::factory()->create();

    StaffNotification::create([
        'staff_id' => $user->id,
        'type' => 'event_reminder',
        'title' => 'Upcoming Event',
        'message' => 'Don\'t forget the workshop tomorrow.',
        'priority' => 'medium',
    ]);

    StaffNotification::create([
        'staff_id' => $user->id,
        'type' => 'assignment_due',
        'title' => 'Assignment Due',
        'message' => 'Your CTF challenge is due Friday.',
        'priority' => 'high',
        'action_required' => true,
        'action_url' => '/events/ctf-challenge',
    ]);

    StaffNotification::create([
        'staff_id' => $user->id,
        'type' => 'general',
        'title' => 'Welcome',
        'message' => 'Welcome to the club!',
        'is_read' => true,
        'read_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSeeLivewire(\App\Livewire\MemberNotifications::class);
});

it('redirects guests to login from notification center', function () {
    $response = $this->get(route('notifications.index'));

    $response->assertRedirect(route('auth.login'));
});

it('marks a notification as read', function () {
    $user = User::factory()->create();

    $notification = StaffNotification::create([
        'staff_id' => $user->id,
        'type' => 'general',
        'title' => 'Test',
        'message' => 'Read me',
    ]);

    $response = $this->actingAs($user)->post(route('notifications.read', $notification));

    $response->assertRedirect();
    expect($notification->fresh()->is_read)->toBeTrue();
});

it('prevents marking another user notification as read', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $notification = StaffNotification::create([
        'staff_id' => $owner->id,
        'type' => 'general',
        'title' => 'Private',
        'message' => 'Not yours',
    ]);

    $this->actingAs($other)
        ->post(route('notifications.read', $notification))
        ->assertForbidden();

    expect($notification->fresh()->is_read)->toBeFalse();
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();

    StaffNotification::create(['staff_id' => $user->id, 'type' => 'a', 'title' => 'A', 'message' => 'Msg A']);
    StaffNotification::create(['staff_id' => $user->id, 'type' => 'b', 'title' => 'B', 'message' => 'Msg B']);

    $response = $this->actingAs($user)->post(route('notifications.read-all'));

    $response->assertRedirect();
    expect(StaffNotification::where('staff_id', $user->id)->where('is_read', false)->count())->toBe(0);
});
