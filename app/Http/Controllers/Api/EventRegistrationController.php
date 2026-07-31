<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkRegistrationRequest;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\RegistrationApprovedNotification;
use App\Notifications\RegistrationRejectedNotification;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function myEvents(Request $request)
    {
        $events = EventRegistration::where('user_id', $request->user()->id)
            ->with(['event.organizer', 'event.categories'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($reg) => [
                'registration' => [
                    'id' => $reg->id,
                    'status' => $reg->status,
                    'registered_at' => $reg->registered_at,
                    'attended_at' => $reg->attended_at,
                ],
                'event' => $this->eventService->formatEventList($reg->event),
            ]);

        return response()->json(['data' => $events]);
    }

    public function register(Request $request, Event $event)
    {
        if (! $event->registration_required) {
            return response()->json(['error' => 'Registration is not required for this event'], 400);
        }

        $check = $event->canMemberRegister($request->user());

        if (! $check['can_register']) {
            return response()->json(['error' => implode(' ', $check['errors'])], 400);
        }

        $registration = $this->eventService->registerMember($event, $request->user());

        return response()->json([
            'success' => true,
            'message' => $registration->status === 'waitlist'
                ? 'Event is full — you have been added to the waitlist'
                : 'Successfully registered for this event',
            'status' => $registration->status,
        ]);
    }

    public function unregister(Request $request, Event $event)
    {
        $this->eventService->unregisterMember($event, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Successfully unregistered from event',
        ]);
    }

    public function cancelRsvp(Request $request, Event $event)
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($registration) {
            $registration->update(['rsvp_status' => 'not_attending', 'status' => 'cancelled']);

            if ($registration->status === 'registered') {
                \App\Jobs\PromoteFromWaitlist::dispatch($event);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'RSVP cancelled',
        ]);
    }

    public function registrations(Request $request, Event $event)
    {
        if (! $request->user()->can('manage_registrations') && $event->organizer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $event->registrations()->with('user');

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->through(fn ($reg) => [
                'id' => $reg->id,
                'member' => ['id' => $reg->user->id, 'name' => $reg->user->name],
                'status' => $reg->status,
                'registered_at' => $reg->registered_at,
                'attended_at' => $reg->attended_at,
                'cancelled_at' => $reg->cancelled_at,
                'payment_completed' => $reg->payment_completed,
            ]);

        return response()->json($registrations);
    }

    public function approveRegistration(Event $event, $memberId)
    {
        if (! auth()->user()->can('manage_registrations') && $event->organizer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $memberId)
            ->first();

        if (! $registration) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        if ($event->is_full) {
            return response()->json(['error' => 'Event is full'], 400);
        }

        $registration->update([
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        $registration->user->notify(new RegistrationApprovedNotification($event));

        return response()->json([
            'success' => true,
            'message' => 'Registration approved',
        ]);
    }

    public function rejectRegistration(Request $request, Event $event, $memberId)
    {
        if (! auth()->user()->can('manage_registrations') && $event->organizer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $memberId)
            ->first();

        if (! $registration) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $request->validate(['reason' => 'nullable|string|max:1000']);

        $registration->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'notes' => $request->reason,
        ]);

        $registration->user->notify(new RegistrationRejectedNotification($event, $request->reason));

        return response()->json([
            'success' => true,
            'message' => 'Registration rejected',
        ]);
    }

    public function removeRegistration(Event $event, $memberId)
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $memberId)
            ->first();

        if (! $registration) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $isAuthorized = auth()->user()->can('manage_registrations') || $event->organizer_id === auth()->id();
        $isOwnRegistration = $registration->user_id === auth()->id();

        if (! $isAuthorized && ! $isOwnRegistration) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($isAuthorized) {
            $registration->delete();
            $message = 'Registration removed';
        } else {
            $registration->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $message = 'Registration cancelled';
        }

        \App\Jobs\PromoteFromWaitlist::dispatch($event);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function bulkRegister(BulkRegistrationRequest $request, Event $event)
    {
        if (! auth()->user()->can('manage_registrations') && $event->organizer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $results = $this->eventService->bulkRegister($event, $request->member_ids);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
