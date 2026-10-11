<?php

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

it('records attendance via QR code', function () {
    $meeting = Meeting::factory()->ongoing()->teachingSession()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    actingAs($user);
    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => true]);

    assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'check_in_method' => 'qr_code',
    ]);
});

it('does not count or reward a check-in that resolves to absent', function () {
    $meeting = Meeting::factory()->ongoing()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subHours(2),
        'late_threshold_minutes' => 15,
    ]);
    $user = User::factory()->create();

    actingAs($user);
    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => true, 'status' => 'absent']);

    $user->refresh();

    expect($user->attendance_count)->toBe(0)
        ->and((int) $user->bonus_points)->toBe(0)
        ->and(PointTransaction::where('user_id', $user->id)->count())->toBe(0);
});

it('prevents duplicate attendance', function () {
    $meeting = Meeting::factory()->ongoing()->teachingSession()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    actingAs($user);
    $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => false]);
    $count = Attendance::where('meeting_id', $meeting->id)->where('user_id', $user->id)->count();
    expect($count)->toBe(1);
});

it('rejects check-in for non-existent meeting code', function () {
    actingAs($this->admin);
    $response = $this->post('/attendance/verify/INVALID');

    $response->assertJson(['success' => false]);
    $response->assertStatus(404);
});

it('tracks check-in method', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    $meeting->recordAttendance($user, 'manual', ['marked_by' => $this->admin->id]);

    assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'check_in_method' => 'manual',
    ]);
});

it('respects allowed attendees restriction', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $allowedUser = User::factory()->create();
    $restrictedUser = User::factory()->create();

    $meeting->allowedAttendees()->attach($allowedUser->id);

    expect($meeting->canUserAttend($allowedUser))->toBeTrue();
    expect($meeting->canUserAttend($restrictedUser))->toBeFalse();
});

it('allows all users when no restriction is set', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    expect($meeting->canUserAttend($user))->toBeTrue();
});

it('excludes absences from a member attendance rate', function () {
    $member = User::factory()->create(['joined_at' => now()->subMonth()]);

    $presentMeeting = Meeting::factory()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subDays(10),
    ]);
    $absentMeeting = Meeting::factory()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subDays(5),
    ]);

    Attendance::create([
        'meeting_id' => $presentMeeting->id,
        'user_id' => $member->id,
        'status' => 'present',
        'checked_in_at' => now()->subDays(10),
    ]);

    Attendance::create([
        'meeting_id' => $absentMeeting->id,
        'user_id' => $member->id,
        'status' => 'absent',
        'is_auto_absent' => true,
    ]);

    $member->refresh();

    expect($member->getAttendanceRate())->toBe(50.0)
        ->and($member->attendedMeetingsCount())->toBe(1)
        ->and($member->getMemberStats()['total_attendance'])->toBe(1);
});

it('ignores cancelled meetings when computing the attendance rate', function () {
    $member = User::factory()->create(['joined_at' => now()->subMonth()]);

    $held = Meeting::factory()->create(['created_by' => $this->admin->id, 'scheduled_at' => now()->subDays(10)]);
    $cancelled = Meeting::factory()->cancelled()->create(['created_by' => $this->admin->id, 'scheduled_at' => now()->subDays(5)]);

    Attendance::create(['meeting_id' => $held->id, 'user_id' => $member->id, 'status' => 'present', 'checked_in_at' => now()->subDays(10)]);

    expect($member->fresh()->getAttendanceRate())->toBe(100.0);
});

it('computes attendance statistics correctly', function () {
    $meeting = Meeting::factory()->ongoing()->create([
        'created_by' => $this->admin->id,
        'expected_attendees' => 20,
    ]);

    $users = User::factory()->count(10)->create();

    foreach ($users as $user) {
        $meeting->recordAttendance($user, 'qr_code', [
            'status' => 'present',
        ]);
    }

    expect($meeting->getAttendanceCount())->toBe(10);
    expect($meeting->getAttendanceRate())->toBe(50.0);
    expect($meeting->getPresentCount())->toBe(10);
});

it('awards reduced ledger points for a late check-in', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subMinutes(5),
        'started_at' => now()->subMinutes(5),
        'attendance_open' => true,
        'late_threshold_minutes' => 15,
    ]);
    $user = User::factory()->create();

    actingAs($user);
    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => true, 'status' => 'late']);

    $user->refresh();

    expect((int) $user->bonus_points)->toBe(0)
        ->and(PointTransaction::where('user_id', $user->id)->count())->toBe(1)
        ->and(PointTransaction::where('user_id', $user->id)->sum('points'))->toBe(GamificationService::POINTS_MEETING_LATE);
});

it('puts the early check-in bonus in the points ledger', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addHour(),
        'attendance_open' => true,
        'late_threshold_minutes' => 15,
    ]);
    $user = User::factory()->create();

    actingAs($user);
    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => true, 'status' => 'present', 'bonus_earned' => GamificationService::POINTS_EARLY_CHECKIN]);

    $user->refresh();

    expect((int) $user->bonus_points)->toBe(GamificationService::POINTS_EARLY_CHECKIN)
        ->and(PointTransaction::where('user_id', $user->id)->count())->toBe(2)
        ->and(PointTransaction::where('user_id', $user->id)->sum('points'))->toBe(
            GamificationService::POINTS_MEETING_PRESENT + GamificationService::POINTS_EARLY_CHECKIN
        );
});
