<?php

namespace App\Livewire;

use App\Events\EventRegistered;
use App\Jobs\PromoteFromWaitlist;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventFeedback;
use App\Models\EventRegistration;
use Livewire\Component;

class EventDetails extends Component
{
    public Event $event;

    public ?string $confirmCancelRsvpId = null;

    public bool $feedbackOpen = false;

    public int $feedbackRating = 5;

    public ?int $feedbackContentQuality = null;

    public ?int $feedbackInstructorRating = null;

    public ?int $feedbackPaceRating = null;

    public string $feedbackText = '';

    public string $feedbackSuggestions = '';

    public bool $feedbackAnonymous = false;

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'organizer', 'categories', 'instructors', 'resources', 'recurrence', 'agendaItems',
        ]);

        abort_unless(in_array($event->status, ['published', 'scheduled', 'ongoing', 'completed']), 404);
    }

    public function rsvpMaybe(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        if ($this->event->rsvp_deadline && now()->isAfter($this->event->rsvp_deadline)) {
            $this->dispatch('toast-show', message: 'RSVP for this event has closed.', type: 'error');

            return;
        }

        EventRegistration::updateOrCreate(
            [
                'event_id' => $this->event->id,
                'user_id' => auth()->id(),
            ],
            [
                'rsvp_status' => 'maybe',
            ]
        );

        $this->dispatch('toast-show', message: "You're tentatively marked as 'Maybe'.", type: 'success');
    }

    public function rsvp(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        if ($this->event->registration_deadline && now()->isAfter($this->event->registration_deadline)) {
            $this->dispatch('toast-show', message: 'Registration for this event has closed.', type: 'error');

            return;
        }

        $data = [
            'status' => ($this->event->is_full && $this->event->waitlist_enabled) ? 'waitlist' : 'registered',
            'rsvp_status' => 'attending',
            'registered_at' => now(),
            'waitlisted_at' => ($this->event->is_full && $this->event->waitlist_enabled) ? now() : null,
        ];

        EventRegistration::updateOrCreate(
            [
                'event_id' => $this->event->id,
                'user_id' => auth()->id(),
            ],
            $data
        );

        EventRegistered::dispatch(auth()->user(), $this->event);

        $message = $this->event->is_full && $this->event->waitlist_enabled
            ? 'Event is full — you have been added to the waitlist.'
            : "You're confirmed for this event!";

        $this->dispatch('toast-show', message: $message, type: 'success');
    }

    public function cancelRsvp(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        $registration = EventRegistration::where('event_id', $this->event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($registration) {
            $registration->update(['rsvp_status' => 'not_attending', 'status' => 'cancelled']);

            dispatch(new PromoteFromWaitlist($this->event));
        }

        $this->confirmCancelRsvpId = null;

        $this->dispatch('toast-show', message: "You've declined this event.", type: 'success');
    }

    public function register(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        if ($this->event->registration_deadline && now()->isAfter($this->event->registration_deadline)) {
            $this->dispatch('toast-show', message: 'Registration for this event has closed.', type: 'error');

            return;
        }

        if ($this->event->is_full && ! $this->event->waitlist_enabled) {
            $this->dispatch('toast-show', message: 'This event is full.', type: 'error');

            return;
        }

        $existing = EventRegistration::where('event_id', $this->event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $this->dispatch('toast-show', message: 'You are already registered for this event.', type: 'error');

            return;
        }

        EventRegistration::create([
            'event_id' => $this->event->id,
            'user_id' => auth()->id(),
            'status' => $this->event->is_full ? 'waitlist' : 'registered',
            'registered_at' => now(),
            'waitlisted_at' => $this->event->is_full ? now() : null,
        ]);

        EventRegistered::dispatch(auth()->user(), $this->event);

        $message = $this->event->is_full
            ? 'Event is full — you have been added to the waitlist.'
            : 'Successfully registered for this event!';

        $this->dispatch('toast-show', message: $message, type: 'success');
    }

    public function unregister(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        $registration = EventRegistration::where('event_id', $this->event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($registration) {
            $registration->update(['status' => 'cancelled']);

            dispatch(new PromoteFromWaitlist($this->event));
        }

        $this->dispatch('toast-show', message: 'Successfully unregistered from event.', type: 'success');
    }

    public function submitFeedback(): void
    {
        $this->ensureAuthenticated();

        if (! $this->ensureApproved()) {
            return;
        }

        $this->validate([
            'feedbackRating' => 'required|integer|min:1|max:5',
            'feedbackContentQuality' => 'nullable|integer|min:1|max:5',
            'feedbackInstructorRating' => 'nullable|integer|min:1|max:5',
            'feedbackPaceRating' => 'nullable|integer|min:1|max:5',
            'feedbackText' => 'nullable|string|max:2000',
            'feedbackSuggestions' => 'nullable|string|max:2000',
            'feedbackAnonymous' => 'boolean',
        ]);

        $registration = EventRegistration::where('event_id', $this->event->id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $registration || ! $registration->hasAttended()) {
            $this->dispatch('toast-show', message: 'You must attend the event before submitting feedback.', type: 'error');

            return;
        }

        if (! $this->event->end_date || ! $this->event->end_date->isPast()) {
            $this->dispatch('toast-show', message: 'Feedback can only be submitted after the event has ended.', type: 'error');

            return;
        }

        $existing = EventFeedback::where('event_id', $this->event->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($existing) {
            $this->dispatch('toast-show', message: 'You have already submitted feedback for this event.', type: 'error');

            return;
        }

        EventFeedback::create([
            'event_id' => $this->event->id,
            'user_id' => auth()->id(),
            'rating' => $this->feedbackRating,
            'content_quality' => $this->feedbackContentQuality,
            'instructor_rating' => $this->feedbackInstructorRating,
            'pace_rating' => $this->feedbackPaceRating,
            'feedback_text' => $this->feedbackText ?: null,
            'suggestions' => $this->feedbackSuggestions ?: null,
            'is_anonymous' => $this->feedbackAnonymous,
        ]);

        $this->feedbackOpen = false;
        $this->feedbackText = '';

        $this->dispatch('toast-show', message: 'Thank you for your feedback!', type: 'success');
    }

    public function toggleFavorite(): void
    {
        $this->ensureAuthenticated();

        $user = auth()->user();

        $existing = $user->favoriteEvents()->where('event_id', $this->event->id)->first();

        if ($existing) {
            $user->favoriteEvents()->detach($this->event->id);
            $this->dispatch('toast-show', message: 'Removed from favorites.', type: 'success');
        } else {
            $user->favoriteEvents()->attach($this->event->id);
            $this->dispatch('toast-show', message: 'Added to favorites.', type: 'success');
        }
    }

    public function render()
    {
        $user = auth()->user();

        $userRegistration = null;
        $userFeedback = null;
        $canSubmitFeedback = false;

        if ($user) {
            $userId = $user->id;

            $userRegistration = EventRegistration::where('event_id', $this->event->id)
                ->where('user_id', $userId)
                ->first();

            $hasAttended = $userRegistration && $userRegistration->hasAttended();
            $eventEnded = $this->event->end_date && $this->event->end_date->isPast();

            if ($hasAttended && $eventEnded) {
                $userFeedback = EventFeedback::where('event_id', $this->event->id)
                    ->where('user_id', $userId)
                    ->first();

                $canSubmitFeedback = is_null($userFeedback);
            }
        }

        $isFavorited = $user?->favoriteEvents()->where('event_id', $this->event->id)->exists() ?? false;

        $agendaItems = $this->event->agendaItems;

        $checkInCode = $userRegistration?->check_in_code;

        $hasCertificate = $user && $userRegistration && $userRegistration->hasAttended()
            && $this->event->end_date && $this->event->end_date->isPast();

        $categories = EventCategory::active()->ordered()->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'color' => $c->color,
        ]);

        $eventTypes = [
            'workshop' => 'Workshop',
            'competition' => 'Competition',
            'ctf' => 'CTF',
            'bootcamp' => 'Bootcamp',
            'awareness_campaign' => 'Awareness Campaign',
            'talk' => 'Talk/Seminar',
            'social' => 'Social',
            'hackathon' => 'Hackathon',
        ];

        $relatedEvents = collect();
        if ($this->event->relationLoaded('categories') && $this->event->categories->isNotEmpty()) {
            $categoryIds = $this->event->categories->pluck('id');
            $relatedEvents = Event::query()
                ->where('id', '!=', $this->event->id)
                ->whereIn('status', ['published', 'scheduled', 'ongoing'])
                ->whereHas('categories', fn ($q) => $q->whereIn('event_categories.id', $categoryIds))
                ->with('categories')
                ->take(4)
                ->orderBy('start_date')
                ->get();
        }

        $recentFeedbacks = EventFeedback::where('event_id', $this->event->id)
            ->where('is_anonymous', false)
            ->latest()
            ->limit(3)
            ->with('user')
            ->get();

        $feedbackStats = [
            'average_rating' => $this->event->average_rating,
            'feedback_count' => $this->event->feedback_count,
            'rating_distribution' => $this->event->rating_distribution,
        ];

        return view('livewire.event-details', [
            'userRegistration' => $userRegistration,
            'userFeedback' => $userFeedback,
            'canSubmitFeedback' => $canSubmitFeedback,
            'categories' => $categories,
            'eventTypes' => $eventTypes,
            'relatedEvents' => $relatedEvents,
            'isFavorited' => $isFavorited,
            'agendaItems' => $agendaItems,
            'checkInCode' => $checkInCode,
            'hasCertificate' => $hasCertificate,
            'recentFeedbacks' => $recentFeedbacks,
            'feedbackStats' => $feedbackStats,
        ]);
    }

    private function ensureAuthenticated(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('auth.login');
        }
    }

    private function ensureApproved(): bool
    {
        if (auth()->user()?->isPendingApproval()) {
            $this->redirectRoute('dashboard');

            return false;
        }

        return true;
    }
}
