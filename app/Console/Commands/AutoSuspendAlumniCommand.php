<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoSuspendAlumniCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:auto-suspend-alumni {--dry-run : Show what would be suspended without actually suspending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically suspend members who should be alumni based on graduation year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-suspension process for alumni...');

        // Get users who should be alumni but aren't marked as such
        $usersToConvert = User::where('membership_status', 'active')
            ->where('membership_type', '!=', 'alumni')
            ->whereNotNull('year_of_study')
            ->get()
            ->filter(function ($user) {
                return $user->shouldBeAlumni();
            });

        if ($usersToConvert->isEmpty()) {
            $this->info('No users need to be converted to alumni.');

            return 0;
        }

        $this->info("Found {$usersToConvert->count()} users to convert to alumni:");

        foreach ($usersToConvert as $user) {
            $expectedGraduationYear = now()->year + (4 - $user->year_of_study);
            $this->line("- {$user->name} (Year {$user->year_of_study}, Expected Graduation: {$expectedGraduationYear})");
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No users were actually converted.');

            return 0;
        }

        if ($this->confirm('Convert these users to alumni?')) {
            $convertedCount = 0;
            $failedCount = 0;

            foreach ($usersToConvert as $user) {
                try {
                    if ($user->convertToAlumni()) {
                        $this->info("✓ Converted {$user->name} to alumni");
                        $convertedCount++;
                    } else {
                        $this->error("✗ Failed to convert {$user->name}");
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Error converting {$user->name}: ".$e->getMessage());
                    $failedCount++;
                    Log::error('Failed to convert user to alumni', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("Conversion complete: {$convertedCount} converted, {$failedCount} failed");

            // Log summary
            Log::info('Auto-suspend alumni command completed', [
                'converted' => $convertedCount,
                'failed' => $failedCount,
                'total_processed' => $usersToConvert->count(),
            ]);

            return $failedCount > 0 ? 1 : 0;
        }

        $this->info('Operation cancelled.');

        return 0;
    }
}
