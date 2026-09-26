<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\PromotedFromWaitlist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class PromoteFromWaitlist implements ShouldQueue
{
    use Queueable;

    public function __construct(public Event $event) {}

    public function handle(): void
    {
        $registration = $this->event->getConnection()->transaction(function (): ?EventRegistration {
            $event = Event::query()->lockForUpdate()->find($this->event->id);

            if (! $event || $event->hasEnded() || ! in_array($event->status, ['published', 'scheduled', 'ongoing'], true) || $event->is_full) {
                return null;
            }

            $registration = $event->registrations()
                ->where('status', 'waitlist')
                ->orderBy('waitlisted_at')
                ->first();

            if ($registration) {
                $registration->update([
                    'status' => 'registered',
                    'registered_at' => now(),
                    'waitlisted_at' => null,
                ]);
            }

            return $registration;
        });

        if ($registration) {
            Notification::send($registration->user, new PromotedFromWaitlist($registration->event));
        }
    }
}
