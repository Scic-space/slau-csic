<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicHomeController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $upcomingEvents = Event::where('is_public', true)
            ->whereIn('status', ['published', 'scheduled'])
            ->where('start_date', '>=', now())
            ->with(['organizer', 'categories'])
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => $event->description,
                'type' => $event->type,
                'start_date' => $event->start_date->toIso8601String(),
                'end_date' => $event->end_date?->toIso8601String(),
                'location' => $event->location,
                'skill_level' => $event->skill_level,
                'is_recurring' => $event->is_recurring,
                'categories' => $event->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug, 'color' => $c->color]),
            ]);

        $announcements = Announcement::published()
            ->where('audience', 'all')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('published_at', 'desc')
            ->take(2)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'type' => $a->type,
                'published_at' => $a->published_at?->toIso8601String(),
            ]);

        $projectCount = Project::count();
        $memberCount = User::query()->where('membership_status', 'active')->count();
        $eventCount = Event::where('is_public', true)->whereIn('status', ['published', 'scheduled', 'ongoing'])->count();

        return Inertia::render('public/Home', [
            'upcomingEvents' => $upcomingEvents,
            'announcements' => $announcements,
            'stats' => [
                'projects' => $projectCount,
                'members' => $memberCount,
                'events' => $eventCount,
            ],
        ]);
    }
}
