<?php

use App\Livewire\Admin\MeetingDetails;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    actingAs($this->admin);
});

it('can list meetings', function () {
    Meeting::factory()->count(3)->create(['created_by' => $this->admin->id]);

    $response = $this->get('/admin/meetings');

    $response->assertOk();
});

it('can create a meeting', function () {
    $meeting = Meeting::factory()->make(['created_by' => $this->admin->id]);

    $response = $this->post('/admin/meetings', $meeting->toArray());

    $response->assertSessionHasNoErrors();
});

it('can view meeting details', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->assertSet('meeting.id', $meeting->id)
        ->assertSee($meeting->title);
});

it('can open attendance', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->call('openAttendance');

    expect($meeting->fresh()->attendance_open)->toBeTrue();
});

it('can close attendance', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->call('closeAttendance');

    expect($meeting->fresh()->attendance_open)->toBeFalse();
});

it('can record manual attendance', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->set('selectedUsers', [$user->id])
        ->call('recordManualAttendance');

    expect(Attendance::where('meeting_id', $meeting->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

it('can cancel a meeting', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);
    $reason = 'Scheduling conflict';

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->set('cancellationReason', $reason)
        ->call('cancelMeeting');

    expect($meeting->fresh()->isCancelled())->toBeTrue();
    expect($meeting->fresh()->cancellation_reason)->toBe($reason);
});

it('can reschedule a meeting', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);
    $newDate = now()->addWeek()->format('Y-m-d');
    $newTime = '15:00';

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->set('newDate', $newDate)
        ->set('newTime', $newTime)
        ->call('rescheduleMeeting');

    $expectedDate = \Carbon\Carbon::parse($newDate.' '.$newTime);
    expect($meeting->fresh()->scheduled_at->format('Y-m-d H:i'))->toBe($expectedDate->format('Y-m-d H:i'));
});

it('can filter meetings by status', function () {
    Meeting::factory()->upcoming()->create(['created_by' => $this->admin->id]);
    Meeting::factory()->past()->create(['created_by' => $this->admin->id]);
    Meeting::factory()->cancelled()->create(['created_by' => $this->admin->id]);

    $response = $this->get('/admin/meetings');

    $response->assertOk();
});

it('prevents unauthorized users from opening attendance', function () {
    $member = User::factory()->create();
    $member->assignRole('member');

    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);

    actingAs($member);

    Livewire::test(MeetingDetails::class, ['meeting' => $meeting])
        ->call('openAttendance');

    expect($meeting->fresh()->attendance_open)->toBeFalse();
});

it('soft-deletes a meeting', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);

    $meeting->delete();

    expect(Meeting::find($meeting->id))->toBeNull();
    expect(Meeting::withTrashed()->find($meeting->id))->not->toBeNull();
});

it('can finalize minutes', function () {
    $meeting = Meeting::factory()->past()->create(['created_by' => $this->admin->id]);

    $meeting->finalizeMinutes();

    expect($meeting->fresh()->minutes_status)->toBe('finalized');
});

it('can publish minutes', function () {
    $meeting = Meeting::factory()->past()->create(['created_by' => $this->admin->id]);
    $meeting->finalizeMinutes();

    $meeting->publishMinutes();

    expect($meeting->fresh()->minutes_status)->toBe('published');
});
