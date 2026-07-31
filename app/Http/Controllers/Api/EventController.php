<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelEventRequest;
use App\Http\Requests\StoreEventFeedbackRequest;
use App\Http\Requests\StoreEventPrerequisiteRequest;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\EventPrerequisite;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function index(Request $request)
    {
        $query = Event::where('is_public', true)
            ->whereIn('status', ['published', 'scheduled'])
            ->with(['organizer', 'categories', 'instructor']);

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($category = $request->category) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
        }

        if ($search = $request->search) {
            $searchTerm = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($instructorId = $request->instructor_id) {
            $query->where('instructor_id', $instructorId);
        }

        $sort = match ($request->sort) {
            'date_desc' => ['start_date', 'desc'],
            'capacity' => ['max_participants', 'desc'],
            'attendees' => ['created_at', 'desc'],
            default => ['start_date', 'asc'],
        };

        $events = $query->orderBy(...$sort)
            ->paginate($request->per_page ?? 20)
            ->through(fn ($event) => $this->eventService->formatEventList($event));

        return response()->json($events);
    }

    public function show(Event $event)
    {
        if (! in_array($event->status, ['published', 'scheduled', 'ongoing']) && ! auth()->user()?->can('view_events')) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->load(['organizer', 'instructor', 'categories', 'recurrence']);

        return response()->json([
            'data' => $this->eventService->formatEventDetail($event),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->can('create_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'virtual_link' => 'nullable|url|max:2048',
            'type' => 'required|in:workshop,competition,ctf,bootcamp,awareness_campaign,talk,social,hackathon',
            'max_participants' => 'nullable|integer|min:1',
            'registration_required' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'is_public' => 'boolean',
            'visibility' => 'nullable|in:public,members_only,invited_only',
            'registration_type' => 'nullable|in:first_come,approval_required,open',
            'registration_deadline' => 'nullable|date|before:start_date|after:now',
            'status' => 'required|in:draft,scheduled,published,cancelled',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'requirements' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced',
            'registration_fee' => 'nullable|numeric|min:0',
            'external_link' => 'nullable|url|max:2048',
        ]);

        $event = $this->eventService->createEvent($validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'event' => $this->eventService->formatEventList($event),
        ], 201);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->organizer_id !== $request->user()->id && ! $request->user()->can('edit_events')) {
            return response()->json(['error' => 'You can only edit your own events'], 403);
        }

        if ($event->start_date && $event->start_date->isPast()) {
            return response()->json(['error' => 'Cannot edit past events'], 400);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date|after:now',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'virtual_link' => 'nullable|url|max:2048',
            'type' => 'sometimes|required|in:workshop,competition,ctf,bootcamp,awareness_campaign,talk,social,hackathon',
            'max_participants' => 'nullable|integer|min:1',
            'registration_required' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'is_public' => 'boolean',
            'visibility' => 'nullable|in:public,members_only,invited_only',
            'registration_type' => 'nullable|in:first_come,approval_required,open',
            'registration_deadline' => 'nullable|date|before:start_date',
            'status' => 'sometimes|required|in:draft,scheduled,published,cancelled',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'requirements' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced',
            'registration_fee' => 'nullable|numeric|min:0',
            'external_link' => 'nullable|url|max:2048',
        ]);

        $event = $this->eventService->updateEvent($event, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'event' => $this->eventService->formatEventList($event),
        ]);
    }

    public function destroy(Event $event)
    {
        if ($event->organizer_id !== auth()->id() && ! auth()->user()->can('delete_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }

    public function publish(Event $event)
    {
        if ($event->organizer_id !== auth()->id() && ! auth()->user()->can('publish_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (empty($event->title) || empty($event->start_date)) {
            return response()->json(['error' => 'Cannot publish event without required fields (title, date)'], 400);
        }

        $event->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Event published successfully',
        ]);
    }

    public function cancel(CancelEventRequest $request, Event $event)
    {
        if ($event->organizer_id !== auth()->id() && ! auth()->user()->can('cancel_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->eventService->cancelEvent($event, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Event cancelled successfully. All registered members have been notified.',
        ]);
    }

    public function editPermission(Request $request, Event $event)
    {
        $canEdit = $this->eventService->canEdit($event, $request->user());

        return response()->json(['canEdit' => $canEdit]);
    }

    public function prerequisites(Event $event)
    {
        $event->load('prerequisites.prerequisiteEvent', 'prerequisites.requiredBadge');

        return response()->json([
            'data' => $event->prerequisites->map(fn ($p) => [
                'id' => $p->id,
                'prerequisite_event' => $p->prerequisiteEvent ? [
                    'id' => $p->prerequisiteEvent->id,
                    'title' => $p->prerequisiteEvent->title,
                ] : null,
                'required_badge' => $p->requiredBadge ? [
                    'id' => $p->requiredBadge->id,
                    'name' => $p->requiredBadge->name,
                ] : null,
                'required_skill_level' => $p->required_skill_level,
            ]),
        ]);
    }

    public function storePrerequisite(StoreEventPrerequisiteRequest $request, Event $event)
    {
        $prerequisite = EventPrerequisite::create([
            'event_id' => $event->id,
            'prerequisite_event_id' => $request->prerequisite_event_id,
            'required_badge_id' => $request->required_badge_id,
            'required_skill_level' => $request->required_skill_level,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prerequisite added',
            'prerequisite' => $prerequisite,
        ], 201);
    }

    public function storeFeedback(StoreEventFeedbackRequest $request, Event $event)
    {
        $feedback = EventFeedback::create([
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
            'rating' => $request->integer('rating'),
            'content_quality' => $request->filled('content_quality') ? $request->integer('content_quality') : null,
            'instructor_rating' => $request->filled('instructor_rating') ? $request->integer('instructor_rating') : null,
            'pace_rating' => $request->filled('pace_rating') ? $request->integer('pace_rating') : null,
            'feedback_text' => $request->input('feedback_text'),
            'suggestions' => $request->input('suggestions'),
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted',
            'feedback' => $feedback,
        ], 201);
    }

    public function feedback(Request $request, Event $event)
    {
        if (! $request->user()->can('view_event_feedback') && $event->organizer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $feedback = $event->feedback()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'member' => $f->is_anonymous ? null : ['id' => $f->user->id, 'name' => $f->user->name],
                'rating' => $f->rating,
                'content_quality' => $f->content_quality,
                'instructor_rating' => $f->instructor_rating,
                'feedback_text' => $f->feedback_text,
                'suggestions' => $f->suggestions,
                'created_at' => $f->created_at,
            ]);

        $aggregate = [
            'average_rating' => round($event->feedback()->avg('rating'), 1),
            'total_responses' => $event->feedback()->count(),
            'rating_distribution' => EventFeedback::where('event_id', $event->id)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),
        ];

        return response()->json([
            'data' => $feedback,
            'aggregate' => $aggregate,
        ]);
    }
}
