<?php

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function attendedMeeting(): array
{
    $user = User::factory()->create();
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subWeek(),
        'ended_at' => now()->subWeek(),
    ]);
    $attendance = Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subWeek(),
    ]);

    return [$user, $meeting, $attendance];
}

it('downloads a PDF certificate for an attended, ended class', function () {
    [$user, $meeting, $attendance] = attendedMeeting();

    $response = $this->actingAs($user)
        ->get(route('meetings.certificate', [$meeting, $attendance]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->streamedContent())->toStartWith('%PDF-');
});

it('forbids downloading another member class certificate', function () {
    [, $meeting, $attendance] = attendedMeeting();

    $this->actingAs(User::factory()->create())
        ->get(route('meetings.certificate', [$meeting, $attendance]))
        ->assertForbidden();
});

it('returns gone when class attendance is not confirmed', function () {
    [$user, $meeting, $attendance] = attendedMeeting();
    $attendance->update(['status' => 'absent']);

    $this->actingAs($user)
        ->get(route('meetings.certificate', [$meeting, $attendance]))
        ->assertStatus(410);
});

it('returns gone when the class has not ended yet', function () {
    [$user, $meeting, $attendance] = attendedMeeting();
    $meeting->update(['ended_at' => null]);

    $this->actingAs($user)
        ->get(route('meetings.certificate', [$meeting, $attendance]))
        ->assertStatus(410);
});

it('returns not found when the attendance belongs to another class', function () {
    [$user, $meeting] = attendedMeeting();
    $otherAttendance = Attendance::create([
        'meeting_id' => Meeting::factory()->teachingSession()->create()->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('meetings.certificate', [$meeting, $otherAttendance]))
        ->assertNotFound();
});
