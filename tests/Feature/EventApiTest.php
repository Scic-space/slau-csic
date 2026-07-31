<?php

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventCategory;
use App\Models\EventFeedback;
use App\Models\EventPrerequisite;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

// ─── Helpers ──────────────────────────────────────────────────────────

function eventApiUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'membership_status' => 'active',
        'approved_at' => now(),
    ], $overrides));
}

function eventApiAuthHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
}

function createEventPermissions(): void
{
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $perms = ['create_events', 'edit_events', 'delete_events', 'publish_events', 'cancel_events',
        'manage_registrations', 'manage_attendance', 'view_event_feedback', 'view_event_analytics', 'view_member_history',
    ];
    foreach ($perms as $perm) {
        \Spatie\Permission\Models\Permission::findOrCreate($perm, 'web');
    }
}

// ─── Setup ────────────────────────────────────────────────────────────

beforeEach(fn () => createEventPermissions());

// ─── GET /api/events (List) ──────────────────────────────────────────

describe('GET /api/events', function () {
    it('lists published events', function () {
        $user = eventApiUser();
        Event::factory()->count(3)->create([
            'status' => 'published',
            'is_public' => true,
            'registration_required' => true,
        ]);

        $response = getJson('/api/events', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(3);
    });

    it('excludes draft events', function () {
        $user = eventApiUser();
        Event::factory()->create(['status' => 'draft', 'is_public' => true]);

        $response = getJson('/api/events', eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(0);
    });

    it('filters by category', function () {
        $user = eventApiUser();
        $category = EventCategory::create(['name' => 'Cyber', 'slug' => 'cyber', 'color' => '#6366f1']);
        $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
        $event->categories()->attach($category);
        Event::factory()->create(['status' => 'published', 'is_public' => true]);

        $response = getJson('/api/events?category=cyber', eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(1);
    });

    it('filters by search', function () {
        $user = eventApiUser();
        Event::factory()->create(['status' => 'published', 'title' => 'Cybersecurity Workshop', 'is_public' => true]);
        Event::factory()->create(['status' => 'published', 'title' => 'Social Meetup', 'is_public' => true]);

        $response = getJson('/api/events?search=Cybersecurity', eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Cybersecurity Workshop');
    });

    it('filters by instructor_id', function () {
        $user = eventApiUser();
        $instructor = User::factory()->create();
        Event::factory()->create(['status' => 'published', 'instructor_id' => $instructor->id, 'is_public' => true]);
        Event::factory()->create(['status' => 'published', 'is_public' => true]);

        $response = getJson('/api/events?instructor_id='.$instructor->id, eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(1);
    });

    it('returns paginated results', function () {
        $user = eventApiUser();
        Event::factory()->count(5)->create(['status' => 'published', 'is_public' => true]);

        $response = getJson('/api/events?per_page=2', eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(2);
        expect($response->json('total'))->toBe(5);
        expect($response->json('last_page'))->toBe(3);
    });

    it('sorts by date ascending by default', function () {
        $user = eventApiUser();
        Event::factory()->create(['status' => 'published', 'start_date' => now()->addDays(10), 'is_public' => true]);
        Event::factory()->create(['status' => 'published', 'start_date' => now()->addDays(5), 'is_public' => true]);

        $response = getJson('/api/events', eventApiAuthHeaders($user));

        $dates = collect($response->json('data'))->pluck('start_date');
        expect($dates[0])->toBeLessThan($dates[1]);
    });

    it('is accessible without authentication', function () {
        getJson('/api/events')->assertSuccessful();
    });
});

// ─── POST /api/events (Create) ───────────────────────────────────────

describe('POST /api/events', function () {
    it('creates an event as authorized user', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');

        $response = postJson('/api/events', [
            'title' => 'Advanced SQL Injection Lab',
            'description' => 'Learn to identify and prevent SQL injection',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'location' => 'Main Lab',
            'type' => 'workshop',
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        $response->assertJson(['success' => true]);
        expect(Event::count())->toBe(1);
        expect(Event::first()->title)->toBe('Advanced SQL Injection Lab');
    });

    it('returns 403 for unauthorized user', function () {
        $user = eventApiUser();

        $response = postJson('/api/events', [
            'title' => 'Test Event',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'type' => 'workshop',
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertForbidden();
    });

    it('returns 401 without auth', function () {
        $response = postJson('/api/events', [
            'title' => 'Test Event',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'type' => 'workshop',
            'status' => 'draft',
        ]);

        $response->assertUnauthorized();
    });

    it('validates required title', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');

        $response = postJson('/api/events', [
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'type' => 'workshop',
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
        expect(Event::count())->toBe(0);
    });

    it('validates future date', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');

        $response = postJson('/api/events', [
            'title' => 'Past Event',
            'start_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'type' => 'workshop',
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('start_date');
    });

    it('validates event type', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');

        $response = postJson('/api/events', [
            'title' => 'Invalid Type',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'type' => 'invalid_type',
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    });

    it('validates max_participants is positive', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');

        $response = postJson('/api/events', [
            'title' => 'Bad Capacity',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'type' => 'workshop',
            'max_participants' => -5,
            'status' => 'draft',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('max_participants');
    });

    it('creates event with all optional fields', function () {
        $user = eventApiUser();
        $user->givePermissionTo('create_events');
        $instructor = User::factory()->create();

        $response = postJson('/api/events', [
            'title' => 'Full Event',
            'description' => 'Full description',
            'start_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'end_date' => now()->addWeek()->addHours(3)->format('Y-m-d H:i:s'),
            'location' => 'Lab Room 5',
            'virtual_link' => 'https://meet.google.com/abc-defg-hij',
            'type' => 'bootcamp',
            'max_participants' => 30,
            'registration_required' => true,
            'waitlist_enabled' => true,
            'is_public' => true,
            'visibility' => 'public',
            'registration_type' => 'first_come',
            'registration_deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'instructor_id' => $instructor->id,
            'requirements' => 'Bring your laptop',
            'learning_objectives' => 'Learn SQL injection',
            'skill_level' => 'intermediate',
            'registration_fee' => 10.50,
            'external_link' => 'https://example.com',
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        $event = Event::first();
        expect($event->title)->toBe('Full Event');
        expect($event->location)->toBe('Lab Room 5');
        expect($event->skill_level)->toBe('intermediate');
        expect((float) $event->registration_fee)->toBe(10.50);
    });
});

// ─── GET /api/events/{event} (Show) ──────────────────────────────────

describe('GET /api/events/{event}', function () {
    it('shows published event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);

        $response = getJson('/api/events/'.$event->id, eventApiAuthHeaders($user));

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['id', 'title', 'slug', 'description', 'start_date']]);
        expect($response->json('data.id'))->toBe($event->id);
    });

    it('returns 404 for draft event without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'draft']);

        $response = getJson('/api/events/'.$event->id, eventApiAuthHeaders($user));

        $response->assertNotFound();
    });
});

// ─── PUT /api/events/{event} (Update) ─────────────────────────────────

describe('PUT /api/events/{event}', function () {
    it('updates event as organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'organizer_id' => $user->id,
            'status' => 'published',
            'start_date' => now()->addWeek(),
        ]);

        $response = putJson('/api/events/'.$event->id, [
            'title' => 'Updated Title',
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($event->fresh()->title)->toBe('Updated Title');
    });

    it('denies update by non-organizer', function () {
        $owner = eventApiUser();
        $other = eventApiUser();
        $event = Event::factory()->create([
            'organizer_id' => $owner->id,
            'status' => 'published',
            'start_date' => now()->addWeek(),
        ]);

        $response = putJson('/api/events/'.$event->id, [
            'title' => 'Hacked Title',
        ], eventApiAuthHeaders($other));

        $response->assertForbidden();
    });

    it('prevents reducing capacity below registrations', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'organizer_id' => $user->id,
            'max_participants' => 10,
            'start_date' => now()->addWeek(),
        ]);
        EventRegistration::factory()->count(5)->create([
            'event_id' => $event->id,
            'status' => 'registered',
        ]);

        $response = putJson('/api/events/'.$event->id, [
            'max_participants' => 3,
        ], eventApiAuthHeaders($user));

        $response->assertStatus(400);
    });

    it('allows editing with edit_events permission', function () {
        $user = eventApiUser();
        $user->givePermissionTo('edit_events');
        $event = Event::factory()->create([
            'start_date' => now()->addWeek(),
        ]);

        $response = putJson('/api/events/'.$event->id, [
            'title' => 'Admin Updated',
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($event->fresh()->title)->toBe('Admin Updated');
    });
});

// ─── DELETE /api/events/{event} (Destroy) ─────────────────────────────

describe('DELETE /api/events/{event}', function () {
    it('deletes event as organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = deleteJson('/api/events/'.$event->id, [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect(Event::find($event->id))->toBeNull();
    });

    it('denies delete by non-organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = deleteJson('/api/events/'.$event->id, [], eventApiAuthHeaders($user));

        $response->assertForbidden();
    });
});

// ─── POST /api/events/{event}/publish ─────────────────────────────────

describe('POST /api/events/{event}/publish', function () {
    it('publishes draft event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'organizer_id' => $user->id,
            'status' => 'draft',
            'start_date' => now()->addWeek(),
        ]);

        $response = postJson('/api/events/'.$event->id.'/publish', [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($event->fresh()->status)->toBe('published');
    });

    it('requires publish_events permission for non-organizer', function () {
        $user = eventApiUser();
        $user->givePermissionTo('publish_events');
        $event = Event::factory()->create([
            'status' => 'draft',
            'start_date' => now()->addWeek(),
        ]);

        $response = postJson('/api/events/'.$event->id.'/publish', [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($event->fresh()->status)->toBe('published');
    });

    it('denies publish without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'draft']);

        $response = postJson('/api/events/'.$event->id.'/publish', [], eventApiAuthHeaders($user));

        $response->assertForbidden();
    });
});

// ─── POST /api/events/{event}/cancel ──────────────────────────────────

describe('POST /api/events/{event}/cancel', function () {
    it('cancels event and notifies registered members', function () {
        Notification::fake();
        $user = eventApiUser();
        $event = Event::factory()->create([
            'organizer_id' => $user->id,
            'status' => 'published',
            'start_date' => now()->addWeek(),
        ]);
        $member = eventApiUser();
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $response = postJson('/api/events/'.$event->id.'/cancel', [
            'reason' => 'Instructor illness',
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($event->fresh()->status)->toBe('cancelled');
        Notification::assertSentTo($member, \App\Notifications\EventCancelledNotification::class);
    });
});

// ─── GET /api/events/{event}/edit-permission ──────────────────────────

describe('GET /api/events/{event}/edit-permission', function () {
    it('returns true for organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = getJson('/api/events/'.$event->id.'/edit-permission', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('canEdit'))->toBeTrue();
    });

    it('returns false for non-organizer without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = getJson('/api/events/'.$event->id.'/edit-permission', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('canEdit'))->toBeFalse();
    });
});

// ─── POST /api/events/{event}/register ────────────────────────────────

describe('POST /api/events/{event}/register', function () {
    it('registers member for event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_required' => true,
            'registration_deadline' => now()->addDay(),
            'start_date' => now()->addWeek(),
            'max_participants' => 10,
        ]);

        $response = postJson('/api/events/'.$event->id.'/register', [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect(EventRegistration::where('event_id', $event->id)->where('user_id', $user->id)->exists())->toBeTrue();
    });

    it('prevents duplicate registration', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_required' => true,
            'registration_deadline' => now()->addDay(),
            'start_date' => now()->addWeek(),
        ]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $response = postJson('/api/events/'.$event->id.'/register', [], eventApiAuthHeaders($user));

        $response->assertStatus(409);
    });

    it('prevents registration for non-registration event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_required' => false,
        ]);

        $response = postJson('/api/events/'.$event->id.'/register', [], eventApiAuthHeaders($user));

        $response->assertStatus(400);
    });

    it('reinstates cancelled registration', function () {
        $user = eventApiUser();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_required' => true,
            'registration_deadline' => now()->addDay(),
            'start_date' => now()->addWeek(),
            'max_participants' => 10,
        ]);
        EventRegistration::factory()->cancelled()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = postJson('/api/events/'.$event->id.'/register', [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect(EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first()->status
        )->toBe('registered');
    });

    it('requires authentication', function () {
        $event = Event::factory()->create(['status' => 'published']);

        postJson('/api/events/'.$event->id.'/register')->assertUnauthorized();
    });
});

// ─── POST /api/events/{event}/unregister ──────────────────────────────

describe('POST /api/events/{event}/unregister', function () {
    it('unregisters member from event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'published']);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $response = postJson('/api/events/'.$event->id.'/unregister', [], eventApiAuthHeaders($user));

        $response->assertOk();
        expect(EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first()->status
        )->toBe('cancelled');
    });

    it('returns 404 when not registered', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'published']);

        $response = postJson('/api/events/'.$event->id.'/unregister', [], eventApiAuthHeaders($user));

        $response->assertNotFound();
    });
});

// ─── POST /api/events/{event}/cancel-rsvp ─────────────────────────────

describe('POST /api/events/{event}/cancel-rsvp', function () {
    it('cancels RSVP for registered member', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'published']);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'rsvp_status' => 'attending',
        ]);

        $response = postJson('/api/events/'.$event->id.'/cancel-rsvp', [], eventApiAuthHeaders($user));

        $response->assertOk();
        $reg = EventRegistration::where('event_id', $event->id)->where('user_id', $user->id)->first();
        expect($reg->rsvp_status)->toBe('not_attending');
        expect($reg->status)->toBe('cancelled');
    });
});

// ─── GET /api/user/events ─────────────────────────────────────────────

describe('GET /api/user/events', function () {
    it('lists my registered events', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'published']);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = getJson('/api/user/events', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.event.id'))->toBe($event->id);
    });

    it('requires authentication', function () {
        getJson('/api/user/events')->assertUnauthorized();
    });
});

// ─── GET /api/events/{event}/registrations ────────────────────────────

describe('GET /api/events/{event}/registrations', function () {
    it('lists registrations as organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);
        EventRegistration::factory()->count(2)->create(['event_id' => $event->id]);

        $response = getJson('/api/events/'.$event->id.'/registrations', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });

    it('denies access as non-organizer without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = getJson('/api/events/'.$event->id.'/registrations', eventApiAuthHeaders($user));

        $response->assertForbidden();
    });

    it('filters by status', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_registrations');
        $event = Event::factory()->create();
        EventRegistration::factory()->create(['event_id' => $event->id, 'status' => 'registered']);
        EventRegistration::factory()->waitlisted()->create(['event_id' => $event->id]);

        $response = getJson('/api/events/'.$event->id.'/registrations?status=waitlist', eventApiAuthHeaders($user));

        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.status'))->toBe('waitlist');
    });
});

// ─── POST /api/events/{event}/registrations/bulk ──────────────────────

describe('POST /api/events/{event}/registrations/bulk', function () {
    it('registers multiple members', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_registrations');
        $event = Event::factory()->create([
            'max_participants' => 10,
            'start_date' => now()->addWeek(),
            'registration_required' => true,
        ]);
        $members = User::factory()->count(3)->create();

        $response = postJson('/api/events/'.$event->id.'/registrations/bulk', [
            'member_ids' => $members->pluck('id')->toArray(),
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect(EventRegistration::where('event_id', $event->id)->count())->toBe(3);
        expect($response->json('results.registered'))->toBe(3);
    });

    it('denies without manage_registrations permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = postJson('/api/events/'.$event->id.'/registrations/bulk', [
            'member_ids' => [1, 2],
        ], eventApiAuthHeaders($user));

        $response->assertForbidden();
    });
});

// ─── Registration Approve/Reject/Remove ───────────────────────────────

describe('Registration approval endpoints', function () {
    it('approves pending registration and sends notification', function () {
        Notification::fake();
        $user = eventApiUser();
        $user->givePermissionTo('manage_registrations');
        $member = eventApiUser();
        $event = Event::factory()->create([
            'max_participants' => 10,
            'start_date' => now()->addWeek(),
        ]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $response = postJson(
            '/api/events/'.$event->id.'/registrations/'.$member->id.'/approve',
            [],
            eventApiAuthHeaders($user),
        );

        $response->assertOk();
        Notification::assertSentTo($member, \App\Notifications\RegistrationApprovedNotification::class);
    });

    it('rejects registration and sends notification', function () {
        Notification::fake();
        $user = eventApiUser();
        $user->givePermissionTo('manage_registrations');
        $member = eventApiUser();
        $event = Event::factory()->create(['start_date' => now()->addWeek()]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $response = postJson(
            '/api/events/'.$event->id.'/registrations/'.$member->id.'/reject',
            ['reason' => 'Does not meet prerequisites'],
            eventApiAuthHeaders($user),
        );

        $response->assertOk();
        expect(EventRegistration::where('event_id', $event->id)
            ->where('user_id', $member->id)
            ->first()->status
        )->toBe('cancelled');
        Notification::assertSentTo($member, \App\Notifications\RegistrationRejectedNotification::class);
    });

    it('removes registration', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_registrations');
        $member = eventApiUser();
        $event = Event::factory()->create();
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
        ]);

        $response = deleteJson(
            '/api/events/'.$event->id.'/registrations/'.$member->id,
            [],
            eventApiAuthHeaders($user),
        );

        $response->assertOk();
        expect(EventRegistration::where('event_id', $event->id)->count())->toBe(0);
    });
});

// ─── Attendance Endpoints ─────────────────────────────────────────────

describe('Event attendance endpoints', function () {
    it('marks attendance for registered member', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $member = eventApiUser();
        $event = Event::factory()->create(['start_date' => now()->subDay()]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $response = postJson('/api/events/'.$event->id.'/attendance', [
            'member_id' => $member->id,
            'status' => 'present',
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect(EventAttendance::where('event_id', $event->id)->where('member_id', $member->id)->exists())->toBeTrue();
    });

    it('denies marking attendance before event starts', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $member = eventApiUser();
        $event = Event::factory()->create(['start_date' => now()->addWeek()]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $response = postJson('/api/events/'.$event->id.'/attendance', [
            'member_id' => $member->id,
            'status' => 'present',
        ], eventApiAuthHeaders($user));

        $response->assertStatus(400);
    });

    it('lists attendance records', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $event = Event::factory()->create();
        EventAttendance::factory()->count(2)->create(['event_id' => $event->id]);

        $response = getJson('/api/events/'.$event->id.'/attendance', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });

    it('exports attendance as CSV', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $event = Event::factory()->create(['slug' => 'test-event']);
        EventAttendance::factory()->create([
            'event_id' => $event->id,
            'status' => 'present',
        ]);

        $response = getJson('/api/events/'.$event->id.'/attendance/export', eventApiAuthHeaders($user));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        expect($response->content())->toContain('Member Name,Status');
    });

    it('handles bulk attendance', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $event = Event::factory()->create(['start_date' => now()->subDay()]);
        $members = User::factory()->count(3)->create();
        foreach ($members as $member) {
            EventRegistration::factory()->create([
                'event_id' => $event->id,
                'user_id' => $member->id,
                'status' => 'registered',
            ]);
        }

        $response = postJson('/api/events/'.$event->id.'/attendance/bulk', [
            'attendance_data' => $members->map(fn ($m) => [
                'member_id' => $m->id,
                'status' => 'present',
            ])->toArray(),
        ], eventApiAuthHeaders($user));

        $response->assertOk();
        expect(EventAttendance::where('event_id', $event->id)->count())->toBe(3);
    });
});

// ─── Prerequisites ────────────────────────────────────────────────────

describe('Event prerequisites endpoints', function () {
    it('lists prerequisites', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();
        $prereqEvent = Event::factory()->create();
        EventPrerequisite::create([
            'event_id' => $event->id,
            'prerequisite_event_id' => $prereqEvent->id,
        ]);

        $response = getJson('/api/events/'.$event->id.'/prerequisites', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.prerequisite_event.id'))->toBe($prereqEvent->id);
    });

    it('creates prerequisite', function () {
        $user = eventApiUser();
        $user->givePermissionTo('edit_events');
        $event = Event::factory()->create();
        $prereqEvent = Event::factory()->create();

        $response = postJson('/api/events/'.$event->id.'/prerequisites', [
            'prerequisite_event_id' => $prereqEvent->id,
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        expect(EventPrerequisite::where('event_id', $event->id)->count())->toBe(1);
    });
});

// ─── Feedback ─────────────────────────────────────────────────────────

describe('Event feedback endpoints', function () {
    it('submits feedback', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['status' => 'completed', 'end_date' => now()->subDay()]);
        EventRegistration::factory()->attended()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = postJson('/api/events/'.$event->id.'/feedback', [
            'rating' => 5,
            'feedback_text' => 'Great event!',
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        expect(EventFeedback::where('event_id', $event->id)->count())->toBe(1);
    });

    it('lists feedback for authorized user', function () {
        $user = eventApiUser();
        $user->givePermissionTo('view_event_feedback');
        $event = Event::factory()->create();

        $response = getJson('/api/events/'.$event->id.'/feedback', eventApiAuthHeaders($user));

        $response->assertOk();
        $response->assertJsonStructure(['data', 'aggregate']);
    });

    it('denies feedback list without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = getJson('/api/events/'.$event->id.'/feedback', eventApiAuthHeaders($user));

        $response->assertForbidden();
    });
});

// ─── Analytics ────────────────────────────────────────────────────────

describe('GET /api/events/analytics/summary', function () {
    it('returns analytics for authorized user', function () {
        $user = eventApiUser();
        $user->givePermissionTo('view_event_analytics');

        $response = getJson('/api/events/analytics/summary', eventApiAuthHeaders($user));

        $response->assertOk();
        $response->assertJsonStructure([
            'total_events',
            'upcoming_events',
            'events_this_month',
            'active_members',
        ]);
    });

    it('denies without view_event_analytics permission', function () {
        $user = eventApiUser();

        $response = getJson('/api/events/analytics/summary', eventApiAuthHeaders($user));

        $response->assertForbidden();
    });
});

// ─── Member Event History ─────────────────────────────────────────────

describe('GET /api/members/{member}/event-history', function () {
    it('shows own event history', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = getJson('/api/members/'.$user->id.'/event-history', eventApiAuthHeaders($user));

        $response->assertOk();
        $response->assertJsonStructure(['member', 'stats', 'registrations']);
    });

    it('denies viewing other member history without permission', function () {
        $user = eventApiUser();
        $other = eventApiUser();

        $response = getJson('/api/members/'.$other->id.'/event-history', eventApiAuthHeaders($user));

        $response->assertForbidden();
    });

    it('allows viewing other member history with permission', function () {
        $user = eventApiUser();
        $user->givePermissionTo('view_member_history');
        $other = eventApiUser();

        $response = getJson('/api/members/'.$other->id.'/event-history', eventApiAuthHeaders($user));

        $response->assertOk();
    });
});

// ─── Edge Cases ───────────────────────────────────────────────────────

describe('Edge cases', function () {
    it('handles analytics with no events', function () {
        $user = eventApiUser();
        $user->givePermissionTo('view_event_analytics');

        $response = getJson('/api/events/analytics/summary', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('total_events'))->toBe(0);
        expect($response->json('attendance_rate'))->toBe(0);
    });

    it('handles empty attendance export', function () {
        $user = eventApiUser();
        $user->givePermissionTo('manage_attendance');
        $event = Event::factory()->create();

        $response = getJson('/api/events/'.$event->id.'/attendance/export', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->content())->toContain('Member Name,Status');
    });

    it('handles empty registration list', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = getJson('/api/events/'.$event->id.'/registrations', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(0);
    });
});

// ─── Event Materials (Resources) ─────────────────────────────────────

describe('Event materials endpoints', function () {
    it('lists materials for an event', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();
        \App\Models\EventResource::factory()->count(2)->create(['event_id' => $event->id]);

        $response = getJson('/api/events/'.$event->id.'/materials', eventApiAuthHeaders($user));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });

    it('creates a link material', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = postJson('/api/events/'.$event->id.'/materials', [
            'title' => 'Workshop Slides',
            'type' => 'slide',
            'url' => 'https://slides.example.com/workshop',
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        expect($response->json('data.title'))->toBe('Workshop Slides');
        expect($event->resources()->count())->toBe(1);
    });

    it('denies material creation by non-organizer without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();

        $response = postJson('/api/events/'.$event->id.'/materials', [
            'title' => 'My Material',
            'type' => 'document',
            'url' => 'https://example.com/doc',
        ], eventApiAuthHeaders($user));

        $response->assertForbidden();
    });

    it('allows material creation with edit_events permission', function () {
        $user = eventApiUser();
        $user->givePermissionTo('edit_events');
        $event = Event::factory()->create();

        $response = postJson('/api/events/'.$event->id.'/materials', [
            'title' => 'Admin Material',
            'type' => 'code',
            'url' => 'https://example.com/code',
        ], eventApiAuthHeaders($user));

        $response->assertCreated();
        expect($event->resources()->count())->toBe(1);
    });

    it('deletes material as organizer', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);
        $resource = \App\Models\EventResource::factory()->create([
            'event_id' => $event->id,
            'type' => 'link',
            'url' => 'https://example.com',
        ]);

        $response = deleteJson(
            '/api/events/'.$event->id.'/materials/'.$resource->id,
            [],
            eventApiAuthHeaders($user),
        );

        $response->assertOk();
        expect(\App\Models\EventResource::find($resource->id))->toBeNull();
    });

    it('denies material deletion by non-organizer without permission', function () {
        $user = eventApiUser();
        $event = Event::factory()->create();
        $resource = \App\Models\EventResource::factory()->create(['event_id' => $event->id]);

        $response = deleteJson(
            '/api/events/'.$event->id.'/materials/'.$resource->id,
            [],
            eventApiAuthHeaders($user),
        );

        $response->assertForbidden();
    });

    it('validates required title and type', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = postJson('/api/events/'.$event->id.'/materials', [
            'url' => 'https://example.com',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['title', 'type']);
    });

    it('validates material type enum', function () {
        $user = eventApiUser();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        $response = postJson('/api/events/'.$event->id.'/materials', [
            'title' => 'Bad Type',
            'type' => 'invalid_type',
            'url' => 'https://example.com',
        ], eventApiAuthHeaders($user));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    });
});
