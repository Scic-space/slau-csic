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

it('marks unticked audience members absent when backfilling a targeted class', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearOneMissed = User::factory()->create();
    $yearOneMissed->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'audience' => 'year_1',
    ]);

    $summary = app(AttendanceService::class)->backfillAttendance($meeting, $this->admin, [$yearOne->id]);

    expect($summary['present'])->toBe(1)
        ->and($summary['absent'])->toBe(1);

    expect(Attendance::where('meeting_id', $meeting->id)->where('user_id', $yearOneMissed->id)->where('status', 'absent')->exists())->toBeTrue()
        ->and(Attendance::where('meeting_id', $meeting->id)->where('user_id', $yearThree->id)->exists())->toBeFalse();
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

it('changes attendance status while keeping points fair', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
    ]);
    $user = User::factory()->create();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subWeek(),
    ]);
    PointTransaction::create([
        'user_id' => $user->id,
        'points' => GamificationService::POINTS_MEETING_PRESENT,
        'reason' => "Checked in: {$meeting->title}",
        'reference_type' => Meeting::class,
        'reference_id' => $meeting->id,
    ]);
    $user->update(['attendance_count' => 1, 'total_sessions_attended' => 1]);

    app(AttendanceService::class)->setAttendanceStatus($meeting, $user, 'absent', $this->admin);

    expect($user->fresh()->attendance_count)->toBe(0)
        ->and(PointTransaction::where('user_id', $user->id)->sum('points'))->toBe(0);

    app(AttendanceService::class)->setAttendanceStatus($meeting, $user, 'late', $this->admin);

    expect($user->fresh()->attendance_count)->toBe(1)
        ->and(PointTransaction::where('user_id', $user->id)->sum('points'))
        ->toBe(GamificationService::POINTS_MEETING_LATE);
});

it('counts only attended members in the attended total', function () {
    $meeting = Meeting::factory()->teachingSession()->create(['created_by' => $this->admin->id]);
    Attendance::create(['meeting_id' => $meeting->id, 'user_id' => User::factory()->create()->id, 'status' => 'present', 'checked_in_at' => now()]);
    Attendance::create(['meeting_id' => $meeting->id, 'user_id' => User::factory()->create()->id, 'status' => 'absent', 'is_auto_absent' => true]);

    expect($meeting->getAttendedCount())->toBe(1)
        ->and($meeting->getAttendanceCount())->toBe(2);
});

it('syncs the restricted check-in list from the meeting audience', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'audience' => 'year_1',
    ]);
    $meeting->syncAudienceAllowList();

    expect($meeting->canUserAttend($yearOne))->toBeTrue()
        ->and($meeting->canUserAttend($yearThree))->toBeFalse();

    $meeting->update(['audience' => 'all']);
    $meeting->syncAudienceAllowList();

    expect($meeting->allowedAttendees()->exists())->toBeFalse()
        ->and($meeting->canUserAttend($yearThree))->toBeTrue();
});

it('duplicates a class for another day without its history', function () {
    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);

    $meeting = Meeting::factory()->teachingSession()->create([
        'title' => 'Bash Basics',
        'description' => '<p>Learn bash</p>',
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->subWeek(),
        'location' => 'Level 3',
        'duration_minutes' => 120,
        'audience' => 'year_1',
        'attendance_open' => false,
        'ended_at' => now()->subWeek(),
    ]);
    $meeting->agendaItems()->create(['title' => 'Intro', 'sort_order' => 1]);
    $meeting->attachments()->create([
        'name' => 'Slides',
        'file_path' => 'slides.pdf',
        'uploaded_by' => $this->admin->id,
    ]);
    $meeting->syncAudienceAllowList();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $yearOne->id,
        'status' => 'present',
        'checked_in_at' => now()->subWeek(),
    ]);

    $clone = $meeting->replicateForDate(now()->addWeek(), $this->admin);

    expect($clone->id)->not->toBe($meeting->id)
        ->and($clone->title)->toBe('Bash Basics')
        ->and($clone->description)->toBe('<p>Learn bash</p>')
        ->and($clone->location)->toBe('Level 3')
        ->and($clone->duration_minutes)->toBe(120)
        ->and($clone->audience)->toBe('year_1')
        ->and($clone->scheduled_at->isFuture())->toBeTrue()
        ->and($clone->meeting_code)->not->toBe($meeting->meeting_code)
        ->and($clone->attendance_open)->toBeFalse()
        ->and($clone->ended_at)->toBeNull()
        ->and($clone->is_recurring)->toBeFalse()
        ->and($clone->created_by)->toBe($this->admin->id)
        ->and($clone->agendaItems()->count())->toBe(1)
        ->and($clone->attachments()->count())->toBe(1)
        ->and($clone->attendance()->count())->toBe(0)
        ->and($clone->canUserAttend($yearOne))->toBeTrue();
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
