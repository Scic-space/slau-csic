<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyEvents extends Component
{
    use GuardsPendingMembers;

    public function render()
    {
        $user = Auth::user();
        $now = now();

        $upcomingEvents = Event::whereHas('registrations', fn ($q) => $q->where('user_id', $user->id)->where('status', 'registered'))
            ->where('start_date', '>', $now)
            ->with('categories')
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->orderBy('start_date')
            ->get()
            ->map(fn ($e) => $this->mapEvent($e));

        $pastEvents = Event::whereHas('registrations', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('attended_at'))
            ->where('start_date', '<', $now)
            ->with('categories')
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($e) => $this->mapEvent($e));

        $instructedEvents = Event::where('organizer_id', $user->id)
            ->with('categories')
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($e) => $this->mapEvent($e));

        $favoriteEvents = $user->favoriteEvents()
            ->with('categories')
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($e) => $this->mapEvent($e));

        $pendingFeedback = Event::whereHas('registrations', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('attended_at'))
            ->where('end_date', '<', $now)
            ->whereDoesntHave('feedback', fn ($q) => $q->where('user_id', $user->id))
            ->with('categories')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($e) => $this->mapEvent($e));

        return view('livewire.my-events', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'instructedEvents' => $instructedEvents,
            'pendingFeedback' => $pendingFeedback,
            'favoriteEvents' => $favoriteEvents,
        ]);
    }

    private function mapEvent($event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'type' => $event->type,
            'start_date' => $event->start_date->toIso8601String(),
            'end_date' => $event->end_date?->toIso8601String(),
            'location' => $event->location,
            'status' => $event->status,
            'is_full' => $event->is_full,
            'registered_count' => $event->registered_count,
            'max_participants' => $event->max_participants,
            'registration_required' => $event->registration_required,
            'categories' => $event->categories->map(fn ($c) => [
                'name' => $c->name, 'color' => $c->color,
            ]),
        ];
    }
}
