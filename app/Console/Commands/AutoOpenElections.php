<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Notifications\ElectionOpenedNotification;
use Illuminate\Console\Command;

class AutoOpenElections extends Command
{
    protected $signature = 'elections:auto-open';

    protected $description = 'Automatically open elections that have reached their starts_at time';

    public function handle(): int
    {
        $this->info('Checking for elections to auto-open...');

        $toOpen = Election::where('status', 'draft')
            ->where('starts_at', '<=', now())
            ->get();

        if ($toOpen->isEmpty()) {
            $this->line('No elections to open.');

            return Command::SUCCESS;
        }

        $this->info("Opening {$toOpen->count()} election(s).");

        foreach ($toOpen as $election) {
            $election->update(['status' => 'open']);

            $this->line("  ✓ Opened: {$election->title}");

            activity()
                ->performedOn($election)
                ->log('election_auto_opened');

            \App\Models\User::activeMembers()->chunk(100, fn ($users) => $users->each(
                fn ($user) => $user->notify(new ElectionOpenedNotification($election))
            ));
        }

        $this->info('Auto-open complete.');

        return Command::SUCCESS;
    }
}
