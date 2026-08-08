<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Event;
use Livewire\Component;

class OrganizerDashboard extends Component
{
    use GuardsPendingMembers;

    public ?int $selectedEventId = null;

    public Event $selectedEvent;

    public function mount(): void
    {
        $events = Event::where('organizer_id', auth()->id())
            ->orderBy('start_date', 'desc')
            ->get();

        if ($events->isNotEmpty()) {
            $this->selectedEventId = $events->first()->id;
            $this->selectedEvent = $events->first();
        }
    }

    public function selectEvent(int $eventId): void
    {
        $event = Event::where('organizer_id', auth()->id())->findOrFail($eventId);

        $this->selectedEventId = $event->id;
        $this->selectedEvent = $event;
    }

    public function render()
    {
        $events = Event::where('organizer_id', auth()->id())
            ->withCount([
                'registrations as registered_count' => fn ($q) => $q->where('status', 'registered'),
                'registrations as waitlist_count' => fn ($q) => $q->where('status', 'waitlist'),
                'registrations as attended_count' => fn ($q) => $q->whereNotNull('attended_at'),
                'feedback as feedback_count',
            ])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'slug' => $e->slug,
                'start_date' => $e->start_date->format('M j, Y'),
                'status' => $e->status,
                'registered_count' => $e->registered_count,
                'waitlist_count' => $e->waitlist_count,
                'attended_count' => $e->attended_count,
                'feedback_count' => $e->feedback_count,
                'max_participants' => $e->max_participants,
            ]);

        $registrations = collect();
        $feedbackData = collect();
        $averageRating = null;

        if ($this->selectedEventId) {
            $event = Event::where('organizer_id', auth()->id())->find($this->selectedEventId);

            if ($event) {
                $registrations = $event->registrations()
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'name' => $r->user?->name ?? 'Unknown',
                        'status' => $r->status,
                        'rsvp_status' => $r->rsvp_status,
                        'attended_at' => $r->attended_at?->format('M j, Y g:i A'),
                        'registered_at' => $r->registered_at?->format('M j, Y'),
                        'check_in_code' => $r->check_in_code,
                    ]);

                $feedbackData = $event->feedback()
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(fn ($f) => [
                        'id' => $f->id,
                        'name' => $f->user?->name ?? 'Anonymous',
                        'rating' => $f->rating,
                        'content_quality' => $f->content_quality,
                        'instructor_rating' => $f->instructor_rating,
                        'feedback_text' => $f->feedback_text,
                        'suggestions' => $f->suggestions,
                        'created_at' => $f->created_at->format('M j, Y'),
                    ]);

                if ($feedbackData->isNotEmpty()) {
                    $averageRating = round($feedbackData->avg('rating'), 1);
                }
            }
        }

        return view('livewire.organizer-dashboard', [
            'events' => $events,
            'registrations' => $registrations,
            'feedbackData' => $feedbackData,
            'averageRating' => $averageRating,
        ]);
    }
}
