<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRecurrence;
use Livewire\Component;

class EventEdit extends Component
{
    use GuardsPendingMembers;

    public Event $event;

    public string $title = '';

    public string $description = '';

    public string $type = 'workshop';

    public string $startDate = '';

    public string $endDate = '';

    public string $location = '';

    public ?int $maxParticipants = null;

    public bool $registrationRequired = false;

    public bool $waitlistEnabled = false;

    public bool $isPublic = true;

    public string $registrationDeadline = '';

    public string $rsvpDeadline = '';

    public string $requirements = '';

    public ?float $registrationFee = null;

    public string $externalLink = '';

    public bool $recurrenceEnabled = false;

    public string $recurrencePattern = 'weekly';

    public string $recurrenceEndsAt = '';

    public array $selectedCategories = [];

    public array $agendaItems = [];

    public function mount(Event $event): void
    {
        $this->event = $event->load(['organizer', 'categories', 'recurrence', 'agendaItems']);

        abort_if(auth()->id() !== $event->organizer_id && ! auth()->user()?->hasAnyRole(['admin', 'super-admin']), 403);

        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->type = $event->type;
        $this->startDate = $event->start_date->format('Y-m-d\TH:i');
        $this->endDate = $event->end_date?->format('Y-m-d\TH:i') ?? '';
        $this->location = $event->location ?? '';
        $this->maxParticipants = $event->max_participants;
        $this->registrationRequired = $event->registration_required ?? false;
        $this->waitlistEnabled = $event->waitlist_enabled ?? false;
        $this->isPublic = $event->is_public ?? true;
        $this->registrationDeadline = $event->registration_deadline?->format('Y-m-d\TH:i') ?? '';
        $this->rsvpDeadline = $event->rsvp_deadline?->format('Y-m-d\TH:i') ?? '';
        $this->requirements = $event->requirements ?? '';
        $this->registrationFee = $event->registration_fee;
        $this->externalLink = $event->external_link ?? '';
        $this->recurrenceEnabled = $event->recurrence !== null;
        $this->recurrencePattern = $event->recurrence?->pattern ?? 'weekly';
        $this->recurrenceEndsAt = $event->recurrence?->ends_at?->format('Y-m-d') ?? '';
        $this->selectedCategories = $event->categories->pluck('id')->toArray();

        $this->agendaItems = $event->agendaItems->map(fn ($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description ?? '',
            'speaker' => $item->speaker ?? '',
            'start_time' => substr((string) $item->start_time, 0, 5),
            'end_time' => $item->end_time ? substr((string) $item->end_time, 0, 5) : '',
            'type' => $item->type,
        ])->toArray();
    }

    public function addAgendaItem(): void
    {
        $this->agendaItems[] = [
            'id' => null,
            'title' => '',
            'description' => '',
            'speaker' => '',
            'start_time' => '',
            'end_time' => '',
            'type' => 'session',
        ];
    }

    public function removeAgendaItem(int $index): void
    {
        unset($this->agendaItems[$index]);
        $this->agendaItems = array_values($this->agendaItems);
    }

    public function toggleCategory(int $id): void
    {
        if (in_array($id, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$id]));
        } else {
            $this->selectedCategories[] = $id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'location' => ['nullable', 'string', 'max:255'],
            'maxParticipants' => ['nullable', 'integer', 'min:1'],
            'registrationFee' => ['nullable', 'numeric', 'min:0'],
            'registrationDeadline' => ['nullable', 'date'],
            'rsvpDeadline' => ['nullable', 'date', 'after_or_equal:now'],
            'externalLink' => ['nullable', 'url'],
            'recurrencePattern' => ['required_if:recurrenceEnabled,true'],
            'agendaItems.*.title' => ['required_with:agendaItems.*.start_time', 'string', 'max:255'],
            'agendaItems.*.start_time' => ['nullable', 'date_format:H:i'],
            'agendaItems.*.end_time' => ['nullable', 'date_format:H:i', 'after:agendaItems.*.start_time'],
            'agendaItems.*.type' => ['required', 'string'],
        ]);

        $this->event->update([
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
        ]);

        if ($this->recurrenceEnabled) {
            EventRecurrence::updateOrCreate(
                ['event_id' => $this->event->id],
                [
                    'pattern' => $this->recurrencePattern,
                    'interval' => 1,
                    'ends_at' => $this->recurrenceEndsAt ? \Carbon\Carbon::parse($this->recurrenceEndsAt)->endOfDay() : null,
                ]
            );
        } else {
            $this->event->recurrence()->delete();
        }

        $this->event->categories()->sync($this->selectedCategories);

        $existingIds = $this->event->agendaItems()->pluck('id')->toArray();
        $keepIds = [];

        foreach ($this->agendaItems as $item) {
            $data = [
                'title' => $item['title'],
                'description' => $item['description'] ?: null,
                'speaker' => $item['speaker'] ?: null,
                'start_time' => $item['start_time'] ?: null,
                'end_time' => $item['end_time'] ?: null,
                'type' => $item['type'],
            ];

            if ($item['id']) {
                $keepIds[] = $item['id'];
                $this->event->agendaItems()->where('id', $item['id'])->update($data);
            } else {
                $agendaItem = $this->event->agendaItems()->create($data);
                $keepIds[] = $agendaItem->id;
            }
        }

        $toDelete = array_diff($existingIds, $keepIds);
        if (! empty($toDelete)) {
            $this->event->agendaItems()->whereIn('id', $toDelete)->delete();
        }

        session()->flash('flash', ['success' => 'Event updated successfully!']);

        $this->redirectRoute('events.show', $this->event->slug);
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

        return view('livewire.event-edit', [
            'categories' => $categories,
        ]);
    }
}
