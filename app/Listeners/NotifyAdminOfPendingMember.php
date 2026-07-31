<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Models\Membership;
use App\Notifications\MemberRequiresApproval;

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
            $admin->notify(new MemberRequiresApproval($event->user));
        }
    }
}
