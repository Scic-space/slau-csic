<?php

use App\Livewire\EventListing;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows only published public upcoming events on the landing page', function () {
    $publicEvent = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDay(),
    ]);
    Event::factory()->create([
        'status' => 'draft',
        'is_public' => true,
        'start_date' => now()->addDay(),
    ]);
    Event::factory()->create([
        'status' => 'published',
        'is_public' => false,
        'start_date' => now()->addDay(),
    ]);

    $this->get('/')
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->has('upcomingEvents', 1)
            ->where('upcomingEvents.0.slug', $publicEvent->slug));
});

it('shows published public events grouped by their dates to guests', function () {
    $upcoming = Event::factory()->create([
        'title' => 'Future Workshop',
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHours(2),
    ]);
    $ongoing = Event::factory()->create([
        'title' => 'Live Workshop',
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->subHour(),
        'end_date' => now()->addHour(),
    ]);
    $completed = Event::factory()->create([
        'title' => 'Past Workshop',
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->subDays(2),
        'end_date' => now()->subDay(),
    ]);

    $this->get('/events')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/Events')
            ->where('events.upcoming.0.slug', $upcoming->slug)
            ->where('events.ongoing.0.slug', $ongoing->slug)
            ->where('events.completed.0.slug', $completed->slug));
});

it('moves ended events into completed public and member listings regardless of their stored status', function (string $status, int $endOffset) {
    $this->freezeSecond();

    $ended = Event::factory()->create([
        'status' => $status,
        'is_public' => true,
        'start_date' => now()->subHours(2),
        'end_date' => now()->addSeconds($endOffset),
    ]);
    $ongoing = Event::factory()->create([
        'status' => 'ongoing',
        'is_public' => true,
        'start_date' => now()->subHour(),
        'end_date' => null,
    ]);

    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/Events')
            ->has('events.completed', 1)
            ->where('events.completed.0.slug', $ended->slug)
            ->has('events.ongoing', 1)
            ->where('events.ongoing.0.slug', $ongoing->slug));

    Livewire::actingAs(User::factory()->create())
        ->test(EventListing::class)
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => collect($events->items())
            ->firstWhere('id', $ended->id)['display_status'] === 'completed')
        ->set('filter', 'ongoing')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => collect($events->items())->pluck('id')->all() === [$ongoing->id])
        ->set('filter', 'completed')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => collect($events->items())->pluck('id')->all() === [$ended->id])
        ->set('filter', 'past')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => collect($events->items())->pluck('id')->all() === [$ended->id])
        ->set('filter', 'upcoming')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => $events->isEmpty());
})->with(['published', 'scheduled', 'ongoing'])->with([
    'past end time' => -60,
    'exact end time' => 0,
]);

it('keeps started events without an end time ongoing until the admin marks them completed', function (string $status) {
    $this->freezeSecond();

    $event = Event::factory()->create([
        'status' => $status,
        'is_public' => true,
        'start_date' => now()->subHour(),
        'end_date' => null,
        'registration_deadline' => null,
        'rsvp_deadline' => null,
    ]);

    $this->get(route('events.index'))
        ->assertInertia(fn ($page) => $page
            ->has('events.ongoing', 1)
            ->where('events.ongoing.0.slug', $event->slug)
            ->has('events.completed', 0));

    $this->get(route('events.show', $event))
        ->assertInertia(fn ($page) => $page
            ->where('event.has_ended', false)
            ->where('event.can_register', true)
            ->where('event.can_rsvp', true));

    $listing = Livewire::actingAs(User::factory()->create())
        ->test(EventListing::class)
        ->set('filter', 'ongoing')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => count($events->items()) === 1
            && $events->items()[0]['display_status'] === 'ongoing')
        ->set('filter', 'completed')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => $events->isEmpty());

    $event->update(['status' => 'completed']);

    expect($event->hasEnded())->toBeTrue()
        ->and($event->publicStatus())->toBe('completed')
        ->and($event->acceptsRegistrations())->toBeFalse()
        ->and($event->acceptsRsvps())->toBeFalse();

    $listing->call('$refresh')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => count($events->items()) === 1
            && $events->items()[0]['display_status'] === 'completed')
        ->set('filter', 'ongoing')
        ->assertViewHas('events', fn (LengthAwarePaginator $events): bool => $events->isEmpty());
})->with(['published', 'scheduled', 'ongoing']);

it('never lists draft unpublished private or deleted events publicly', function (array $attributes) {
    $event = Event::factory()->create($attributes);

    if ($attributes['deleted_at'] ?? false) {
        $event->delete();
    }

    $this->get('/events')
        ->assertInertia(fn ($page) => $page
            ->component('public/Events')
            ->missing('events.upcoming.0')
            ->missing('events.ongoing.0')
            ->missing('events.completed.0'));
})->with([
    'draft' => [['status' => 'draft', 'is_public' => true]],
    'unpublished' => [['status' => 'published', 'is_public' => false]],
    'cancelled' => [['status' => 'cancelled', 'is_public' => true]],
    'deleted' => [['status' => 'published', 'is_public' => true, 'deleted_at' => true]],
]);

it('allows guests to view public event details but hides non-public details', function () {
    $publicEvent = Event::factory()->create(['status' => 'completed', 'is_public' => true]);
    $privateEvent = Event::factory()->create(['status' => 'published', 'is_public' => false]);

    $this->get(route('events.show', $publicEvent))
        ->assertInertia(fn ($page) => $page
            ->component('events/Show')
            ->where('event.slug', $publicEvent->slug));

    $this->get(route('events.show', $privateEvent))->assertNotFound();
});

it('shows resources attached to an event on the details page', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    \App\Models\EventResource::factory()->count(2)->create([
        'event_id' => $event->id,
        'type' => 'link',
        'file_path' => null,
        'url' => 'https://example.com/slides',
    ]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn ($page) => $page
            ->component('events/Show')
            ->has('event.resources', 2)
            ->where('event.resources.0.url', 'https://example.com/slides'));
});

it('keeps event creation and admin event management protected from guests', function () {
    $this->get('/events/create')->assertRedirect(route('auth.login'));
    $this->get('/admin/manage-events')->assertRedirect(route('auth.login'));
});

it('keeps protected event actions unavailable to guests', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);

    $this->post(route('events.register', $event))->assertRedirect(route('auth.login'));
    $this->post(route('events.rsvp', $event))->assertRedirect(route('auth.login'));
});

it('does not expose dangerous external event links publicly', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'external_link' => 'javascript:alert(1)',
    ]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn ($page) => $page->where('event.external_link', null));
});

it('limits the public event api to published public fields', function () {
    Event::factory()->create([
        'title' => 'Public API Event',
        'status' => 'ongoing',
        'is_public' => true,
        'virtual_link' => 'https://internal.example.test/meeting',
    ]);
    Event::factory()->create(['status' => 'draft', 'is_public' => true]);

    $this->getJson('/api/events')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Public API Event')
        ->assertJsonMissingPath('data.0.id')
        ->assertJsonMissingPath('data.0.virtual_link')
        ->assertJsonMissingPath('data.0.organizer');
});
