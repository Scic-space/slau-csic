<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function attendedEvent(): array
{
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'completed',
        'type' => 'workshop',
        'start_date' => now()->subDays(2),
        'end_date' => now()->subDay(),
    ]);
    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
        'attended_at' => now()->subDay(),
    ]);

    return [$user, $event, $registration];
}

it('downloads a PDF certificate for an attended, ended event', function () {
    [$user, $event, $registration] = attendedEvent();

    $response = $this->actingAs($user)
        ->get(route('events.certificate', [$event, $registration]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->streamedContent())->toStartWith('%PDF-');
});

it('forbids downloading another member certificate', function () {
    [, $event, $registration] = attendedEvent();

    $this->actingAs(User::factory()->create())
        ->get(route('events.certificate', [$event, $registration]))
        ->assertForbidden();
});

it('returns gone when attendance is not confirmed', function () {
    [$user, $event, $registration] = attendedEvent();
    $registration->update(['attended_at' => null]);

    $this->actingAs($user)
        ->get(route('events.certificate', [$event, $registration]))
        ->assertStatus(410);
});

it('returns gone when the event has not ended yet', function () {
    [$user, $event, $registration] = attendedEvent();
    $event->update(['end_date' => now()->addDay()]);

    $this->actingAs($user)
        ->get(route('events.certificate', [$event, $registration]))
        ->assertStatus(410);
});

it('returns not found when the registration belongs to another event', function () {
    [$user, $event] = attendedEvent();
    $otherRegistration = EventRegistration::factory()->create([
        'event_id' => Event::factory()->create(['end_date' => now()->subDay()])->id,
        'user_id' => $user->id,
        'attended_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('events.certificate', [$event, $otherRegistration]))
        ->assertNotFound();
});
