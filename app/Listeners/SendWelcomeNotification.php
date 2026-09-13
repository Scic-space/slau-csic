<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Notifications\WelcomeMember;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWelcomeNotification
{
    public function handle(MemberRegistered $event): void
    {
        try {
            $event->user->notify(new WelcomeMember($event->user));
        } catch (Throwable $e) {
            Log::warning('Welcome email could not be sent to new member', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
