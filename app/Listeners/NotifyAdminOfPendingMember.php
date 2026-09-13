<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Models\Membership;
use App\Notifications\MemberRequiresApproval;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyAdminOfPendingMember
{
    public function handle(MemberRegistered $event): void
    {
        $admins = Membership::query()
            ->whereIn('status', ['active'])
            ->whereHas('user.roles', fn ($q) => $q->whereIn('name', ['admin', 'super-admin']))
            ->with('user')
            ->get()
            ->pluck('user');

        foreach ($admins as $admin) {
            try {
                $admin->notify(new MemberRequiresApproval($event->user));
            } catch (Throwable $e) {
                Log::warning('Approval request email could not be sent to admin', [
                    'admin_id' => $admin->id,
                    'pending_user_id' => $event->user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
