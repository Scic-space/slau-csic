<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicNetworkingController extends Controller
{
    public function __invoke(): Response
    {
        $upcomingEvents = Event::query()
            ->whereIn('type', ['talk', 'hackathon', 'awareness_campaign'])
            ->where('is_public', true)
            ->whereIn('status', ['published', 'scheduled'])
            ->where('start_date', '>=', now())
            ->with('categories')
            ->orderBy('start_date')
            ->take(4)
            ->get()
            ->map(fn (Event $e) => [
                'title' => $e->title,
                'slug' => $e->slug,
                'description' => $e->description,
                'type' => $e->type,
                'start_date' => $e->start_date->toIso8601String(),
                'end_date' => $e->end_date?->toIso8601String(),
                'location' => $e->location,
                'skill_level' => $e->skill_level,
                'categories' => $e->categories->map(fn ($cat) => [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'color' => $cat->color,
                ]),
            ]);

        $totalMembers = User::where('membership_status', 'active')->count();
        $totalEvents = Event::where('is_public', true)
            ->where('start_date', '<', now())
            ->count();
        $totalAttendees = EventRegistration::where('status', '!=', 'cancelled')
            ->distinct('user_id')
            ->count('user_id');

        return Inertia::render('public/About', [
            'upcomingEvents' => $upcomingEvents,
            'stats' => [
                'active_members' => $totalMembers,
                'events_hosted' => $totalEvents,
                'total_attendees' => $totalAttendees,
            ],
        ]);
    }
}
