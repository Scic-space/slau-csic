<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class EventRegistered
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public Event $event,
    ) {}
}
