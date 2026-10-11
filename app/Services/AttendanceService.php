<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected GamificationService $gamification,
    ) {}

    public function checkInViaQr(string $meetingCode, User $user, ?string $ipAddress = null, ?string $deviceInfo = null): array
    {
        $meeting = Meeting::where('meeting_code', $meetingCode)->first();

        if (! $meeting) {
            return [
                'success' => false,
                'error' => 'Invalid meeting code',
            ];
        }

        if (! $meeting->isEligibleForCheckIn()) {
            return [
                'success' => false,
                'error' => 'Attendance is not open for this session',
            ];
        }

        if (! $meeting->canUserAttend($user)) {
            return [
                'success' => false,
                'error' => 'You are not in the allowed attendees list for this session',
            ];
        }

        $existingAttendance = $this->getAttendance($meeting, $user);
        if ($existingAttendance) {
            return [
                'success' => false,
                'error' => 'You have already checked in to this session',
                'checked_in_at' => $existingAttendance->checked_in_at,
            ];
        }

        $checkInTime = now();
        $status = $this->resolveStatus($meeting, $checkInTime);

        $isEarlyCheckIn = $checkInTime->lt($meeting->scheduled_at);
        $bonusPoints = $isEarlyCheckIn ? 5 : 0;

        $attendance = Attendance::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'checked_in_at' => $checkInTime,
            'check_in_method' => 'qr_code',
            'status' => $status,
            'late_threshold_minutes' => $meeting->getLateThresholdMinutes(),
            'ip_address' => $ipAddress,
            'device_info' => $deviceInfo,
        ]);

        if ($attendance->isPresentOrLate()) {
            $user->update([
                'attendance_count' => DB::raw('attendance_count + 1'),
            ]);

            $attendancePoints = $attendance->status === 'late'
                ? GamificationService::POINTS_MEETING_LATE
                : GamificationService::POINTS_MEETING_PRESENT;

            if ($bonusPoints > 0) {
                $user->update([
                    'bonus_points' => DB::raw('bonus_points + '.$bonusPoints),
                ]);

                $this->gamification->awardPoints(
                    $user,
                    GamificationService::POINTS_EARLY_CHECKIN,
                    "Early check-in bonus: {$meeting->title}",
                    Meeting::class,
                    $meeting->id,
                );
            }

            $this->gamification->awardPoints(
                $user,
                $attendancePoints,
                "Checked in: {$meeting->title}",
                Meeting::class,
                $meeting->id,
            );
        }

        $this->updateStreak($user, $meeting);

        return [
            'success' => true,
            'message' => $status === 'late' ? 'Checked in successfully (Late)' : 'Checked in successfully',
            'status' => $status,
            'is_early' => $isEarlyCheckIn,
            'bonus_earned' => $bonusPoints,
            'attendance' => $attendance,
        ];
    }

    public function markManually(Meeting $session, User $user, string $status, User $markedBy): Attendance
    {
        if (! $session->canUserAttend($user)) {
            throw new \InvalidArgumentException('User is not in the allowed attendees list for this session.');
        }

        $existingAttendance = $this->getAttendance($session, $user);

        if ($existingAttendance) {
            $existingAttendance->update([
                'status' => $status,
                'check_in_method' => 'admin_override',
                'marked_by' => $markedBy->id,
            ]);

            return $existingAttendance;
        }

        return Attendance::create([
            'meeting_id' => $session->id,
            'user_id' => $user->id,
            'checked_in_at' => now(),
            'check_in_method' => 'admin_override',
            'status' => $status,
            'late_threshold_minutes' => $session->getLateThresholdMinutes(),
            'marked_by' => $markedBy->id,
        ]);
    }

    /**
     * Record attendance for a past meeting that was never opened for check-in.
     *
     * Only the members in $presentIds/$lateIds appear in the meeting's
     * attendance. They earn the standard check-in points (late arrivals
     * earn the reduced rate, no early-bird bonus). Members that already
     * have an attendance row or points for the meeting are skipped, so
     * re-running is safe. The meeting is closed and marked completed.
     *
     * @param  list<int>  $presentIds
     * @param  list<int>  $lateIds
     * @return array{present: int, late: int, absent: int, skipped: int}
     */
    public function backfillAttendance(Meeting $meeting, User $admin, array $presentIds, array $lateIds = []): array
    {
        if ($meeting->isCancelled()) {
            throw new \InvalidArgumentException('Cannot backfill attendance for a cancelled meeting.');
        }

        if ($meeting->scheduled_at && $meeting->scheduled_at->isFuture()) {
            throw new \InvalidArgumentException('Backfill is only available for past meetings.');
        }

        $lateIds = array_values(array_unique(array_map('intval', $lateIds)));
        $presentIds = array_values(array_diff(array_unique(array_map('intval', $presentIds)), $lateIds));

        $eligibleIds = $meeting->allowedAttendees()->exists()
            ? $meeting->allowedAttendees()->pluck('user_id')->map(fn ($id) => (int) $id)->all()
            : User::activeMembers()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $presentIds = array_values(array_intersect($presentIds, $eligibleIds));
        $lateIds = array_values(array_intersect($lateIds, $eligibleIds));

        $existingIds = $meeting->attendance()->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $checkInTime = $meeting->scheduled_at ?? now();

        $present = 0;
        $late = 0;
        $skipped = 0;

        foreach (['present' => $presentIds, 'late' => $lateIds] as $status => $userIds) {
            foreach ($userIds as $userId) {
                if (in_array($userId, $existingIds, true)) {
                    $skipped++;

                    continue;
                }

                $user = User::find($userId);

                if (! $user) {
                    $skipped++;

                    continue;
                }

                Attendance::create([
                    'meeting_id' => $meeting->id,
                    'user_id' => $user->id,
                    'checked_in_at' => $checkInTime,
                    'check_in_method' => 'admin_override',
                    'status' => $status,
                    'late_threshold_minutes' => $meeting->getLateThresholdMinutes(),
                    'marked_by' => $admin->id,
                ]);

                $user->update([
                    'attendance_count' => DB::raw('attendance_count + 1'),
                    'total_sessions_attended' => DB::raw('total_sessions_attended + 1'),
                ]);

                $this->gamification->awardOnce(
                    $user,
                    $status === 'late'
                        ? GamificationService::POINTS_MEETING_LATE
                        : GamificationService::POINTS_MEETING_PRESENT,
                    "Checked in: {$meeting->title}",
                    Meeting::class,
                    $meeting->id,
                );

                $existingIds[] = $userId;

                if ($status === 'late') {
                    $late++;
                } else {
                    $present++;
                }
            }
        }

        $meeting->update([
            'attendance_open' => false,
            'ended_at' => $meeting->ended_at ?? now(),
        ]);

        return [
            'present' => $present,
            'late' => $late,
            'absent' => 0,
            'skipped' => $skipped,
        ];
    }

    public function finalizeAbsences(Meeting $session): int
    {
        if (! $session->started_at) {
            return 0;
        }

        $members = User::activeMembers();

        if ($session->allowedAttendees()->exists()) {
            $allowedIds = $session->allowedAttendees()->pluck('user_id');
            $members = $members->whereIn('id', $allowedIds);
        }

        $allActiveMembers = $members->get();
        $attendedUserIds = $session->attendance()->pluck('user_id');

        $absentCount = 0;

        foreach ($allActiveMembers as $member) {
            if (! $attendedUserIds->contains($member->id)) {
                Attendance::create([
                    'meeting_id' => $session->id,
                    'user_id' => $member->id,
                    'checked_in_at' => null,
                    'check_in_method' => null,
                    'status' => 'absent',
                    'is_auto_absent' => true,
                ]);
                $absentCount++;
            }
        }

        return $absentCount;
    }

    public function updateStreak(User $user, Meeting $session): void
    {
        $attendance = $this->getAttendance($session, $user);

        if (! $attendance) {
            return;
        }

        if ($attendance->isPresentOrLate()) {
            $newStreak = $user->current_streak + 1;

            $user->update([
                'current_streak' => $newStreak,
                'longest_streak' => max($user->longest_streak, $newStreak),
                'total_sessions_attended' => DB::raw('total_sessions_attended + 1'),
            ]);
        } else {
            $user->update([
                'current_streak' => 0,
            ]);
        }
    }

    private function resolveStatus(Meeting $session, Carbon $checkInTime): string
    {
        if (! $session->scheduled_at) {
            return 'present';
        }

        $lateThreshold = $session->getLateThresholdMinutes();
        $startTime = $session->scheduled_at;
        $lateCutoff = $startTime->copy()->addMinutes($lateThreshold);

        if ($checkInTime->lt($startTime)) {
            return 'present';
        }

        if ($checkInTime->gt($lateCutoff)) {
            return 'absent';
        }

        return 'late';
    }

    private function getAttendance(Meeting $session, User $user): ?Attendance
    {
        return Attendance::where('meeting_id', $session->id)
            ->where('user_id', $user->id)
            ->first();
    }
}
