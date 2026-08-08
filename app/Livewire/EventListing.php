<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Events')]
class EventListing extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $category = '';

    public string $type = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $filter = '';

    public int $perPage = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $isGuest = ! auth()->check();

        $featured = Event::where('is_public', true)
            ->whereIn('status', ['published', 'scheduled', 'ongoing'])
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();

        $query = Event::where('is_public', true)
            ->whereIn('status', ['published', 'scheduled', 'ongoing'])
            ->with(['organizer', 'categories', 'recurrence'])
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id));

        if ($this->search) {
            $searchTerm = str_replace(['%', '_'], ['\\%', '\\_'], $this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        if ($this->category) {
            $query->whereHas('categories', fn ($q) => $q->where('event_categories.slug', $this->category));
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->dateFrom) {
            $query->where('start_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('start_date', '<=', $this->dateTo);
        }

        match ($this->filter) {
            'upcoming' => $query->where('start_date', '>=', now()),
            'past' => $query->where('start_date', '<', now()),
            'favorites' => $query->whereHas('favoritedBy', fn ($q) => $q->where('user_id', auth()->id())),
            default => null,
        };

        $perPage = min($this->perPage, 48);

        $events = $query->orderBy('start_date', 'asc')
            ->paginate($perPage)
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'type' => $event->type,
                'start_date' => $event->start_date->toIso8601String(),
                'end_date' => $event->end_date?->toIso8601String(),
                'location' => $event->location,
                'description' => str($event->description)->limit(200),
                'max_participants' => $event->max_participants,
                'registered_count' => $event->registered_count,
                'registration_required' => $event->registration_required,
                'is_full' => $event->is_full,
                'remaining_spots' => $event->remaining_spots,
                'categories' => $event->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'color' => $c->color,
                ]),
                'organizer' => $event->organizer?->only(['id', 'name']),
                'is_recurring' => $event->is_recurring,
            ]);

        $categories = EventCategory::active()->ordered()->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'color' => $c->color,
        ]);

        $eventTypes = [
            ['value' => 'workshop', 'label' => 'Workshop'],
            ['value' => 'competition', 'label' => 'Competition'],
            ['value' => 'ctf', 'label' => 'CTF'],
            ['value' => 'bootcamp', 'label' => 'Bootcamp'],
            ['value' => 'awareness_campaign', 'label' => 'Awareness Campaign'],
            ['value' => 'talk', 'label' => 'Talk/Seminar'],
            ['value' => 'social', 'label' => 'Social'],
            ['value' => 'hackathon', 'label' => 'Hackathon'],
        ];

        return view('livewire.event-listing', [
            'events' => $events,
            'categories' => $categories,
            'eventTypes' => $eventTypes,
            'featuredEvent' => $featured,
            'isGuest' => $isGuest,
        ]);
    }
}
