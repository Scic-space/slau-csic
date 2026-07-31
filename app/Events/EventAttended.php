<?php

namespace App\Events;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class EventAttended
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public Event $event,
        public EventRegistration $registration,
    ) {}
}
