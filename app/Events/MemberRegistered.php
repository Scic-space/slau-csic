<?php

namespace App\Events;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class MemberRegistered
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public User $user,
        public Membership $membership,
    ) {}
}
