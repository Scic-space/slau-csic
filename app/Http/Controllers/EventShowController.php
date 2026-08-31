<?php

namespace App\Http\Controllers;

use App\Events\EventRegistered;
use App\Http\Requests\StoreEventFeedbackRequest;
use App\Jobs\PromoteFromWaitlist;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventFeedback;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventShowController extends Controller
{
    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $allowed = '<p><br><b><i><u><strong><em><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><div><img><table><tr><td><th>';
        $html = strip_tags($html, $allowed);

        if ($html === '' || $html === false) {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//@*[starts-with(name(), "on")]') as $attr) {
            $attr->ownerElement->removeAttributeNode($attr);
        }

        $dangerousProtocols = ['javascript:', 'vbscript:', 'data:', 'file:'];
        foreach ($xpath->query('//@href | //@src | //@action | //@formaction') as $attr) {
            $value = strtolower(trim($attr->value));
            foreach ($dangerousProtocols as $protocol) {
                if (str_starts_with($value, $protocol)) {
                    $attr->ownerElement->setAttribute($attr->nodeName, '#');
                    break;
                }
            }
        }

        foreach ($xpath->query('//@style') as $attr) {
            $attr->ownerElement->removeAttributeNode($attr);
        }

        foreach ($xpath->query('//a') as $link) {
            $link->setAttribute('rel', 'noopener noreferrer');
            $link->setAttribute('target', '_blank');
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return null;
        }

        $cleaned = '';
        foreach ($body->childNodes as $child) {
            $cleaned .= $dom->saveHTML($child);
        }

        return $cleaned;
    }

    public function show(Event $event): Response
    {
        $event = Event::query()
            ->publiclyVisible()
            ->whereKey($event->getKey())
            ->with(['organizer', 'categories', 'instructors', 'recurrence'])
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->firstOrFail();

        $userRegistration = null;
        $canSubmitFeedback = false;
        $userFeedback = null;

        if (auth()->check()) {
            $userId = auth()->id();

            $userRegistration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->first();

            $hasAttended = $userRegistration && $userRegistration->hasAttended();
            $eventEnded = $event->end_date && $event->end_date->isPast();

            if ($hasAttended && $eventEnded) {
                $userFeedback = EventFeedback::where('event_id', $event->id)
                    ->where('user_id', $userId)
                    ->first();

                $canSubmitFeedback = is_null($userFeedback);
            }
        }

        $categories = EventCategory::active()->ordered()->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'color' => $c->color,
        ]);

        return Inertia::render('events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => $this->sanitizeHtml($event->description),
                'type' => $event->type,
                'start_date' => $event->start_date->toIso8601String(),
                'end_date' => $event->end_date?->toIso8601String(),
                'location' => $event->location,
                'banner_image' => $event->banner_image,
                'max_participants' => $event->max_participants,
                'registration_required' => $event->registration_required,
                'waitlist_enabled' => $event->waitlist_enabled,
                'registration_deadline' => $event->registration_deadline?->toIso8601String(),
                'is_public' => $event->is_public,
                'status' => $event->status,
                'requirements' => $this->sanitizeHtml($event->requirements),
                'registration_fee' => $event->registration_fee,
                'external_link' => $this->publicExternalLink($event->external_link),
                'is_recurring' => $event->is_recurring,
                'registered_count' => $event->registered_count,
                'is_full' => $event->is_full,
                'remaining_spots' => $event->remaining_spots,
                'organizer' => $event->organizer?->only(['id', 'name']),
                'categories' => $event->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'color' => $c->color,
                ]),
                'instructors' => $event->instructors->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'role' => $i->pivot->role,
                ]),
                'resources' => [],
                'user_registration' => $userRegistration ? [
                    'id' => $userRegistration->id,
                    'status' => $userRegistration->status,
                    'rsvp_status' => $userRegistration->rsvp_status,
                    'registered_at' => $userRegistration->registered_at?->toIso8601String(),
                    'waitlisted_at' => $userRegistration->waitlisted_at?->toIso8601String(),
                ] : null,
                'can_submit_feedback' => $canSubmitFeedback,
                'user_feedback' => $userFeedback ? [
                    'rating' => $userFeedback->rating,
                    'content_quality' => $userFeedback->content_quality,
                    'instructor_rating' => $userFeedback->instructor_rating,
                    'pace_rating' => $userFeedback->pace_rating,
                    'feedback_text' => $userFeedback->feedback_text,
                ] : null,
            ],
            'categories' => $categories,
        ]);
    }

    private function publicExternalLink(?string $url): ?string
    {
        if ($url === null || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return parse_url($url, PHP_URL_SCHEME) === 'https' ? $url : null;
    }

    public function rsvp(string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! auth()->check()) {
            return redirect()->route('auth.login');
        }

        if ($event->registration_deadline && now()->isAfter($event->registration_deadline)) {
            return redirect()->back()->with('flash', [
                'error' => 'Registration for this event has closed.',
            ]);
        }

        $data = [
            'status' => ($event->is_full && $event->waitlist_enabled) ? 'waitlist' : 'registered',
            'rsvp_status' => 'attending',
            'registered_at' => now(),
            'waitlisted_at' => ($event->is_full && $event->waitlist_enabled) ? now() : null,
        ];

        EventRegistration::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ],
            $data
        );

        EventRegistered::dispatch(auth()->user(), $event);

        $message = $event->is_full && $event->waitlist_enabled
            ? 'Event is full — you have been added to the waitlist.'
            : "You're confirmed for this event!";

        return redirect()->back()->with('flash', ['success' => $message]);
    }

    public function cancelRsvp(string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! auth()->check()) {
            return redirect()->route('auth.login');
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($registration) {
            $registration->update(['rsvp_status' => 'not_attending', 'status' => 'cancelled']);

            dispatch(new PromoteFromWaitlist($event));
        }

        return redirect()->back()->with('flash', ['success' => "You've declined this event."]);
    }

    public function register(string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! auth()->check()) {
            return redirect()->route('auth.login');
        }

        if ($event->registration_deadline && now()->isAfter($event->registration_deadline)) {
            return redirect()->back()->with('flash', [
                'error' => 'Registration for this event has closed.',
            ]);
        }

        if ($event->is_full && ! $event->waitlist_enabled) {
            return redirect()->back()->with('flash', ['error' => 'This event is full.']);
        }

        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return redirect()->back()->with('flash', ['error' => 'You are already registered for this event.']);
        }

        $data = [
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => $event->is_full ? 'waitlist' : 'registered',
            'registered_at' => now(),
            'waitlisted_at' => $event->is_full ? now() : null,
        ];

        if ($event->is_full) {
            EventRegistration::create($data);

            EventRegistered::dispatch(auth()->user(), $event);

            return redirect()->back()->with('flash', ['success' => 'Event is full — you have been added to the waitlist.']);
        }

        EventRegistration::create($data);

        EventRegistered::dispatch(auth()->user(), $event);

        return redirect()->back()->with('flash', ['success' => 'Successfully registered for this event!']);
    }

    public function unregister(string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! auth()->check()) {
            return redirect()->route('auth.login');
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($registration) {
            $registration->update(['status' => 'cancelled']);

            dispatch(new PromoteFromWaitlist($event));
        }

        return redirect()->back()->with('flash', ['success' => 'Successfully unregistered from event.']);
    }

    public function storeFeedback(string $slug, StoreEventFeedbackRequest $request): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! auth()->check()) {
            return redirect()->route('auth.login');
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $registration || ! $registration->hasAttended()) {
            return redirect()->back()->with('flash', ['error' => 'You must attend the event before submitting feedback.']);
        }

        if (! $event->end_date || ! $event->end_date->isPast()) {
            return redirect()->back()->with('flash', ['error' => 'Feedback can only be submitted after the event has ended.']);
        }

        $existing = EventFeedback::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($existing) {
            return redirect()->back()->with('flash', ['error' => 'You have already submitted feedback for this event.']);
        }

        EventFeedback::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'rating' => $request->integer('rating'),
            'content_quality' => $request->filled('content_quality') ? $request->integer('content_quality') : null,
            'instructor_rating' => $request->filled('instructor_rating') ? $request->integer('instructor_rating') : null,
            'pace_rating' => $request->filled('pace_rating') ? $request->integer('pace_rating') : null,
            'feedback_text' => $request->input('feedback_text'),
            'suggestions' => $request->input('suggestions'),
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        return redirect()->back()->with('flash', ['success' => 'Thank you for your feedback!']);
    }
}
