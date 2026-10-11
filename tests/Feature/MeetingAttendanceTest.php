<?php

use App\Filament\Resources\Meetings\Schemas\MeetingForm;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\AttendanceService;
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

it('returns zero attendance rate when expected attendees is not set', function () {
    $meeting = Meeting::factory()->create([
        'created_by' => $this->admin->id,
        'expected_attendees' => null,
    ]);

    expect($meeting->getAttendanceRate())->toBe(0.0);
});

it('backfills past attendance, awards points once, and completes the meeting', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'attendance_open' => false,
        'ended_at' => null,
    ]);
    $present = User::factory()->create();
    $late = User::factory()->create();
    $absent = User::factory()->create();

    $summary = app(AttendanceService::class)->backfillAttendance($meeting, $this->admin, [$present->id], [$late->id]);

    expect($summary['present'])->toBe(1)
        ->and($summary['late'])->toBe(1)
        ->and($summary['absent'])->toBe(0)
        ->and($summary['skipped'])->toBe(0);

    expect(Attendance::where('meeting_id', $meeting->id)->count())->toBe(2)
        ->and(Attendance::where('meeting_id', $meeting->id)->where('status', 'absent')->count())->toBe(0);

    expect(PointTransaction::where('user_id', $present->id)->sum('points'))
        ->toBe(GamificationService::POINTS_MEETING_PRESENT)
        ->and(PointTransaction::where('user_id', $late->id)->sum('points'))
        ->toBe(GamificationService::POINTS_MEETING_LATE)
        ->and(PointTransaction::where('user_id', $absent->id)->count())->toBe(0);

    expect($present->fresh()->attendance_count)->toBe(1)
        ->and($meeting->fresh()->attendance_open)->toBeFalse()
        ->and($meeting->fresh()->ended_at)->not->toBeNull();

    $rerun = app(AttendanceService::class)->backfillAttendance($meeting, $this->admin, [$present->id], [$late->id]);

    expect($rerun['skipped'])->toBe(2)
        ->and(PointTransaction::where('user_id', $present->id)->sum('points'))
        ->toBe(GamificationService::POINTS_MEETING_PRESENT);
});

it('leaves unselected members out of backfilled attendance entirely', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'started_at' => null,
        'attendance_open' => false,
        'ended_at' => null,
    ]);
    $present = User::factory()->create();
    $other = User::factory()->create();

    app(AttendanceService::class)->backfillAttendance($meeting, $this->admin, [$present->id]);

    expect(Attendance::where('meeting_id', $meeting->id)->pluck('user_id')->all())
        ->toBe([$present->id])
        ->and(Attendance::where('meeting_id', $meeting->id)->where('user_id', $other->id)->exists())->toBeFalse();
});

it('does not finalize absences for meetings that were never opened', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'started_at' => null,
        'attendance_open' => false,
        'ended_at' => now()->subWeek(),
    ]);
    User::factory()->create();

    expect(app(AttendanceService::class)->finalizeAbsences($meeting))->toBe(0)
        ->and(Attendance::where('meeting_id', $meeting->id)->count())->toBe(0);
});

it('finalizes absences for meetings that were opened', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'started_at' => now()->subWeek(),
        'attendance_open' => true,
    ]);
    User::factory()->create();

    expect(app(AttendanceService::class)->finalizeAbsences($meeting))->toBeGreaterThan(0);
});

it('selects attendance pickers by member year', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);
    $noYear = User::factory()->create();

    expect(MeetingForm::memberIdsForYear(1))->toBe([$yearOne->id])
        ->and(MeetingForm::memberIdsAboveYear(1))->toBe([$yearThree->id]);

    $options = MeetingForm::memberOptions();

    expect($options[$yearOne->id])->toContain('(Y1)')
        ->and($options[$yearThree->id])->toContain('(Y3)')
        ->and($options[$noYear->id])->not->toContain('(Y');
});

it('refuses to backfill cancelled or future meetings', function () {
    $cancelled = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'cancelled_at' => now()->subWeek(),
    ]);
    $future = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addWeek(),
    ]);

    expect(fn () => app(AttendanceService::class)->backfillAttendance($cancelled, $this->admin, []))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => app(AttendanceService::class)->backfillAttendance($future, $this->admin, []))
        ->toThrow(InvalidArgumentException::class);
});

it('stamps ended_at when auto-closing past meetings', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subHours(3),
        'started_at' => now()->subHours(3),
        'duration_minutes' => 60,
        'attendance_open' => true,
        'ended_at' => null,
    ]);

    $this->artisan('meetings:auto-close')->assertSuccessful();

    $meeting->refresh();

    expect($meeting->attendance_open)->toBeFalse()
        ->and($meeting->ended_at)->not->toBeNull();
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
