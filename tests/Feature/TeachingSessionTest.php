<?php

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Training;
use App\Models\User;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('shows a teaching session detail page', function () {
    $meeting = Meeting::factory()->teachingSession()->create([
        'title' => 'Network Security Fundamentals',
        'scheduled_at' => now()->subDay(),
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Network Security Fundamentals')
        ->assertSee('Teaching Session');
});

it('shows attendance status when user attended', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'status' => 'present',
        'checked_in_at' => now(),
        'check_in_method' => 'qr_code',
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('You attended this session');
});

it('shows no attendance when user did not attend', function () {
    $meeting = Meeting::factory()->teachingSession()->create();

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Attendance not recorded');
});

it('shows linked training when meeting has one', function () {
    $training = Training::factory()->published()->create(['title' => 'Ethical Hacking']);
    $meeting = Meeting::factory()->teachingSession()->create([
        'training_id' => $training->id,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Linked Training')
        ->assertSee('Ethical Hacking');
});

it('returns 404 for non-teaching session meetings', function () {
    $meeting = Meeting::factory()->create(['type' => 'general']);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertNotFound();
});

it('shows agenda items when present', function () {
    $meeting = Meeting::factory()->teachingSession()->create();
    $meeting->agendaItems()->create([
        'title' => 'Introduction to Kali Linux',
        'duration_minutes' => 30,
        'sort_order' => 1,
    ]);

    actingAs($this->user)
        ->get(route('classes.show', $meeting->id))
        ->assertOk()
        ->assertSee('Agenda')
        ->assertSee('Introduction to Kali Linux');
});
