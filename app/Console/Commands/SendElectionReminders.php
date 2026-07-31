<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Notifications\ElectionReminderNotification;
use Illuminate\Console\Command;

class SendElectionReminders extends Command
{
    protected $signature = 'elections:send-reminders';

    protected $description = 'Send reminders to members who have not voted in elections closing within 24 hours';

    public function handle(): int
    {
        $this->info('Checking for elections closing within 24 hours...');

        $closingSoon = Election::where('status', 'open')
            ->where('ends_at', '<=', now()->addDay())
            ->where('ends_at', '>', now())
            ->get();

        if ($closingSoon->isEmpty()) {
            $this->line('No elections closing within 24 hours.');

            return Command::SUCCESS;
        }

        foreach ($closingSoon as $election) {
            $this->line("Processing: {$election->title}");

            $voterIds = $election->votes()->pluck('user_id');

            \App\Models\User::activeMembers()
                ->whereNotIn('id', $voterIds)
                ->chunk(100, fn ($users) => $users->each(
                    fn ($user) => $user->notify(new ElectionReminderNotification($election))
                ));

            $this->line("  ✓ Reminders sent for {$election->title}");
        }

        $this->info('Reminder dispatch complete.');

        return Command::SUCCESS;
    }
}
