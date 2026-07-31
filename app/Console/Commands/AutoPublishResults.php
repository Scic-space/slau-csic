<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Notifications\ElectionResultsPublishedNotification;
use Illuminate\Console\Command;

class AutoPublishResults extends Command
{
    protected $signature = 'elections:auto-publish-results';

    protected $description = 'Automatically publish election results that have reached their results_publish_at time';

    public function handle(): int
    {
        $this->info('Checking for results to auto-publish...');

        $toPublish = Election::where('results_visible', false)
            ->whereNotNull('results_publish_at')
            ->where('results_publish_at', '<=', now())
            ->get();

        if ($toPublish->isEmpty()) {
            $this->line('No results to publish.');

            return Command::SUCCESS;
        }

        foreach ($toPublish as $election) {
            $election->update(['results_visible' => true]);

            $this->line("  Published: {$election->title}");

            activity()
                ->performedOn($election)
                ->log('election_results_auto_published');

            \App\Models\User::whereHas('electionVotes', fn ($q) => $q->where('election_id', $election->id))
                ->chunk(100, fn ($users) => $users->each(
                    fn ($user) => $user->notify(new ElectionResultsPublishedNotification($election))
                ));
        }

        $this->info('Auto-publish complete.');

        return Command::SUCCESS;
    }
}
