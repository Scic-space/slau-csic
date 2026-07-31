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

        $user->update([
            'attendance_count' => DB::raw('attendance_count + 1'),
        ]);

        if ($bonusPoints > 0) {
            $user->update([
                'bonus_points' => DB::raw('bonus_points + '.$bonusPoints),
            ]);
        }

        $this->updateStreak($user, $meeting);

        $this->gamification->awardPoints(
            $user,
            20,
            "Checked in: {$meeting->title}",
            Meeting::class,
            $meeting->id,
        );

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

    public function finalizeAbsences(Meeting $session): int
    {
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
