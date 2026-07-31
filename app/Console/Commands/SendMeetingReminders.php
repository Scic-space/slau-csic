<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Meeting;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Console\Command;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders {--type=24h : Reminder type: 24h or 1h}';

    protected $description = 'Send reminder notifications for upcoming meetings';

    public function handle(): int
    {
        $type = $this->option('type');
        $this->info("Sending {$type} meeting reminders...");

        if ($type === '24h') {
            $targetStart = now()->addDay()->startOfDay();
            $targetEnd = now()->addDay()->endOfDay();
        } else {
            $targetStart = now()->addHour()->startOfHour();
            $targetEnd = now()->addHour()->endOfHour();
        }

        $meetings = Meeting::notCancelled()
            ->whereBetween('scheduled_at', [$targetStart, $targetEnd])
            ->get();

        $sent = 0;

        foreach ($meetings as $meeting) {
            $users = \App\Models\User::activeMembers()->get();

            foreach ($users as $user) {
                $hasAttended = Attendance::where('meeting_id', $meeting->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if (! $hasAttended) {
                    $user->notify(new MeetingReminderNotification($meeting, $type));
                    $sent++;
                }
            }
        }

        $this->info("Sent {$sent} reminders for {$meetings->count()} meetings.");

        return Command::SUCCESS;
    }
}
