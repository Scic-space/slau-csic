<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Event Calendar')]
class EventCalendar extends Component
{
    public function render()
    {
        $user = Auth::user();

        $myRegisteredEventIds = collect();
        if ($user) {
            $myRegisteredEventIds = $user->eventRegistrations()
                ->whereIn('status', ['registered', 'attended'])
                ->pluck('event_id');
        }

        $events = Event::where('is_public', true)
            ->whereIn('status', ['published', 'scheduled', 'ongoing'])
            ->where('start_date', '>=', now()->subMonths(3))
            ->where('start_date', '<=', now()->addMonths(6))
            ->with('categories')
            ->orderBy('start_date')
            ->get()
            ->map(fn (Event $event) => [
                'id' => (string) $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date->toIso8601String(),
                'color' => $event->categories->first()?->color ?? '#6366f1',
                'textColor' => '#ffffff',
                'type' => $event->type,
                'location' => $event->location,
                'description' => $event->description,
                'slug' => $event->slug,
                'url' => route('events.show', $event->slug),
                'categoryIds' => $event->categories->pluck('id'),
                'is_recurring' => $event->is_recurring,
                'max_participants' => $event->max_participants,
                'registration_required' => $event->registration_required,
                'is_registered' => $myRegisteredEventIds->contains($event->id),
            ]);

        $categories = EventCategory::active()->ordered()->get()->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'color' => $cat->color,
            'icon' => $cat->icon,
        ]);

        return view('livewire.event-calendar', [
            'events' => $events,
            'categories' => $categories,
        ]);
    }
}
