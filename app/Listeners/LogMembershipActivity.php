<?php

namespace App\Listeners;

use App\Events\MemberApproved;
use App\Events\MemberRejected;
use App\Events\MemberSuspended;

class LogMembershipActivity
{
    public function handleApproved(MemberApproved $event): void
    {
        activity()
            ->performedOn($event->membership ?? $event->user)
            ->causedBy($event->approver)
            ->log("Member {$event->user->name} was approved");
    }

    public function handleRejected(MemberRejected $event): void
    {
        activity()
            ->performedOn($event->membership ?? $event->user)
            ->causedBy($event->rejecter)
            ->log("Member {$event->user->name} was rejected");
    }

    public function handleSuspended(MemberSuspended $event): void
    {
        activity()
            ->performedOn($event->membership ?? $event->user)
            ->causedBy($event->suspender)
            ->log("Member {$event->user->name} was suspended");
    }

    public function subscribe($events): array
    {
        return [
            MemberApproved::class => 'handleApproved',
            MemberRejected::class => 'handleRejected',
            MemberSuspended::class => 'handleSuspended',
        ];
    }
}
