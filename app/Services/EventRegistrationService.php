<?php

namespace App\Services;

use App\Events\EventRegistered;
use App\Jobs\PromoteFromWaitlist;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EventRegistrationService
{
    public function register(Event $event, User $user, bool $isRsvp = false): EventRegistration
    {
        $registration = $event->getConnection()->transaction(function () use ($event, $user, $isRsvp): EventRegistration {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            $this->ensureActive($event);
            $this->ensure($event->acceptsRegistrations(), 'Registration for this event has closed.');

            if ($isRsvp) {
                $this->ensure($event->acceptsRsvps(), 'RSVP for this event has closed.');
            }

            $registration = $event->registrations()->where('user_id', $user->id)->first();

            if ($registration && ! $registration->isCancelled()) {
                $this->ensure($isRsvp, 'You are already registered for this event.');
                $registration->update(['rsvp_status' => 'attending']);

                return $registration;
            }

            $isWaitlisted = $event->is_full;
            $this->ensure(! $isWaitlisted || $event->waitlist_enabled, 'This event is full.');

            return $event->registrations()->updateOrCreate(['user_id' => $user->id], [
                'status' => $isWaitlisted ? 'waitlist' : 'registered',
                'rsvp_status' => 'attending',
                'registered_at' => now(),
                'waitlisted_at' => $isWaitlisted ? now() : null,
            ]);
        });

        if ($registration->wasRecentlyCreated || $registration->wasChanged('status')) {
            EventRegistered::dispatch($user, $event);
        }

        return $registration;
    }

    public function maybe(Event $event, User $user): void
    {
        $event->getConnection()->transaction(function () use ($event, $user): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            $this->ensureActive($event);
            $this->ensure($event->acceptsRsvps(), 'RSVP for this event has closed.');
            $registration = $event->registrations()->firstOrNew(['user_id' => $user->id]);

            if (! $registration->exists) {
                $registration->status = 'cancelled';
                $registration->registered_at = now();
            }

            $registration->rsvp_status = 'maybe';
            $registration->save();
        });
    }

    public function cancel(Event $event, User $user): void
    {
        $shouldPromote = $event->getConnection()->transaction(function () use ($event, $user): bool {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            $this->ensureActive($event);
            $registration = $event->registrations()->where('user_id', $user->id)->first();

            if (! $registration) {
                return false;
            }

            $occupiedSpot = ! $registration->isCancelled() && ! $registration->isWaitlisted();
            $registration->update(['status' => 'cancelled', 'rsvp_status' => 'not_attending']);

            return $occupiedSpot;
        });

        if ($shouldPromote) {
            PromoteFromWaitlist::dispatch($event);
        }
    }

    private function ensureActive(Event $event): void
    {
        $this->ensure(! $event->hasEnded(), 'This event has completed.');
        $this->ensure(in_array($event->status, ['published', 'scheduled', 'ongoing'], true), 'Registration for this event has closed.');
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['registration' => $message]);
        }
    }
}
