<?php

use App\Livewire\MemberMeetings;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\MeetingInterest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('requires authentication to view meetings', function () {
    $this->get(route('meetings.index'))->assertRedirect('/auth/login');
});

it('renders the meetings page for approved members', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $this->actingAs($user)
        ->get(route('meetings.index'))
        ->assertSuccessful()
        ->assertSee('Meetings')
        ->assertSee('All Types');
});

it('shows upcoming meetings by default and past meetings when filtered', function () {
    $user = User::factory()->create();

    Meeting::factory()->upcoming()->create([
        'title' => 'Upcoming General Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);
    Meeting::factory()->past()->create([
        'title' => 'Past General Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);
    Meeting::factory()->upcoming()->teachingSession()->create([
        'title' => 'Upcoming Teaching Session',
    ]);
    Meeting::factory()->upcoming()->cancelled()->create([
        'title' => 'Cancelled General Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->assertSee('Upcoming General Meeting')
        ->assertDontSee('Past General Meeting')
        ->assertDontSee('Upcoming Teaching Session')
        ->assertDontSee('Cancelled General Meeting')
        ->set('filter', 'past')
        ->assertSee('Past General Meeting')
        ->assertDontSee('Upcoming General Meeting');
});

it('filters meetings by type and search term', function () {
    $user = User::factory()->create();

    Meeting::factory()->upcoming()->create([
        'title' => 'Budget Planning Sync',
        'type' => 'general',
        'audience' => 'all',
    ]);
    Meeting::factory()->upcoming()->create([
        'title' => 'Linux Security Workshop',
        'type' => 'workshop',
        'audience' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->set('type', 'workshop')
        ->assertSee('Linux Security Workshop')
        ->assertDontSee('Budget Planning Sync')
        ->set('type', '')
        ->set('search', 'Budget')
        ->assertSee('Budget Planning Sync')
        ->assertDontSee('Linux Security Workshop');
});

it('hides meetings targeted at other year groups', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    Meeting::factory()->upcoming()->create([
        'title' => 'First Year Orientation',
        'type' => 'general',
        'audience' => 'year_1',
    ]);
    $targetedMeeting = Meeting::factory()->upcoming()->create([
        'title' => 'First Year Workshop',
        'type' => 'workshop',
        'audience' => 'year_1',
    ]);
    Meeting::factory()->upcoming()->create([
        'title' => 'Open Club Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);

    Livewire::actingAs($yearThree)
        ->test(MemberMeetings::class)
        ->assertSee('Open Club Meeting')
        ->assertDontSee('First Year Orientation')
        ->call('toggleInterest', $targetedMeeting->id)
        ->assertForbidden();

    expect(MeetingInterest::where('meeting_id', $targetedMeeting->id)->exists())->toBeFalse();

    Livewire::actingAs($yearOne)
        ->test(MemberMeetings::class)
        ->assertSee('First Year Orientation')
        ->assertSee('Open Club Meeting');
});

it('toggles interest in an upcoming meeting', function () {
    $user = User::factory()->create();
    $meeting = Meeting::factory()->upcoming()->create([
        'title' => 'Interest Toggle Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->call('toggleInterest', $meeting->id)
        ->assertSee('Interested');

    expect(MeetingInterest::where('meeting_id', $meeting->id)->where('user_id', $user->id)->exists())->toBeTrue();

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->call('toggleInterest', $meeting->id)
        ->assertSee("I'm interested");

    expect(MeetingInterest::where('meeting_id', $meeting->id)->where('user_id', $user->id)->exists())->toBeFalse();
});

it('blocks interest flags for meetings that have ended', function () {
    $user = User::factory()->create();
    $meeting = Meeting::factory()->past()->create([
        'type' => 'general',
        'audience' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->call('toggleInterest', $meeting->id)
        ->assertForbidden();

    expect(MeetingInterest::where('meeting_id', $meeting->id)->exists())->toBeFalse();
});

it('shows attendance status for past meetings', function () {
    $user = User::factory()->create();
    $meeting = Meeting::factory()->past()->create([
        'title' => 'Attendance Review Meeting',
        'type' => 'general',
        'audience' => 'all',
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'status' => 'late',
        'checked_in_at' => now()->subWeek(),
    ]);

    Livewire::actingAs($user)
        ->test(MemberMeetings::class)
        ->set('filter', 'past')
        ->assertSee('Attendance Review Meeting')
        ->assertSee('Late');
});
