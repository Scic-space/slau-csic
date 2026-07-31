<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send 24-hour reminder notifications for upcoming events';

    public function handle(): int
    {
        $targetStart = now()->addDay()->startOfDay();
        $targetEnd = now()->addDay()->endOfDay();

        $events = Event::whereIn('status', ['published', 'scheduled'])
            ->whereBetween('start_date', [$targetStart, $targetEnd])
            ->get();

        $sent = 0;

        foreach ($events as $event) {
            $registrations = $event->registrations()
                ->where('status', 'registered')
                ->with('user')
                ->get();

            foreach ($registrations as $registration) {
                $registration->user->notify(new EventReminderNotification($event));
                $sent++;
            }
        }

        $this->info("Sent {$sent} reminders for {$events->count()} events.");

        return Command::SUCCESS;
    }
}
