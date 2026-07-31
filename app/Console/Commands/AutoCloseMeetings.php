<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class AutoCloseMeetings extends Command
{
    protected $signature = 'meetings:auto-close';

    protected $description = 'Auto-close attendance and mark absent for meetings past their end time';

    public function handle(): int
    {
        $this->info('Checking meetings for auto-close...');

        $meetings = Meeting::notCancelled()
            ->where('attendance_open', true)
            ->get();

        $closed = 0;

        foreach ($meetings as $meeting) {
            $endTime = $meeting->getEndTime();

            if (! $endTime || $endTime->isFuture()) {
                continue;
            }

            $this->info("Closing: {$meeting->title}");

            $attendanceService = app(AttendanceService::class);

            if ($meeting->isTeachingSession()) {
                $attendanceService->finalizeAbsences($meeting);
            }

            $meeting->update(['attendance_open' => false]);

            $this->markNonAttendeesAsAbsent($meeting);

            $closed++;
            $this->line("  ✓ Closed attendance for {$meeting->title}");
        }

        $this->info("Auto-closed {$closed} meeting(s).");

        return Command::SUCCESS;
    }

    protected function markNonAttendeesAsAbsent(Meeting $meeting): void
    {
        if ($meeting->expected_attendees <= 0) {
            return;
        }

        $attendedUserIds = $meeting->attendance()->pluck('user_id');

        $nonAttendees = $meeting->allowedAttendees()
            ->whereNotIn('user_id', $attendedUserIds)
            ->get();

        foreach ($nonAttendees as $user) {
            $meeting->recordAttendance($user, 'system', [
                'status' => 'absent',
                'is_auto_absent' => true,
                'marked_by' => $meeting->created_by,
            ]);
        }
    }
}
