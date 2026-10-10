<?php

use App\Events\EventAttended;
use App\Models\Badge;
use App\Models\BadgeCriteriaType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Services\EventService;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
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

// ─── Attendance via the service ──────────────────────────────────────

it('marks a member attended and dispatches EventAttended through the service', function () {
    EventFacade::fake([EventAttended::class]);

    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $event = Event::factory()->create(['status' => 'published']);
    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
    ]);

    app(EventService::class)->markAttendance($event, $member->id, 'present');

    $registration->refresh();

    expect($registration->status)->toBe('attended')
        ->and($registration->attended_at)->not->toBeNull();

    $this->assertDatabaseHas('event_attendance', [
        'event_id' => $event->id,
        'member_id' => $member->id,
        'status' => 'present',
    ]);

    EventFacade::assertDispatched(EventAttended::class);
});

it('awards attendance points only once when marked present repeatedly', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $event = Event::factory()->create(['status' => 'published']);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
    ]);

    $service = app(EventService::class);
    $service->markAttendance($event, $member->id, 'present');
    $service->markAttendance($event, $member->id, 'present');

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $member->id,
        'points' => 50,
        'reference_type' => Event::class,
        'reference_id' => $event->id,
    ]);

    expect($member->pointTransactions()->where('reference_id', $event->id)->where('points', 50)->count())->toBe(1);
});

it('unlocks an events-attended badge from real attendance records', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'approved_at' => now()]);
    $badge = Badge::factory()->create([
        'criteria_type' => BadgeCriteriaType::EventsAttended,
        'criteria_value' => 1,
        'points_bonus' => 0,
    ]);

    $event = Event::factory()->create(['status' => 'published']);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'registered',
        'attended_at' => now(),
    ]);

    app(GamificationService::class)->checkBadges($member);

    $this->assertDatabaseHas('user_badges', [
        'user_id' => $member->id,
        'badge_id' => $badge->id,
    ]);
});
