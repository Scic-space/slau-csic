<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
