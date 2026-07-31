<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Notifications\ElectionClosedNotification;
use Illuminate\Console\Command;

class AutoCloseElections extends Command
{
    protected $signature = 'elections:auto-close';

    protected $description = 'Automatically close elections that have passed their end date';

    public function handle(): int
    {
        $this->info('Checking for elections to auto-close...');

        $expired = Election::where('status', 'open')
            ->where('ends_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->line('No elections to close.');

            return Command::SUCCESS;
        }

        $this->info("Closing {$expired->count()} election(s).");

        foreach ($expired as $election) {
            $election->update(['status' => 'closed']);

            $this->line("  ✓ Closed: {$election->title}");

            activity()
                ->performedOn($election)
                ->log('election_auto_closed');

            \App\Models\User::whereHas('electionVotes', fn ($q) => $q->where('election_id', $election->id))
                ->chunk(100, fn ($users) => $users->each(
                    fn ($user) => $user->notify(new ElectionClosedNotification($election))
                ));
        }

        $this->info('Auto-close complete.');

        return Command::SUCCESS;
    }
}
