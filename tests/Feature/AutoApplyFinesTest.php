<?php

use App\Console\Commands\AutoApplyFines;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\Fine;
use App\Models\FineType;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\FineIssuedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AutoApplyFinesTest extends TestCase
{
    use RefreshDatabase;

    private FineType $eventFineType;

    private FineType $meetingFineType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->eventFineType = FineType::factory()->create([
            'name' => 'Event No-Show',
            'auto_apply_trigger' => 'event_no_show',
            'auto_apply_threshold' => 7,
            'default_amount' => 10000.00,
        ]);

        $this->meetingFineType = FineType::factory()->create([
            'name' => 'Missed Meeting',
            'auto_apply_trigger' => 'missed_meetings',
            'auto_apply_threshold' => 3,
            'default_amount' => 5000.00,
        ]);
    }

    public function test_it_fines_registered_members_who_miss_a_completed_event(): void
    {
        Notification::fake();

        $member = User::factory()->create();
        $organizer = User::factory()->create();
        $event = Event::factory()->create([
            'start_date' => now()->subDay(),
            'status' => 'completed',
            'organizer_id' => $organizer->id,
            'no_show_fine_amount' => 15000.00,
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $fine = Fine::where('user_id', $member->id)->first();

        $this->assertNotNull($fine);
        $this->assertSame('15000.00', (string) $fine->amount);
        $this->assertSame($this->eventFineType->id, $fine->fine_type_id);
        $this->assertSame('pending', $fine->status);
        $this->assertSame($organizer->id, $fine->issued_by);
        $this->assertStringContainsString("(Event #{$event->id})", $fine->reason);

        Notification::assertSentTo($member, FineIssuedNotification::class);
    }

    public function test_it_uses_the_fine_type_default_when_event_has_no_amount(): void
    {
        $member = User::factory()->create();
        $event = Event::factory()->create([
            'start_date' => now()->subDay(),
            'status' => 'completed',
            'no_show_fine_amount' => null,
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_skips_members_who_attended_the_event(): void
    {
        $member = User::factory()->create();
        $event = Event::factory()->create([
            'start_date' => now()->subDay(),
            'status' => 'completed',
            'no_show_fine_amount' => 10000.00,
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
        ]);

        EventAttendance::create([
            'event_id' => $event->id,
            'member_id' => $member->id,
            'status' => 'present',
            'checked_in_at' => now(),
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_skips_future_and_cancelled_events(): void
    {
        $member = User::factory()->create();

        $future = Event::factory()->create([
            'start_date' => now()->addWeek(),
            'no_show_fine_amount' => 10000.00,
        ]);

        $cancelled = Event::factory()->create([
            'start_date' => now()->subDay(),
            'cancelled_at' => now(),
            'no_show_fine_amount' => 10000.00,
        ]);

        EventRegistration::factory()->create(['event_id' => $future->id, 'user_id' => $member->id]);
        EventRegistration::factory()->create(['event_id' => $cancelled->id, 'user_id' => $member->id]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseCount('fines', 0);
    }

    public function test_it_does_not_create_duplicate_fines_for_an_event(): void
    {
        $member = User::factory()->create();
        $event = Event::factory()->create([
            'start_date' => now()->subDay(),
            'status' => 'completed',
            'no_show_fine_amount' => 10000.00,
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();
        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertSame(1, Fine::where('user_id', $member->id)->count());
    }

    public function test_it_fines_active_members_who_missed_an_ended_meeting(): void
    {
        Notification::fake();

        $member = User::factory()->create();
        $issuer = User::factory()->create(['membership_type' => 'associate']);
        $meeting = Meeting::factory()->past()->create([
            'missed_fine_amount' => 5000.00,
            'created_by' => $issuer->id,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $fine = Fine::where('user_id', $member->id)->first();

        $this->assertNotNull($fine);
        $this->assertSame('5000.00', (string) $fine->amount);
        $this->assertSame($this->meetingFineType->id, $fine->fine_type_id);
        $this->assertSame($issuer->id, $fine->issued_by);
        $this->assertStringContainsString("(Meeting #{$meeting->id})", $fine->reason);

        Notification::assertSentTo($member, FineIssuedNotification::class);
    }

    public function test_it_skips_members_who_attended_the_meeting(): void
    {
        $member = User::factory()->create();
        $meeting = Meeting::factory()->past()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $meeting->attendance()->create([
            'user_id' => $member->id,
            'status' => 'present',
            'checked_in_at' => now(),
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_skips_meetings_that_have_not_ended(): void
    {
        $member = User::factory()->create();
        Meeting::factory()->upcoming()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_skips_cancelled_meetings(): void
    {
        $member = User::factory()->create();
        Meeting::factory()->past()->cancelled()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_only_fines_allowed_attendees_when_defined(): void
    {
        $allowed = User::factory()->create();
        $other = User::factory()->create();
        $meeting = Meeting::factory()->past()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $meeting->allowedAttendees()->attach($allowed->id);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseHas('fines', ['user_id' => $allowed->id]);
        $this->assertDatabaseMissing('fines', ['user_id' => $other->id]);
    }

    public function test_it_skips_meetings_without_a_fine_amount(): void
    {
        $member = User::factory()->create();
        Meeting::factory()->past()->create([
            'missed_fine_amount' => null,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseMissing('fines', ['user_id' => $member->id]);
    }

    public function test_it_does_not_create_duplicate_fines_for_a_meeting(): void
    {
        $member = User::factory()->create();
        Meeting::factory()->past()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();
        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertSame(1, Fine::where('user_id', $member->id)->count());
    }

    public function test_it_does_nothing_when_no_fine_types_are_configured(): void
    {
        FineType::query()->delete();

        $member = User::factory()->create();
        $event = Event::factory()->create([
            'start_date' => now()->subDay(),
            'no_show_fine_amount' => 10000.00,
        ]);

        EventRegistration::factory()->create(['event_id' => $event->id, 'user_id' => $member->id]);

        $this->artisan(AutoApplyFines::class)->assertSuccessful();

        $this->assertDatabaseCount('fines', 0);
    }

    public function test_dry_run_does_not_create_fines(): void
    {
        $member = User::factory()->create();
        Meeting::factory()->past()->create([
            'missed_fine_amount' => 5000.00,
        ]);

        $this->artisan(AutoApplyFines::class, ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('fines', 0);
    }
}
