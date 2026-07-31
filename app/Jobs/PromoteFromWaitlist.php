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
        $event = $this->event->fresh();

        if (! $event->is_full) {
            $nextReg = EventRegistration::where('event_id', $event->id)
                ->where('status', 'waitlist')
                ->orderBy('waitlisted_at')
                ->first();

            if ($nextReg) {
                $nextReg->update([
                    'status' => 'registered',
                    'registered_at' => now(),
                    'waitlisted_at' => null,
                ]);

                Notification::send(
                    $nextReg->user,
                    new PromotedFromWaitlist($event)
                );
            }
        }
    }
}
