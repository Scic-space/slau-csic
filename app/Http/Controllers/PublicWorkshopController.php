<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventFeedback;
use App\Models\EventRegistration;
use Inertia\Inertia;
use Inertia\Response;

class PublicWorkshopController extends Controller
{
    public function __invoke(): Response
    {
        $upcomingWorkshops = Event::query()
            ->where('type', 'workshop')
            ->where('is_public', true)
            ->whereIn('status', ['published', 'scheduled'])
            ->where('start_date', '>=', now())
            ->with(['organizer', 'categories', 'instructors'])
            ->withCount('registrations')
            ->orderBy('start_date')
            ->take(6)
            ->get()
            ->map(fn (Event $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'slug' => $e->slug,
                'description' => str($e->description)->limit(200),
                'start_date' => $e->start_date->toIso8601String(),
                'end_date' => $e->end_date?->toIso8601String(),
                'location' => $e->location,
                'skill_level' => $e->skill_level,
                'max_participants' => $e->max_participants,
                'registrations_count' => $e->registrations_count,
                'registration_required' => $e->registration_required,
                'is_full' => $e->max_participants && $e->registrations_count >= $e->max_participants,
                'learning_objectives' => $e->learning_objectives,
                'requirements' => $e->requirements,
                'categories' => $e->categories->map(fn ($cat) => [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'color' => $cat->color,
                ]),
                'organizer' => [
                    'name' => $e->organizer->name,
                ],
                'instructors' => $e->instructors->map(fn ($u) => [
                    'name' => $u->name,
                ]),
            ]);

        $totalWorkshops = Event::where('type', 'workshop')->published()->count();

        $totalAttendees = EventRegistration::query()
            ->whereHas('event', fn ($q) => $q->where('type', 'workshop'))
            ->where('status', '!=', 'cancelled')
            ->distinct('user_id')
            ->count('user_id');

        $totalFeedback = EventFeedback::query()
            ->whereHas('event', fn ($q) => $q->where('type', 'workshop'))
            ->count();

        $averageRating = EventFeedback::query()
            ->whereHas('event', fn ($q) => $q->where('type', 'workshop'))
            ->whereNotNull('rating')
            ->avg('rating');

        $categories = EventCategory::active()
            ->ordered()
            ->whereHas('events', fn ($q) => $q->where('type', 'workshop'))
            ->withCount(['events' => fn ($q) => $q->where('type', 'workshop')])
            ->get()
            ->map(fn (EventCategory $cat) => [
                'name' => $cat->name,
                'slug' => $cat->slug,
                'color' => $cat->color,
                'icon' => $cat->icon,
                'events_count' => $cat->events_count,
            ]);

        $pastHighlights = Event::query()
            ->where('type', 'workshop')
            ->where('is_public', true)
            ->where('start_date', '<', now())
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withAvg('feedback', 'rating')
            ->orderByDesc('start_date')
            ->take(3)
            ->get()
            ->map(fn (Event $e) => [
                'title' => $e->title,
                'start_date' => $e->start_date->toIso8601String(),
                'registered_count' => $e->registered_count ?? 0,
                'average_rating' => round($e->feedback_avg_rating ?? 0, 1),
                'skill_level' => $e->skill_level,
            ]);

        $topInstructors = Event::query()
            ->where('type', 'workshop')
            ->where('start_date', '<', now())
            ->whereNotNull('instructor_id')
            ->with('instructor')
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->get()
            ->groupBy('instructor_id')
            ->map(fn ($events, $id) => [
                'name' => $events->first()->instructor->name ?? 'Unknown',
                'workshops_count' => $events->count(),
                'average_rating' => round($events->avg(fn ($e) => $e->feedback_avg_rating ?? 0), 1),
                'total_feedback' => $events->sum('feedback_count'),
            ])
            ->values()
            ->sortByDesc('workshops_count')
            ->take(3)
            ->values();

        return Inertia::render('public/WorkshopLanding', [
            'upcomingWorkshops' => $upcomingWorkshops,
            'categories' => $categories,
            'pastHighlights' => $pastHighlights,
            'topInstructors' => $topInstructors,
            'stats' => [
                'total_workshops' => $totalWorkshops,
                'total_attendees' => $totalAttendees,
                'total_feedback' => $totalFeedback,
                'average_rating' => round($averageRating ?? 0, 1),
            ],
        ]);
    }
}
