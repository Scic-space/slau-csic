<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MembershipExpiring;
use Illuminate\Console\Command;

class CheckExpiredMemberships extends Command
{
    protected $signature = 'members:check-expired';

    protected $description = 'Suspend expired memberships and warn about upcoming expirations';

    public function handle(): void
    {
        $this->suspendExpired();
        $this->warnExpiring();
    }

    protected function suspendExpired(): void
    {
        $count = User::whereDate('membership_expires_at', '<', now())
            ->where('membership_status', 'active')
            ->count();

        if ($count > 0) {
            User::whereDate('membership_expires_at', '<', now())
                ->where('membership_status', 'active')
                ->chunk(100, function ($users): void {
                    foreach ($users as $user) {
                        $user->update(['membership_status' => 'inactive']);
                    }
                });
        }

        $this->info("Suspended {$count} expired memberships.");
    }

    protected function warnExpiring(): void
    {
        $cutoff = now()->addDays(14);

        $count = User::whereDate('membership_expires_at', '<=', $cutoff)
            ->whereDate('membership_expires_at', '>', now())
            ->where('membership_status', 'active')
            ->count();

        if ($count > 0) {
            User::whereDate('membership_expires_at', '<=', $cutoff)
                ->whereDate('membership_expires_at', '>', now())
                ->where('membership_status', 'active')
                ->chunk(100, function ($users): void {
                    foreach ($users as $user) {
                        $user->notify(new MembershipExpiring($user->membership_expires_at));
                    }
                });
        }

        $this->info("Warned {$count} members about upcoming expiration.");
    }
}
