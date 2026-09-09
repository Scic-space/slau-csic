<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

function checkInUser(array $overrides = []): User
{
    $permission = Permission::findOrCreate('manage_attendance', 'web');
    $user = User::factory()->create(array_merge([
        'membership_status' => 'active',
        'approved_at' => now(),
    ], $overrides));
    $user->givePermissionTo($permission);

    return $user;
}

// ─── Code generation ─────────────────────────────────────────────────

it('generates a 16 character uppercase check-in code on registration', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published']);

    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
    ]);

    expect($registration->check_in_code)->not->toBeNull()
        ->and(strlen($registration->check_in_code))->toBe(16)
        ->and(mb_strtoupper($registration->check_in_code))->toBe($registration->check_in_code);
});

// ─── Manual check-in ─────────────────────────────────────────────────

it('checks in a participant with a valid code', function () {
    $checker = checkInUser();
    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
    ]);

    $response = actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => $registration->check_in_code,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect($registration->fresh()->hasAttended())->toBeTrue();

    $this->assertDatabaseHas('event_attendance', [
        'event_id' => $event->id,
        'member_id' => $member->id,
        'status' => 'present',
    ]);
});

it('rejects an unknown check-in code', function () {
    $checker = checkInUser();

    actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => 'AAAA0000BBBB1111',
    ])
        ->assertStatus(404)
        ->assertJsonPath('success', false);
});

it('accepts legacy 12 character check-in codes', function () {
    $checker = checkInUser();
    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
        'check_in_code' => 'ABC123XYZ789',
    ]);

    actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => 'ABC123XYZ789',
    ])->assertOk();
});

it('rejects codes that are too short or too long', function (string $code) {
    $checker = checkInUser();

    actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => $code,
    ])->assertStatus(422);
})->with([
    'too short' => 'ABC123',
    'too long' => 'ABCDEFGHIJKLMNOPQRSTUVWX',
]);

it('tells the checker when a participant has already checked in', function () {
    $checker = checkInUser();
    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'attended',
        'attended_at' => now(),
    ]);

    actingAs($checker)->postJson(route('events.checkin.process'), [
        'code' => $registration->check_in_code,
    ])
        ->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('registration.name', $member->name);
});

it('denies check-in without the manage_attendance permission', function () {
    $user = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);

    actingAs($user)->get(route('events.checkin'))
        ->assertStatus(403);
});
