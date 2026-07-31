<?php

namespace App\Services;

use App\Events\EventRegistered;
use App\Jobs\PromoteFromWaitlist;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventCancelledNotification;

class EventService
{
    public function createEvent(array $data, User $organizer): Event
    {
        $event = Event::create(array_merge($data, [
            'organizer_id' => $organizer->id,
            'end_date' => $data['end_date'] ?? $data['start_date'],
        ]));

        event(new EventRegistered($organizer, $event));

        return $event;
    }

    public function updateEvent(Event $event, array $data): Event
    {
        if (isset($data['max_participants'])) {
            $activeRegistrations = $event->registrations()->where('status', 'registered')->count();

            if ($data['max_participants'] < $activeRegistrations) {
                abort(400, "Cannot reduce capacity below {$activeRegistrations} current registrations");
            }
        }

        $event->update($data);

        return $event;
    }

    public function cancelEvent(Event $event, ?string $reason = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($event, $reason) {
            $event->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $registeredMembers = $event->registrations()
                ->where('status', 'registered')
                ->with('user')
                ->get();

            foreach ($registeredMembers as $registration) {
                $registration->user->notify(new EventCancelledNotification($event, $reason));
            }

            $event->registrations()->where('status', 'registered')->update(['status' => 'cancelled']);
        });
    }

    public function registerMember(Event $event, User $member): EventRegistration
    {
        $allowedCount = $event->allowedMembers()->count();

        if ($allowedCount > 0 && ! $event->allowedMembers()->where('user_id', $member->id)->exists()) {
            abort(403, 'You are not in the allowed members list for this event');
        }

        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $member->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'cancelled') {
                $existing->update([
                    'status' => $event->is_full && $event->waitlist_enabled ? 'waitlist' : 'registered',
                    'registered_at' => now(),
                    'cancelled_at' => null,
                ]);

                return $existing;
            }

            abort(409, 'You are already registered for this event');
        }

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'registered_at' => now(),
            'status' => $event->registration_type === 'approval_required'
                ? 'registered'
                : ($event->is_full && $event->waitlist_enabled ? 'waitlist' : 'registered'),
            'waitlisted_at' => $event->is_full && $event->waitlist_enabled ? now() : null,
        ]);

        event(new EventRegistered($member, $event));

        return $registration;
    }

    public function unregisterMember(Event $event, User $member): void
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $member->id)
            ->first();

        if (! $registration) {
            abort(404, 'You are not registered for this event');
        }

        if ($registration->status === 'cancelled') {
            abort(400, 'Registration is already cancelled');
        }

        $wasWaitlisted = $registration->isWaitlisted();

        $registration->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        if (! $wasWaitlisted) {
            dispatch(new PromoteFromWaitlist($event));
        }
    }

    public function markAttendance(Event $event, int $memberId, string $status): EventAttendance
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $memberId)
            ->where('status', 'registered')
            ->first();

        if (! $registration) {
            abort(400, 'Member is not registered for this event');
        }

        $attendance = EventAttendance::updateOrCreate(
            ['event_id' => $event->id, 'member_id' => $memberId],
            [
                'status' => $status,
                'checked_in_at' => $status === 'present' ? now() : null,
                'recorded_at' => now(),
            ]
        );

        if ($status === 'present') {
            $registration->update(['attended_at' => now()]);
        }

        return $attendance;
    }

    public function bulkRegister(Event $event, array $memberIds): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($event, $memberIds) {
            $results = ['registered' => 0, 'waitlisted' => 0, 'duplicates' => 0, 'errors' => []];
            $allowedMemberIds = $event->allowedMembers()->pluck('user_id');
            $isRestricted = $allowedMemberIds->isNotEmpty();

            foreach ($memberIds as $memberId) {
                $user = User::find($memberId);
                if (! $user || ! $user->isActiveMember()) {
                    $results['errors'][] = "User #{$memberId} is not an active member";

                    continue;
                }

                if ($isRestricted && ! $allowedMemberIds->contains($memberId)) {
                    $results['errors'][] = "User #{$memberId} is not in the allowed members list";

                    continue;
                }

                $existing = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $memberId)
                    ->exists();

                if ($existing) {
                    $results['duplicates']++;

                    continue;
                }

                if ($event->is_full && ! $event->waitlist_enabled) {
                    $results['errors'][] = "Event is full, user #{$memberId} not registered";

                    continue;
                }

                EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $memberId,
                    'registered_at' => now(),
                    'status' => $event->is_full ? 'waitlist' : 'registered',
                    'waitlisted_at' => $event->is_full ? now() : null,
                ]);

                if ($event->is_full) {
                    $results['waitlisted']++;
                } else {
                    $results['registered']++;
                }
            }

            return $results;
        });
    }

    public function bulkAttendance(Event $event, array $attendanceData): array
    {
        $results = ['recorded' => 0, 'errors' => []];

        foreach ($attendanceData as $entry) {
            try {
                $this->markAttendance($event, $entry['member_id'], $entry['status']);
                $results['recorded']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    public function canEdit(Event $event, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $event->organizer_id === $user->id || $user->can('edit_events');
    }

    public function canManageRegistrations(Event $event, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('manage_registrations') || $event->organizer_id === $user->id;
    }

    public function canManageAttendance(Event $event, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('manage_attendance') || $event->organizer_id === $user->id;
    }

    public function formatEventList($event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'type' => $event->type,
            'description' => str($event->description)->limit(200),
            'start_date' => $event->start_date?->toISOString(),
            'end_date' => $event->end_date?->toISOString(),
            'location' => $event->location,
            'virtual_link' => $event->virtual_link,
            'max_participants' => $event->max_participants,
            'registration_required' => $event->registration_required,
            'registration_deadline' => $event->registration_deadline?->toISOString(),
            'is_full' => $event->is_full,
            'remaining_spots' => $event->remaining_spots,
            'status' => $event->status,
            'visibility' => $event->visibility,
            'skill_level' => $event->skill_level,
            'organizer' => $event->organizer?->only(['id', 'name']),
            'instructor' => $event->instructor?->only(['id', 'name']),
            'categories' => $event->relationLoaded('categories')
                ? $event->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])
                : [],
        ];
    }

    public function formatEventDetail($event): array
    {
        $detail = $this->formatEventList($event);
        $detail['description'] = $event->description;
        $detail['requirements'] = $event->requirements;
        $detail['learning_objectives'] = $event->learning_objectives;
        $detail['registration_fee'] = $event->registration_fee;
        $detail['registration_type'] = $event->registration_type;
        $detail['external_link'] = $event->external_link;
        $detail['is_recurring'] = $event->is_recurring;
        $detail['registered_count'] = $event->registered_count;
        $detail['cancelled_at'] = $event->cancelled_at?->toISOString();
        $detail['instructors'] = $event->relationLoaded('instructors')
            ? $event->instructors->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'role' => $i->pivot->role])
            : [];

        return $detail;
    }
}
