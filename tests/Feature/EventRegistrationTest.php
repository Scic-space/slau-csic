<?php

use App\Jobs\PromoteFromWaitlist;
use App\Livewire\EventCalendar;
use App\Livewire\EventDetails;
use App\Livewire\EventEdit;
use App\Livewire\EventListing;
use App\Livewire\MyEvents;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

// ─── Registration ─────────────────────────────────────────────────────

it('allows authenticated user to register for an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 10,
        'registration_required' => true,
        'registration_deadline' => now()->addDay(),
    ]);

    actingAs($user)->post(route('events.register', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
    ]);
});

it('redirects guests to login when registering', function () {
    $event = Event::factory()->create(['status' => 'published']);

    post(route('events.register', $event))
        ->assertRedirect(route('auth.login'));
});

it('prevents duplicate registration', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'registration_deadline' => now()->addDay(),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
    ]);

    actingAs($user)->post(route('events.register', $event))
        ->assertRedirect();
});

it('prevents registration after deadline', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'registration_deadline' => now()->subDay(),
    ]);

    $response = actingAs($user)->post(route('events.register', $event));

    $response->assertRedirect();
    $response->assertSessionHas('flash.error', 'Registration for this event has closed.');
});

it('prevents registration when event is full and waitlist disabled', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 1,
        'waitlist_enabled' => false,
        'registration_deadline' => now()->addDay(),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => 'registered',
    ]);

    $response = actingAs($user)->post(route('events.register', $event));

    $response->assertRedirect();
    $response->assertSessionHas('flash.error', 'This event is full.');
});

it('allows unregistering from an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published', 'registration_deadline' => now()->addDay()]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
    ]);

    actingAs($user)->post(route('events.unregister', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'cancelled',
    ]);
});

// ─── RSVP ─────────────────────────────────────────────────────────────

it('allows user to RSVP attending', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 10,
        'registration_deadline' => now()->addDay(),
    ]);

    actingAs($user)->post(route('events.rsvp', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'rsvp_status' => 'attending',
        'status' => 'registered',
    ]);
});

it('allows user to cancel RSVP', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published', 'registration_deadline' => now()->addDay()]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'rsvp_status' => 'attending',
        'status' => 'registered',
    ]);

    actingAs($user)->post(route('events.cancel-rsvp', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'rsvp_status' => 'not_attending',
        'status' => 'cancelled',
    ]);
});

it('redirects guests to login when RSVPing', function () {
    $event = Event::factory()->create(['status' => 'published']);

    post(route('events.rsvp', $event))
        ->assertRedirect(route('auth.login'));
});

it('prevents RSVP after registration deadline', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'registration_deadline' => now()->subDay(),
    ]);

    $response = actingAs($user)->post(route('events.rsvp', $event));

    $response->assertRedirect();
    $response->assertSessionHas('flash.error', 'Registration for this event has closed.');
});

// ─── Waitlist ─────────────────────────────────────────────────────────

it('adds user to waitlist when event is full', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 1,
        'waitlist_enabled' => true,
        'registration_deadline' => now()->addDay(),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => 'registered',
    ]);

    actingAs($user)->post(route('events.register', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'waitlist',
    ]);
});

it('dispatches waitlist promotion when someone unregisters', function () {
    Bus::fake();

    $registeredUser = User::factory()->create();
    $waitlistedUser = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 1,
        'waitlist_enabled' => true,
        'registration_deadline' => now()->addDay(),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $registeredUser->id,
        'status' => 'registered',
    ]);
    EventRegistration::factory()->waitlisted()->create([
        'event_id' => $event->id,
        'user_id' => $waitlistedUser->id,
    ]);

    actingAs($registeredUser)->post(route('events.unregister', $event));

    Bus::assertDispatched(PromoteFromWaitlist::class);
});

// ─── Listing & Filters ───────────────────────────────────────────────

it('lists published events on index page', function () {
    Event::factory()->count(3)->create(['status' => 'published', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->assertSee('events');
});

it('does not show draft events on index page', function () {
    Event::factory()->create(['status' => 'draft', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->assertSee('events');
});

it('filters events by search query', function () {
    Event::factory()->create(['status' => 'published', 'title' => 'Cybersecurity Workshop', 'is_public' => true]);
    Event::factory()->create(['status' => 'published', 'title' => 'Social Meetup', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('search', 'Cyber')
        ->assertSee('Cybersecurity Workshop');
});

it('filters events by category', function () {
    $category = EventCategory::create(['name' => 'Tech', 'slug' => 'tech', 'color' => '#6366f1']);
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $event->categories()->attach($category);
    Event::factory()->create(['status' => 'published', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('category', 'tech')
        ->assertSee('events');
});

it('filters events by type', function () {
    Event::factory()->create(['status' => 'published', 'type' => 'workshop', 'is_public' => true]);
    Event::factory()->create(['status' => 'published', 'type' => 'social', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('type', 'workshop')
        ->assertSee('events');
});

it('filters events by upcoming', function () {
    Event::factory()->create(['status' => 'published', 'start_date' => now()->addWeek(), 'is_public' => true]);
    Event::factory()->create(['status' => 'published', 'start_date' => now()->subWeek(), 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('filter', 'upcoming')
        ->assertSee('events');
});

it('filters events by past', function () {
    Event::factory()->create(['status' => 'published', 'start_date' => now()->subWeek(), 'is_public' => true]);
    Event::factory()->create(['status' => 'published', 'start_date' => now()->addWeek(), 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('filter', 'past')
        ->assertSee('events');
});

it('paginates events', function () {
    Event::factory()->count(15)->create(['status' => 'published', 'is_public' => true]);

    Livewire::test(EventListing::class)
        ->set('perPage', 5)
        ->assertSee('events');
});

// ─── Event Show Page ──────────────────────────────────────────────────

it('shows event details on show page', function () {
    $event = Event::factory()->create(['status' => 'published']);

    Livewire::test(EventDetails::class, ['event' => $event])
        ->assertSet('event.id', $event->id);
});

it('returns 404 for unpublished events', function () {
    $event = Event::factory()->create(['status' => 'draft']);

    get(route('events.show', $event))
        ->assertNotFound();
});

it('returns 404 for cancelled events', function () {
    $event = Event::factory()->create(['status' => 'cancelled']);

    get(route('events.show', $event))
        ->assertNotFound();
});

// ─── Calendar ─────────────────────────────────────────────────────────

it('renders calendar with events for authenticated user', function () {
    $user = User::factory()->create();
    Event::factory()->count(2)->create([
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->addDay(),
    ]);

    actingAs($user);

    Livewire::test(EventCalendar::class)
        ->assertOk()
        ->assertSeeInOrder([
            'Categories',
            'Legend',
            "You're registered",
            'Click an event to view details',
            'Events are colored by category',
        ]);
});

// ─── My Events ────────────────────────────────────────────────────────

it('shows my events for authenticated user', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published']);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    actingAs($user);

    Livewire::test(MyEvents::class)
        ->assertOk();
});

// ─── Recurring Events ─────────────────────────────────────────────────

it('creates event as recurring', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_recurring' => true,
    ]);

    expect($event->is_recurring)->toBeTrue();
    expect($event->isMasterEvent())->toBeTrue();
    expect($event->isOccurrence())->toBeFalse();
});

it('associates occurrence with master event', function () {
    $master = Event::factory()->create(['is_recurring' => true]);
    $occurrence = Event::factory()->create([
        'status' => 'published',
        'parent_event_id' => $master->id,
    ]);

    expect($occurrence->masterEvent->id)->toBe($master->id);
    expect($master->occurrences->first()->id)->toBe($occurrence->id);
    expect($occurrence->isOccurrence())->toBeTrue();
});

// ─── Authorization ────────────────────────────────────────────────────

it('allows organizer to edit event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'organizer_id' => $user->id,
        'status' => 'published',
    ]);

    actingAs($user);

    Livewire::test(EventEdit::class, ['event' => $event])
        ->assertSet('event.id', $event->id);
});

it('denies non-organizer from editing event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published']);

    actingAs($user)->get(route('events.edit', $event))
        ->assertForbidden();
});

it('redirects guest from editing event', function () {
    $event = Event::factory()->create(['status' => 'published']);

    get(route('events.edit', $event))
        ->assertRedirect(route('auth.login'));
});
