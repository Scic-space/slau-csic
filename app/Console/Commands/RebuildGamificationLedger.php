<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GamificationRebuildService;
use Illuminate\Console\Command;

class RebuildGamificationLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:rebuild
        {--dry-run : Preview per-user changes without writing anything}
        {--user= : Rebuild a single user by ID or email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild points ledgers, counters, streaks, badges, and ranks from attendance records using the current fair points rules';

    /**
     * Execute the console command.
     */
    public function handle(GamificationRebuildService $rebuild): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($userOption = $this->option('user')) {
            $user = is_numeric($userOption)
                ? User::find($userOption)
                : User::where('email', $userOption)->first();

            if (! $user) {
                $this->error("User not found: {$userOption}");

                return self::FAILURE;
            }

            $users = collect([$user]);
        } else {
            $users = User::orderBy('id')->get();
        }

        if (! $dryRun && ! $this->option('user')) {
            $this->warn('This will delete and regenerate rule-governed point transactions for '.$users->count().' users.');
            $this->warn('Take a database backup first.');

            if (! $this->confirm('Proceed?', false)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $reports = [];

        $this->withProgressBar($users, function (User $user) use ($rebuild, $dryRun, &$reports): void {
            $reports[] = $rebuild->rebuild($user, $dryRun);
        });

        $this->newLine(2);

        $changed = array_values(array_filter($reports, fn (array $report) => $report['before'] !== $report['after']
            || $report['removed_count'] > 0
            || $report['added_count'] > 0
            || $report['new_badges'] !== []));

        if ($changed === []) {
            $this->info('No changes needed.');

            return self::SUCCESS;
        }

        $this->table(
            ['Member', 'Before', 'Removed', 'Added', 'Kept', 'New badges', 'After'],
            array_map(fn (array $report) => [
                $report['name'],
                $report['before'],
                $report['removed_count'] > 0 ? "-{$report['removed_points']} ({$report['removed_count']})" : '—',
                $report['added_count'] > 0 ? "+{$report['added_points']} ({$report['added_count']})" : '—',
                $report['kept_points'],
                $report['new_badges'] !== [] ? implode(', ', $report['new_badges']) : '—',
                $report['after'],
            ], $changed),
        );

        $unknown = [];

        foreach ($changed as $report) {
            foreach ($report['unknown_reasons'] as $reason) {
                $unknown[$reason] = ($unknown[$reason] ?? 0) + 1;
            }
        }

        if ($unknown !== []) {
            $this->newLine();
            $this->warn('Preserved positive transactions with unrecognized reasons:');

            foreach ($unknown as $reason => $count) {
                $this->line("  {$count}x {$reason}");
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? 'Dry run complete — nothing was written.'
            : 'Rebuild complete: '.count($changed).' member(s) updated.');

        return self::SUCCESS;
    }
}
