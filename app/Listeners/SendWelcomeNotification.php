<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Notifications\WelcomeMember;

class SendWelcomeNotification
{
    public function handle(MemberRegistered $event): void
    {
        $event->user->notify(new WelcomeMember($event->user));
    }
}
