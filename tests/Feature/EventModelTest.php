<?php

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventFeedback;
use App\Models\EventPrerequisite;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Model Creation ───────────────────────────────────────────────────

it('creates an event with valid data', function () {
    $event = Event::factory()->create([
        'title' => 'Cybersecurity Workshop',
        'status' => 'draft',
    ]);

    expect($event->title)->toBe('Cybersecurity Workshop');
    expect($event->status)->toBe('draft');
    expect($event->slug)->not->toBeNull();
    expect($event->created_at)->not->toBeNull();
    expect($event->updated_at)->not->toBeNull();
});

it('generates slug from title on creation', function () {
    $event = Event::factory()->create([
        'title' => 'Advanced SQL Injection Lab',
        'slug' => null,
    ]);

    expect($event->slug)->toContain('advanced-sql-injection');
});

it('defaults status to draft when not specified', function () {
    $event = Event::factory()->create(['status' => 'draft']);

    expect($event->status)->toBe('draft');
});

// ─── Relationships ────────────────────────────────────────────────────

it('belongs to organizer', function () {
    $organizer = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    expect($event->organizer)->toBeInstanceOf(User::class);
    expect($event->organizer->id)->toBe($organizer->id);
});

it('has many registrations', function () {
    $event = Event::factory()->create();
    $registrations = EventRegistration::factory()->count(3)->create(['event_id' => $event->id]);

    expect($event->registrations)->toHaveCount(3);
    expect($event->registrations->first())->toBeInstanceOf(EventRegistration::class);
});

it('has many attendance records', function () {
    $event = Event::factory()->create();
    EventAttendance::factory()->count(2)->create(['event_id' => $event->id]);

    expect($event->attendanceRecords)->toHaveCount(2);
    expect($event->attendanceRecords->first())->toBeInstanceOf(EventAttendance::class);
});

it('has many feedback', function () {
    $event = Event::factory()->create();
    EventFeedback::factory()->count(2)->create(['event_id' => $event->id]);

    expect($event->feedback)->toHaveCount(2);
});

it('has many prerequisites', function () {
    $event = Event::factory()->create();
    $prereq = Event::factory()->create();
    EventPrerequisite::create([
        'event_id' => $event->id,
        'prerequisite_event_id' => $prereq->id,
    ]);

    expect($event->prerequisites)->toHaveCount(1);
});

it('has many categories', function () {
    $event = Event::factory()->create();
    $category = \App\Models\EventCategory::create([
        'name' => 'Cyber',
        'slug' => 'cyber',
        'color' => '#6366f1',
    ]);
    $event->categories()->attach($category);

    expect($event->categories)->toHaveCount(1);
});

// ─── Scopes ───────────────────────────────────────────────────────────

it('published scope returns only published events', function () {
    Event::factory()->create(['status' => 'published']);
    Event::factory()->create(['status' => 'draft']);
    Event::factory()->create(['status' => 'cancelled']);

    $publishedEvents = Event::published()->get();

    expect($publishedEvents)->toHaveCount(1);
    expect($publishedEvents->first()->status)->toBe('published');
});

// ─── Computed Attributes ──────────────────────────────────────────────

it('calculates registered count', function () {
    $event = Event::factory()->create();
    EventRegistration::factory()->count(3)->create(['event_id' => $event->id, 'status' => 'registered']);
    EventRegistration::factory()->count(2)->create(['event_id' => $event->id, 'status' => 'cancelled']);

    expect($event->registered_count)->toBe(3);
});

it('calculates waitlisted count', function () {
    $event = Event::factory()->create();
    EventRegistration::factory()->count(2)->create(['event_id' => $event->id, 'status' => 'waitlist']);

    expect($event->waitlisted_count)->toBe(2);
});

it('detects is_full correctly', function () {
    $event = Event::factory()->create(['max_participants' => 2]);
    EventRegistration::factory()->count(2)->create(['event_id' => $event->id, 'status' => 'registered']);

    expect($event->is_full)->toBeTrue();
});

it('detects not full when under capacity', function () {
    $event = Event::factory()->create(['max_participants' => 10]);
    EventRegistration::factory()->count(3)->create(['event_id' => $event->id, 'status' => 'registered']);

    expect($event->is_full)->toBeFalse();
});

it('calculates remaining spots', function () {
    $event = Event::factory()->create(['max_participants' => 10]);
    EventRegistration::factory()->count(4)->create(['event_id' => $event->id, 'status' => 'registered']);

    expect($event->remaining_spots)->toBe(6);
});

it('returns 999 remaining spots when max_participants is null', function () {
    $event = Event::factory()->create(['max_participants' => null]);

    expect($event->remaining_spots)->toBe(999);
});

it('has attendance rate of 0 with zero registrations', function () {
    $event = Event::factory()->create();

    expect($event->attendance_rate)->toBe(0.0);
});

it('calculates attendance rate correctly', function () {
    $event = Event::factory()->create();
    EventRegistration::factory()->count(4)->create(['event_id' => $event->id, 'status' => 'registered']);
    EventAttendance::factory()->count(3)->create(['event_id' => $event->id, 'status' => 'present']);
    EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'absent']);

    expect($event->attendance_rate)->toBe(75.0);
});

// ─── Business Logic Methods ───────────────────────────────────────────

it('canMemberRegister returns true when all conditions met', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addDay(),
        'registration_deadline' => now()->addDay(),
        'max_participants' => 10,
        'waitlist_enabled' => true,
    ]);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeTrue();
});

it('canMemberRegister rejects cancelled events', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create(['status' => 'cancelled']);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeFalse();
    expect($result['errors'][0])->toContain('cancelled');
});

it('canMemberRegister rejects past events', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->subDay(),
    ]);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeFalse();
    expect($result['errors'][0])->toContain('started');
});

it('canMemberRegister rejects past deadline', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addDay(),
        'registration_deadline' => now()->subDay(),
    ]);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeFalse();
    expect($result['errors'][0])->toContain('deadline');
});

it('canMemberRegister rejects full event without waitlist', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addDay(),
        'registration_deadline' => now()->addDay(),
        'max_participants' => 1,
        'waitlist_enabled' => false,
    ]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'status' => 'registered']);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeFalse();
    expect($result['errors'][0])->toContain('full');
});

it('canMemberRegister checks prerequisite events', function () {
    $member = User::factory()->create();
    $prereqEvent = Event::factory()->create(['title' => 'Prerequisite Lab']);
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addDay(),
        'registration_deadline' => now()->addDay(),
    ]);
    EventPrerequisite::create([
        'event_id' => $event->id,
        'prerequisite_event_id' => $prereqEvent->id,
    ]);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeFalse();
    expect($result['errors'][0])->toContain('Prerequisite');
});

it('canMemberRegister passes when prerequisite completed', function () {
    $member = User::factory()->create();
    $prereqEvent = Event::factory()->create(['title' => 'Prerequisite Lab']);
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addDay(),
        'registration_deadline' => now()->addDay(),
    ]);
    EventPrerequisite::create([
        'event_id' => $event->id,
        'prerequisite_event_id' => $prereqEvent->id,
    ]);
    EventAttendance::factory()->create([
        'event_id' => $prereqEvent->id,
        'member_id' => $member->id,
        'status' => 'present',
    ]);

    $result = $event->canMemberRegister($member);

    expect($result['can_register'])->toBeTrue();
});

it('hasMemberAttended returns true when member attended', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create();
    EventAttendance::factory()->create([
        'event_id' => $event->id,
        'member_id' => $member->id,
        'status' => 'present',
    ]);

    expect($event->hasMemberAttended($member))->toBeTrue();
});

it('hasMemberAttended returns false when member did not attend', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create();

    expect($event->hasMemberAttended($member))->toBeFalse();
});

it('hasMemberAttended returns true for excused status', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create();
    EventAttendance::factory()->create([
        'event_id' => $event->id,
        'member_id' => $member->id,
        'status' => 'excused',
    ]);

    expect($event->hasMemberAttended($member))->toBeTrue();
});

// ─── Recurring Events ─────────────────────────────────────────────────

it('isRecurring returns true when marked as recurring', function () {
    $event = Event::factory()->create(['is_recurring' => true]);

    expect($event->isRecurring())->toBeTrue();
});

it('isMasterEvent when recurring and no parent', function () {
    $event = Event::factory()->create(['is_recurring' => true, 'parent_event_id' => null]);

    expect($event->isMasterEvent())->toBeTrue();
});

it('isOccurrence when parent_event_id is set', function () {
    $master = Event::factory()->create(['is_recurring' => true]);
    $occurrence = Event::factory()->create(['parent_event_id' => $master->id]);

    expect($occurrence->isOccurrence())->toBeTrue();
});

it('masterEvent relationship works', function () {
    $master = Event::factory()->create(['is_recurring' => true]);
    $occurrence = Event::factory()->create(['parent_event_id' => $master->id]);

    expect($occurrence->masterEvent->id)->toBe($master->id);
});

it('occurrences relationship works', function () {
    $master = Event::factory()->create(['is_recurring' => true]);
    Event::factory()->count(2)->create(['parent_event_id' => $master->id]);

    expect($master->occurrences)->toHaveCount(2);
});
