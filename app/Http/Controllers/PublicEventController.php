<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
    public function __invoke(): Response
    {
        $events = Event::query()
            ->publiclyVisible()
            ->with('categories')
            ->orderBy('start_date')
            ->get()
            ->map(fn (Event $event): array => [
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => str(strip_tags($event->description))->limit(180)->toString(),
                'type' => $event->type,
                'start_date' => $event->start_date->toIso8601String(),
                'end_date' => $event->end_date?->toIso8601String(),
                'location' => $event->location,
                'banner_image' => $event->banner_image,
                'display_status' => $event->publicStatus(),
                'categories' => $event->categories->map(fn ($category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'color' => $category->color,
                ]),
            ])
            ->groupBy('display_status');

        return Inertia::render('public/Events', [
            'events' => [
                'upcoming' => $events->get('upcoming', collect())->values(),
                'ongoing' => $events->get('ongoing', collect())->values(),
                'completed' => $events->get('completed', collect())->sortByDesc('start_date')->values(),
            ],
        ]);
    }
}
