<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use App\Models\EventFeedback;
use App\Models\EventRegistration;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->pastEvent = fn () => Event::factory()->create(['status' => 'completed', 'end_date' => now()->subDay()]);
    $this->createAttendance = fn (Event $event) => EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->user->id,
        'attended_at' => now(),
    ]);
});

it('requires authentication', function () {
    $event = Event::factory()->create();

    post("/events/{$event->slug}/feedback", ['rating' => 5])
        ->assertRedirect(route('auth.login'));
});

it('requires attendance before submitting feedback', function () {
    $event = ($this->pastEvent)();
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", ['rating' => 5])
        ->assertSessionHas('flash.error', 'You must attend the event before submitting feedback.');
});

it('requires event to have ended', function () {
    $event = Event::factory()->create(['end_date' => now()->addDay()]);
    ($this->createAttendance)($event);
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", ['rating' => 5])
        ->assertSessionHas('flash.error', 'Feedback can only be submitted after the event has ended.');
});

it('prevents duplicate feedback', function () {
    $event = ($this->pastEvent)();
    ($this->createAttendance)($event);
    EventFeedback::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->user->id,
    ]);
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", ['rating' => 5])
        ->assertSessionHas('flash.error', 'You have already submitted feedback for this event.');
});

it('requires a rating', function () {
    $event = ($this->pastEvent)();
    ($this->createAttendance)($event);
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", [])
        ->assertSessionHasErrors('rating');
});

it('accepts valid feedback submission', function () {
    $event = ($this->pastEvent)();
    ($this->createAttendance)($event);
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", [
        'rating' => 5,
        'content_quality' => 4,
        'instructor_rating' => 5,
        'pace_rating' => 3,
        'feedback_text' => 'Great event!',
        'suggestions' => 'More practical exercises',
        'is_anonymous' => true,
    ])
        ->assertSessionHas('flash.success', 'Thank you for your feedback!');

    $this->assertDatabaseHas('event_feedback', [
        'event_id' => $event->id,
        'user_id' => $this->user->id,
        'rating' => 5,
        'content_quality' => 4,
        'instructor_rating' => 5,
        'pace_rating' => 3,
        'feedback_text' => 'Great event!',
        'suggestions' => 'More practical exercises',
        'is_anonymous' => true,
    ]);
});

it('stores feedback with minimal data', function () {
    $event = ($this->pastEvent)();
    ($this->createAttendance)($event);
    actingAs($this->user);

    post("/events/{$event->slug}/feedback", [
        'rating' => 3,
    ])
        ->assertSessionHas('flash.success', 'Thank you for your feedback!');

    $this->assertDatabaseHas('event_feedback', [
        'event_id' => $event->id,
        'user_id' => $this->user->id,
        'rating' => 3,
        'feedback_text' => null,
    ]);
});
