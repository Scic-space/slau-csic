<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use Livewire\Component;

class EventCreate extends Component
{
    public string $title = '';

    public string $description = '';

    public string $type = 'workshop';

    public string $startDate = '';

    public string $endDate = '';

    public string $location = '';

    public ?int $maxParticipants = null;

    public bool $registrationRequired = true;

    public bool $waitlistEnabled = false;

    public bool $isPublic = true;

    public string $registrationDeadline = '';

    public string $rsvpDeadline = '';

    public string $requirements = '';

    public ?float $registrationFee = null;

    public string $externalLink = '';

    public array $selectedCategories = [];

    public function create(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'startDate' => ['required', 'date', 'after_or_equal:now'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'location' => ['nullable', 'string', 'max:255'],
            'maxParticipants' => ['nullable', 'integer', 'min:1'],
            'registrationFee' => ['nullable', 'numeric', 'min:0'],
            'registrationDeadline' => ['nullable', 'date'],
            'rsvpDeadline' => ['nullable', 'date'],
            'externalLink' => ['nullable', 'url'],
        ]);

        $event = Event::create([
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate ?: null,
            'location' => $this->location ?: null,
            'max_participants' => $this->maxParticipants ?: null,
            'registration_required' => $this->registrationRequired,
            'waitlist_enabled' => $this->waitlistEnabled,
            'is_public' => $this->isPublic,
            'registration_deadline' => $this->registrationDeadline ?: null,
            'rsvp_deadline' => $this->rsvpDeadline ?: null,
            'requirements' => $this->requirements,
            'registration_fee' => $this->registrationFee ?: null,
            'external_link' => $this->externalLink ?: null,
            'organizer_id' => auth()->id(),
            'status' => 'draft',
        ]);

        $event->categories()->sync($this->selectedCategories);

        session()->flash('flash', ['success' => 'Event created! Now add more details.']);

        $this->redirectRoute('events.edit', $event->slug);
    }

    public function toggleCategory(int $id): void
    {
        if (in_array($id, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$id]));
        } else {
            $this->selectedCategories[] = $id;
        }
    }

    public function render()
    {
        $categories = EventCategory::active()->ordered()->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'color' => $c->color,
            'icon' => $c->icon,
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

        return view('livewire.event-create', [
            'categories' => $categories,
            'eventTypes' => $eventTypes,
        ]);
    }
}
