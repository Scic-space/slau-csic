<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSending;

class CheckNotificationPreferences
{
    public function handle(NotificationSending $event): void
    {
        $notification = $event->notification;
        $notifiable = $event->notifiable;

        $preferenceType = method_exists($notification, 'getPreferenceType')
            ? $notification->getPreferenceType()
            : null;

        if ($preferenceType && method_exists($notifiable, 'shouldNotify')) {
            if (! $notifiable->shouldNotify($preferenceType)) {
                $event->cancel();
            }
        }
    }
}
