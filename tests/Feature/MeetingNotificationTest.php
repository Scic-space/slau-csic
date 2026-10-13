<?php

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingCancelledNotification;
use App\Notifications\MeetingReminderNotification;
use App\Notifications\MeetingRescheduledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    actingAs($this->admin);
});

it('sends cancellation notification', function () {
    Notification::fake();
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();
    $reason = 'Room unavailable';

    Notification::send($user, new MeetingCancelledNotification($meeting, $reason));

    Notification::assertSentTo(
        [$user],
        MeetingCancelledNotification::class,
        function ($notification) use ($meeting, $reason) {
            return $notification->meeting->id === $meeting->id
                && $notification->reason === $reason;
        }
    );
});

it('sends reschedule notification', function () {
    Notification::fake();
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();
    $oldDate = $meeting->scheduled_at->format('M d, Y g:i A');
    $newDateTime = now()->addWeek()->format('M d, Y g:i A');

    Notification::send($user, new MeetingRescheduledNotification($meeting, $oldDate, $newDateTime));

    Notification::assertSentTo(
        [$user],
        MeetingRescheduledNotification::class,
        function ($notification) use ($meeting) {
            return $notification->meeting->id === $meeting->id;
        }
    );
});

it('sends reminder notification', function () {
    Notification::fake();
    $user = User::factory()->create();
    $meeting = Meeting::factory()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addDay(),
    ]);

    Notification::send($user, new MeetingReminderNotification($meeting, '24h'));

    Notification::assertSentTo(
        [$user],
        MeetingReminderNotification::class,
        function ($notification) {
            return $notification->type === '24h';
        }
    );
});

it('sends reminders only to the targeted audience', function () {
    Notification::fake();

    $yearOne = User::factory()->create();
    $yearOne->memberProfile()->create(['year_of_study' => 1]);
    $yearThree = User::factory()->create();
    $yearThree->memberProfile()->create(['year_of_study' => 3]);

    $meeting = Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addDay()->setTime(11, 0),
        'audience' => 'year_1',
    ]);

    $this->artisan('meetings:send-reminders', ['--type' => '24h'])->assertSuccessful();

    Notification::assertSentTo($yearOne, MeetingReminderNotification::class);
    Notification::assertNotSentTo($yearThree, MeetingReminderNotification::class);
});

it('completes reminders when a recipient mail fails', function () {
    config(['mail.default' => 'throwing']);

    Mail::extend('throwing', fn () => new class implements \Symfony\Component\Mailer\Transport\TransportInterface
    {
        public function send(\Symfony\Component\Mime\RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): ?\Symfony\Component\Mailer\SentMessage
        {
            throw new \Symfony\Component\Mailer\Exception\TransportException('Mail is down.');
        }

        public function __toString(): string
        {
            return 'throwing';
        }
    });

    User::factory()->create();
    Meeting::factory()->teachingSession()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addDay()->setTime(11, 0),
    ]);

    $this->artisan('meetings:send-reminders', ['--type' => '24h'])->assertSuccessful();
});

it('creates database notification on reminder', function () {
    Notification::fake();
    $user = User::factory()->create();
    $meeting = Meeting::factory()->create([
        'created_by' => $this->admin->id,
        'scheduled_at' => now()->addDay(),
    ]);

    Notification::send($user, new MeetingReminderNotification($meeting, '24h'));

    Notification::assertSentTo(
        [$user],
        MeetingReminderNotification::class
    );
});
