<?php

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function approvedEventCategory(): EventCategory
{
    return EventCategory::create([
        'name' => 'Workshops',
        'color' => '#4f46e5',
        'icon' => 'workshops',
        'is_active' => true,
        'sort_order' => 1,
    ]);
}

it('renders every event page for an approved member without redirecting home', function () {
    $member = User::factory()->create();
    $category = approvedEventCategory();
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(3)->addHours(2),
        'registration_deadline' => now()->addDay(),
    ]);
    $event->categories()->attach($category);

    EventRegistration::create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
        'rsvp_status' => 'attending',
        'registered_at' => now(),
    ]);

    $this->actingAs($member)
        ->get(route('events.browse'))
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\EventListing::class);

    $this->get(route('events.member-show', $event))
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\EventDetails::class);

    $this->get(route('my-events'))
        ->assertOk();

    $this->get(route('events.calendar'))
        ->assertOk();
});

it('does not redirect an approved member to the dashboard for events', function (string $routeName) {
    $category = approvedEventCategory();
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(4),
        'registration_deadline' => now()->addDay(),
    ]);
    $event->categories()->attach($category);

    $this->actingAs(User::factory()->create())
        ->get(route($routeName, ['event' => $event]))
        ->assertOk();
})->with([
    'events browse' => 'events.browse',
    'events calendar' => 'events.calendar',
    'my events' => 'my-events',
    'events member-show' => 'events.member-show',
]);

it('keeps public event pages public and only redirects approved members to their member equivalents', function () {
    $category = approvedEventCategory();
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(4),
        'registration_deadline' => now()->addDay(),
    ]);
    $event->categories()->attach($category);

    $this->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page->component('public/Events'));

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Show')
            ->where('event.slug', $event->slug));

    $this->actingAs(User::factory()->create())
        ->get(route('events.index'))
        ->assertRedirect(route('events.browse'));

    $this->get(route('events.show', $event))
        ->assertRedirect(route('events.member-show', $event));
});

it('processes the rsvp and register flows for an approved member', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
        'registration_deadline' => now()->addDay(),
    ]);

    $this->actingAs($member)
        ->post(route('events.register', $event))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
    ]);

    $this->post(route('events.rsvp', $event))
        ->assertSessionHasNoErrors();

    $this->post(route('events.cancel-rsvp', $event))
        ->assertSessionHasNoErrors();

    $this->post(route('events.unregister', $event))
        ->assertSessionHasNoErrors();
});

it('asks an approved but unverified member to verify their email before registering', function () {
    $member = User::factory()->unverified()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
        'registration_deadline' => now()->addDay(),
    ]);

    $this->actingAs($member)
        ->post(route('events.register', $event))
        ->assertRedirect(route('verification.notice'));
});
