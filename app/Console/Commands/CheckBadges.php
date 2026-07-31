<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Console\Command;

class CheckBadges extends Command
{
    protected $signature = 'badge:check {--user= : Check badges for a specific user ID or email}';

    protected $description = 'Check and award any unearned badges for all users or a specific user';

    public function handle(GamificationService $gamification): void
    {
        if ($userOption = $this->option('user')) {
            $user = is_numeric($userOption)
                ? User::find($userOption)
                : User::where('email', $userOption)->first();

            if (! $user) {
                $this->error("User not found: {$userOption}");

                return;
            }

            $earned = $gamification->checkBadges($user);

            if ($earned->isEmpty()) {
                $this->info("No new badges for {$user->name}.");

                return;
            }

            foreach ($earned as $badge) {
                $this->info("Awarded [{$badge->name}] to {$user->name}");
            }

            return;
        }

        $users = User::all();
        $totalAwarded = 0;

        $this->withProgressBar($users, function (User $user) use ($gamification, &$totalAwarded): void {
            $earned = $gamification->checkBadges($user);
            $totalAwarded += $earned->count();
        });

        $this->newLine();
        $this->info("Done! Awarded {$totalAwarded} badge(s) across ".$users->count().' users.');
    }
}
