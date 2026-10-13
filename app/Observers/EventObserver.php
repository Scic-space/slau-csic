<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\User;
use App\Notifications\EventPublishedNotification;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        if ($event->status === 'published') {
            $this->announce($event);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        if ($event->wasChanged('status') && $event->status === 'published') {
            $this->announce($event);
        }
    }

    protected function announce(Event $event): void
    {
        if ($event->start_date && $event->start_date->isPast()) {
            return;
        }

        $recipients = $event->allowedMembers()->exists()
            ? $event->allowedMembers()->get()
            : User::activeMembers()->get();

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new EventPublishedNotification($event));
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
}
