<?php

use App\Jobs\PromoteFromWaitlist;
use App\Livewire\EventDetails;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\PromotedFromWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->freezeSecond();
    Bus::fake();
});

dataset('ended event states', [
    'past end date with stale published status' => ['published', -1],
    'completed before scheduled end date' => ['completed', 1],
]);

function endedLifecycleEvent(string $status, int $endOffset): Event
{
    return Event::factory()->create([
        'status' => $status,
        'is_public' => true,
        'start_date' => now()->subHours(2),
        'end_date' => now()->addHours($endOffset),
        'registration_deadline' => now()->addDay(),
        'rsvp_deadline' => now()->addDay(),
    ]);
}

it('closes public registration and RSVP at the end of an event', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $this->actingAs(User::factory()->create());

    foreach (['events.register', 'events.rsvp'] as $route) {
        $this->post(route($route, $event))->assertRedirect()->assertSessionHas('flash.error');
    }

    expect($event->registrations()->count())->toBe(0);
})->with('ended event states');

it('preserves registrations when cancellation is submitted after the event ends', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);

    foreach (['events.unregister', 'events.cancel-rsvp'] as $route) {
        $this->post(route($route, $event))->assertRedirect()->assertSessionHas('flash.error');

        expect($registration->fresh()->status)->toBe('registered')
            ->and($registration->fresh()->rsvp_status)->toBe('attending');
    }
})->with('ended event states');

it('blocks member registration and RSVP actions after the event ends', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $this->actingAs(User::factory()->create());
    $component = Livewire::test(EventDetails::class, ['event' => $event]);

    foreach (['register', 'rsvp', 'rsvpMaybe'] as $action) {
        $component->call($action)->assertDispatched('toast-show', type: 'error');
    }

    expect($event->registrations()->count())->toBe(0);
})->with('ended event states');

it('blocks member cancellation actions after the event ends', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);
    $component = Livewire::test(EventDetails::class, ['event' => $event]);

    foreach (['unregister', 'cancelRsvp', 'rsvpMaybe'] as $action) {
        $component->call($action)->assertDispatched('toast-show', type: 'error');

        expect($registration->fresh()->status)->toBe('registered')
            ->and($registration->fresh()->rsvp_status)->toBe('attending');
    }
})->with('ended event states');

it('replaces member registration controls with disabled completed and hides RSVP and QR', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);

    $component = Livewire::test(EventDetails::class, ['event' => $event])
        ->assertSee('Completed')
        ->assertDontSee('Register Now')
        ->assertDontSee('Your Check-In Code')
        ->assertDontSee($registration->check_in_code)
        ->assertDontSeeHtml('wire:click="rsvp"')
        ->assertDontSeeHtml('wire:click="rsvpMaybe"')
        ->assertDontSeeHtml('wire:click="unregister"');

    $document = new DOMDocument;
    @$document->loadHTML($component->html());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//button[@disabled and normalize-space(.)="Completed"]')->length)->toBe(1)
        ->and($xpath->query('//h3[normalize-space(.)="RSVP"]')->length)->toBe(0);
})->with('ended event states');

it('exposes completed registration state to public event details', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);

    $this->get(route('events.show', $event))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('events/Show')
            ->where('event.has_ended', true)
            ->where('event.can_register', false)
            ->where('event.can_rsvp', false));
})->with('ended event states');

it('rejects check-in codes after the event ends', function (string $status, int $endOffset) {
    $event = endedLifecycleEvent($status, $endOffset);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $checker = User::factory()->create();
    $checker->givePermissionTo(Permission::findOrCreate('manage_attendance', 'web'));

    $this->actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => $registration->check_in_code,
    ])->assertForbidden()->assertJsonPath('success', false);

    expect($registration->fresh()->status)->toBe('registered')
        ->and($registration->fresh()->attended_at)->toBeNull();
    $this->assertDatabaseMissing('event_attendance', ['event_id' => $event->id]);
})->with('ended event states');

it('treats the exact end time as completed and keeps open ended events active', function () {
    $event = Event::factory()->make([
        'status' => 'ongoing',
        'start_date' => now()->subHour(),
        'end_date' => now(),
    ]);

    expect($event->hasEnded())->toBeTrue();

    $event->end_date = now()->addSecond();
    expect($event->hasEnded())->toBeFalse();

    $event->end_date = null;
    expect($event->hasEnded())->toBeFalse();
});

it('keeps past cancelled events cancelled while rejecting registration and RSVP', function () {
    $event = endedLifecycleEvent('cancelled', -1);

    expect($event->hasEnded())->toBeFalse()
        ->and($event->acceptsRegistrations())->toBeFalse()
        ->and($event->acceptsRsvps())->toBeFalse();

    $this->actingAs(User::factory()->create());

    foreach (['events.register', 'events.rsvp'] as $route) {
        $this->post(route($route, $event))->assertRedirect()->assertSessionHas('flash.error');
    }

    expect($event->registrations()->count())->toBe(0)
        ->and($event->fresh()->status)->toBe('cancelled');
});

it('does not promote waitlisted members after the event ends', function (string $status, int $endOffset) {
    Notification::fake();
    $event = endedLifecycleEvent($status, $endOffset);
    $registration = EventRegistration::factory()->waitlisted()->create(['event_id' => $event->id]);

    (new PromoteFromWaitlist($event))->handle();

    expect($registration->fresh()->status)->toBe('waitlist')
        ->and($event->fresh()->registered_count)->toBe(0);
    Notification::assertNothingSent();
})->with('ended event states');

it('promotes waitlisted members only into remaining spots', function (int $capacity, bool $shouldPromote) {
    Notification::fake();
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => $capacity,
        'waitlist_enabled' => true,
    ]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'status' => 'attended']);
    $registration = EventRegistration::factory()->waitlisted()->create(['event_id' => $event->id]);

    (new PromoteFromWaitlist($event))->handle();

    expect($registration->fresh()->status)->toBe($shouldPromote ? 'registered' : 'waitlist')
        ->and($event->fresh()->registered_count)->toBe($capacity)
        ->and($event->fresh()->remaining_spots)->toBe(0);

    if ($shouldPromote) {
        expect($registration->fresh()->waitlisted_at)->toBeNull();
        Notification::assertSentTo($registration->user, PromotedFromWaitlist::class);
    } else {
        Notification::assertNothingSent();
    }
})->with([
    'one available spot' => [2, true],
    'full with an attended member' => [1, false],
]);

it('removes RSVP and QR when an open member page refreshes after the end time', function () {
    $event = Event::factory()->create([
        'status' => 'ongoing',
        'start_date' => now()->subHour(),
        'end_date' => now()->addMinute(),
    ]);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);

    $component = Livewire::test(EventDetails::class, ['event' => $event])
        ->assertSee('Your Check-In Code')
        ->assertSee($registration->check_in_code)
        ->assertSee('RSVP');

    $this->travelTo($event->end_date);

    $component->call('$refresh')
        ->assertSee('Completed')
        ->assertDontSee('Your Check-In Code')
        ->assertDontSee($registration->check_in_code)
        ->assertDontSeeHtml('wire:click="rsvpMaybe"');
});

it('updates an open member page when an administrator completes the event early', function () {
    $event = Event::factory()->create([
        'status' => 'ongoing',
        'start_date' => now()->subHour(),
        'end_date' => now()->addHour(),
    ]);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);

    $component = Livewire::test(EventDetails::class, ['event' => $event])
        ->assertSee('Your Check-In Code')
        ->assertSee($registration->check_in_code)
        ->assertSee('RSVP');

    $event->update(['status' => 'completed']);

    $component->call('$refresh')
        ->assertSee('Completed')
        ->assertDontSee('Register Now')
        ->assertDontSee('Your Check-In Code')
        ->assertDontSee($registration->check_in_code)
        ->assertDontSeeHtml('wire:click="rsvp"')
        ->assertDontSeeHtml('wire:click="rsvpMaybe"')
        ->assertDontSeeHtml('wire:click="unregister"');

    $document = new DOMDocument;
    @$document->loadHTML($component->html());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//button[@disabled and normalize-space(.)="Completed"]')->length)->toBe(1)
        ->and($xpath->query('//h3[normalize-space(.)="RSVP"]')->length)->toBe(0);
});

it('refreshes remaining spots on an open member page as other members register', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 3,
        'waitlist_enabled' => false,
    ]);
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    $component = Livewire::test(EventDetails::class, ['event' => $event])
        ->assertSee('3 spots remaining');

    foreach ([2, 1, 0] as $remaining) {
        $this->actingAs(User::factory()->create())
            ->post(route('events.register', $event))
            ->assertRedirect()
            ->assertSessionMissing('flash.error');
        $this->actingAs($viewer);

        $component->call('$refresh')
            ->assertSee($remaining.' spot'.($remaining === 1 ? '' : 's').' remaining');

        expect($event->fresh()->remaining_spots)->toBe($remaining);
    }

    $component->assertDontSee('Register Now');
    expect($event->registrations()->count())->toBe(3);
});

it('counts attended and no show registrations as occupied spots in public and member details', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'max_participants' => 5,
    ]);

    foreach (['registered', 'attended', 'no_show', 'cancelled', 'waitlist'] as $status) {
        EventRegistration::factory()->create(['event_id' => $event->id, 'status' => $status]);
    }

    expect($event->registered_count)->toBe(3)
        ->and($event->remaining_spots)->toBe(2);

    $this->get(route('events.show', $event))
        ->assertInertia(fn ($page) => $page
            ->where('event.registered_count', 3)
            ->where('event.remaining_spots', 2)
            ->where('event.is_full', false));

    Livewire::test(EventDetails::class, ['event' => $event])->assertSee('2 spots remaining');
    $event->update(['max_participants' => 2]);

    expect($event->remaining_spots)->toBe(0)
        ->and($event->is_full)->toBeTrue();
});

it('updates remaining spots for registration cancellation and registration again', function (string $interface) {
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 2,
        'waitlist_enabled' => false,
    ]);
    $this->actingAs(User::factory()->create());
    $component = $interface === 'member' ? Livewire::test(EventDetails::class, ['event' => $event]) : null;

    foreach (['register' => 1, 'unregister' => 2] as $action => $remaining) {
        if ($component) {
            $component->call($action)->assertSee($remaining.' spot'.($remaining === 1 ? '' : 's').' remaining');
        } else {
            $this->post(route('events.'.$action, $event))->assertRedirect();
        }

        expect($event->fresh()->remaining_spots)->toBe($remaining);
    }

    if ($component) {
        $component->call('register');
    } else {
        $this->post(route('events.register', $event))->assertRedirect();
    }

    expect($event->fresh()->remaining_spots)->toBe(1)
        ->and($event->registrations()->count())->toBe(1);
})->with(['public', 'member']);

it('keeps a confirmed RSVP confirmed when submitted again at capacity', function (string $interface) {
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 1,
        'waitlist_enabled' => true,
    ]);
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $this->actingAs($registration->user);

    if ($interface === 'member') {
        Livewire::test(EventDetails::class, ['event' => $event])->call('rsvp');
    } else {
        $this->post(route('events.rsvp', $event))->assertRedirect();
    }

    expect($registration->fresh()->status)->toBe('registered')
        ->and($event->fresh()->remaining_spots)->toBe(0)
        ->and($event->registrations()->count())->toBe(1);
})->with(['public', 'member']);

it('does not allow RSVP to exceed capacity when the waitlist is disabled', function (string $interface) {
    $event = Event::factory()->create([
        'status' => 'published',
        'max_participants' => 1,
        'waitlist_enabled' => false,
    ]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'status' => 'attended']);
    $this->actingAs(User::factory()->create());

    if ($interface === 'member') {
        Livewire::test(EventDetails::class, ['event' => $event])
            ->call('rsvp')
            ->assertDispatched('toast-show', type: 'error');
    } else {
        $this->post(route('events.rsvp', $event))->assertRedirect()->assertSessionHas('flash.error');
    }

    expect($event->registrations()->count())->toBe(1)
        ->and($event->fresh()->remaining_spots)->toBe(0);
})->with(['public', 'member']);

it('does not reserve a spot for a new tentative RSVP', function () {
    $event = Event::factory()->create(['status' => 'published', 'max_participants' => 2]);
    $this->actingAs(User::factory()->create());

    Livewire::test(EventDetails::class, ['event' => $event])
        ->call('rsvpMaybe')
        ->assertSee('2 spots remaining')
        ->call('rsvp')
        ->assertSee('1 spot remaining');

    expect($event->fresh()->remaining_spots)->toBe(1)
        ->and($event->registrations()->count())->toBe(1)
        ->and($event->registrations()->first()->rsvp_status)->toBe('attending');
});
