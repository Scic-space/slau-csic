<?php

use App\Livewire\MemberDashboard;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('shows the authenticated user a time-appropriate dashboard greeting', function (string $time, string $greeting) {
    Carbon::setTestNow($time);

    $user = User::factory()->create([
        'name' => 'Amina Nsubuga',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Authenticated user greeting')
        ->assertSeeInOrder([$greeting, 'Amina Nsubuga']);
})->with([
    'night before five' => ['2026-08-22 04:59:00', 'Good night'],
    'morning from five' => ['2026-08-22 05:00:00', 'Good morning'],
    'afternoon from noon' => ['2026-08-22 12:00:00', 'Good afternoon'],
    'evening from five' => ['2026-08-22 17:00:00', 'Good evening'],
    'night from nine' => ['2026-08-22 21:00:00', 'Good night'],
]);

it('shows the complete your profile prompt when the profile is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('Complete Your Profile');
});

it('renders meaningful Material Symbols for dashboard cards', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSeeHtml('material-symbols-outlined')
        ->assertSeeInOrder([
            'event',
            'event_available',
            'local_fire_department',
            'stars',
            'fact_check',
            'emoji_events',
            'calendar_month',
            'school',
            'military_tech',
            'history',
        ]);
});

it('does not count profile fields that live on the users table columns only', function () {
    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/avatar.jpg',
    ]);

    $user->update([
        'phone' => '0700000001',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'bio' => 'A keen security enthusiast.',
    ]);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('Your profile is 31% complete.');
});

it('counts profile fields stored on member_profiles and social_links', function () {
    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/avatar.jpg',
    ]);

    $user->memberProfile()->create([
        'phone' => '0700000001',
        'bio' => 'A keen security enthusiast.',
        'headline' => 'Security Researcher',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
    ]);

    $user->socialLinks()->create([
        'github_username' => 'kevin',
        'linkedin_url' => 'https://linkedin.com/in/kevin',
        'discord_username' => 'kevin',
    ]);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertDontSee('Complete Your Profile');
});

it('counts events attended from the attended_at marker, not meetings or status', function () {
    $user = User::factory()->create();

    $attended = Event::factory()->create(['status' => 'completed']);
    $mislabeled = Event::factory()->create(['status' => 'completed']);

    EventRegistration::factory()->create([
        'event_id' => $attended->id,
        'user_id' => $user->id,
        'status' => 'registered',
        'attended_at' => now(),
    ]);

    EventRegistration::factory()->create([
        'event_id' => $mislabeled->id,
        'user_id' => $user->id,
        'status' => 'attended',
        'attended_at' => null,
    ]);

    expect($user->eventsAttendedCount())->toBe(1);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['events_attended'] === 1);
});

it('reports an attendance rate that combines meetings and events like the calendar', function () {
    $user = User::factory()->create();

    $meeting = Meeting::factory()->create(['created_by' => $user->id]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subDay(),
    ]);

    $absentMeeting = Meeting::factory()->create(['created_by' => $user->id]);
    Attendance::create([
        'meeting_id' => $absentMeeting->id,
        'user_id' => $user->id,
        'status' => 'absent',
        'is_auto_absent' => true,
    ]);

    $event = Event::factory()->create(['status' => 'completed']);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
        'attended_at' => now(),
    ]);

    expect($user->getOverallAttendanceRate())->toBe(66.67);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['attendance_rate'] === 66.67);
});

it('returns a zero attendance rate when there are no sessions', function () {
    $user = User::factory()->create();

    expect($user->getOverallAttendanceRate())->toBe(0.0);
});

it('renders the my events section with attendance and feedback prompts', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('My Events')
        ->assertSee('Recently Attended')
        ->assertSee('Awaiting Feedback');
});
