<?php

use App\Livewire\SidebarBadges;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Fine;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->user = User::factory()->create()->assignRole('member');
    actingAs($this->user);
});

it('counts unread announcements', function () {
    Announcement::factory()->count(2)->create();
    Announcement::factory()->draft()->create();

    Livewire::test(SidebarBadges::class)
        ->assertSet('unreadAnnouncements', 2);
});

it('does not count announcements the user has viewed', function () {
    $viewed = Announcement::factory()->create();
    Announcement::factory()->create();

    $viewed->markAsViewedBy($this->user);

    Livewire::test(SidebarBadges::class)
        ->assertSet('unreadAnnouncements', 1);
});

it('counts unanswered polls', function () {
    Poll::factory()->published()->active()->create();
    Poll::factory()->active()->create(['is_published' => true]);
    Poll::factory()->published()->expired()->create();

    Livewire::test(SidebarBadges::class)
        ->assertSet('unansweredPolls', 2);
});

it('does not count polls the user already voted on', function () {
    $poll = Poll::factory()->published()->active()->create();
    $option = $poll->options()->create(['label' => 'Option A']);

    PollVote::create([
        'poll_id' => $poll->id,
        'option_id' => $option->id,
        'user_id' => $this->user->id,
    ]);

    Poll::factory()->published()->active()->create();

    Livewire::test(SidebarBadges::class)
        ->assertSet('unansweredPolls', 1);
});

it('counts unpaid fines', function () {
    $fineType = \App\Models\FineType::factory()->create();

    Fine::factory()->pending()->create(['user_id' => $this->user->id, 'fine_type_id' => $fineType->id]);
    Fine::factory()->partiallyPaid()->create(['user_id' => $this->user->id, 'fine_type_id' => $fineType->id]);
    Fine::factory()->paid()->create(['user_id' => $this->user->id, 'fine_type_id' => $fineType->id]);
    Fine::factory()->waived()->create(['user_id' => $this->user->id, 'fine_type_id' => $fineType->id]);

    Livewire::test(SidebarBadges::class)
        ->assertSet('unpaidFines', 2);
});

it('counts events the user can still register for', function () {
    Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'registration_required' => true,
        'registration_deadline' => now()->addDay(),
    ]);

    $alreadyRegistered = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'registration_required' => true,
        'registration_deadline' => now()->addDay(),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $alreadyRegistered->id,
        'user_id' => $this->user->id,
        'status' => 'registered',
    ]);

    Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'registration_required' => true,
        'registration_deadline' => now()->subDay(),
    ]);

    Event::factory()->create([
        'status' => 'draft',
        'is_public' => false,
    ]);

    Livewire::test(SidebarBadges::class)
        ->assertSet('eventsToRegister', 1);
});

it('counts upcoming events the user registered for', function () {
    $upcoming = Event::factory()->create([
        'start_date' => now()->addWeek(),
        'status' => 'scheduled',
    ]);
    EventRegistration::factory()->create([
        'event_id' => $upcoming->id,
        'user_id' => $this->user->id,
        'status' => 'registered',
    ]);

    Livewire::test(SidebarBadges::class)
        ->assertSet('upcomingMyEvents', 1);
});

it('counts upcoming meetings only for admin roles', function () {
    Meeting::factory()->upcoming()->create();
    Meeting::factory()->past()->create();
    Meeting::factory()->cancelled()->create(['scheduled_at' => now()->addWeek()]);

    Livewire::test(SidebarBadges::class)
        ->assertSet('upcomingMeetings', 0);

    $admin = User::factory()->create()->assignRole('admin');
    actingAs($admin);

    Livewire::test(SidebarBadges::class)
        ->assertSet('upcomingMeetings', 1);
});

it('refreshes counts when the sidebar-badges-refresh event fires', function () {
    Livewire::test(SidebarBadges::class)
        ->assertSet('unreadAnnouncements', 0)
        ->call('refreshCounts');

    Announcement::factory()->create();

    Livewire::test(SidebarBadges::class)
        ->dispatch('sidebar-badges-refresh')
        ->assertSet('unreadAnnouncements', 1);
});
