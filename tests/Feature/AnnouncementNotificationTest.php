<?php

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\BroadcastMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates database notifications for all members when push delivery is enabled', function () {
    $first = User::factory()->create(['membership_status' => 'active']);
    $second = User::factory()->create(['membership_status' => 'active']);

    Announcement::factory()->create([
        'title' => 'Club-wide announcement',
        'audience' => 'all',
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $first->id)->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $second->id)->count())->toBe(1);
});

it('skips rejected and left members for the all audience', function () {
    $active = User::factory()->create(['membership_status' => 'active']);
    $rejected = User::factory()->create(['membership_status' => 'rejected']);
    $left = User::factory()->create(['membership_status' => 'left']);

    Announcement::factory()->create([
        'audience' => 'all',
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $active->id)->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $rejected->id)->count())->toBe(0)
        ->and(DB::table('notifications')->where('notifiable_id', $left->id)->count())->toBe(0);
});

it('notifies only active members when the audience is active members', function () {
    $active = User::factory()->create(['membership_status' => 'active']);
    $pending = User::factory()->create(['membership_status' => 'pending']);

    Announcement::factory()->create([
        'audience' => 'active_members',
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $active->id)->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $pending->id)->count())->toBe(0);
});

it('notifies only members with the targeted roles', function () {
    $targeted = User::factory()->create(['membership_status' => 'active']);
    $member = User::factory()->create(['membership_status' => 'active']);

    Role::findOrCreate('Events Lead');
    $targeted->assignRole('Events Lead');

    Announcement::factory()->create([
        'audience' => 'specific_roles',
        'target_roles' => ['Events Lead'],
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $targeted->id)->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);
});

it('does not notify anyone when targeted roles are missing', function () {
    $member = User::factory()->create(['membership_status' => 'active']);

    Announcement::factory()->create([
        'audience' => 'specific_roles',
        'target_roles' => null,
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);
});

it('sends an email when email delivery is enabled', function () {
    Notification::fake();

    $member = User::factory()->create(['membership_status' => 'active']);

    Announcement::factory()->create([
        'title' => 'Email only',
        'audience' => 'all',
        'is_published' => true,
        'send_push' => false,
        'send_email' => true,
    ]);

    Notification::assertSentTo($member, BroadcastMessage::class);
});

it('creates no notifications when both delivery toggles are off', function () {
    $member = User::factory()->create(['membership_status' => 'active']);

    Announcement::factory()->create([
        'audience' => 'all',
        'is_published' => true,
        'send_push' => false,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);
});

it('does not create notifications for draft announcements', function () {
    $member = User::factory()->create(['membership_status' => 'active']);

    Announcement::factory()->draft()->create([
        'audience' => 'all',
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);
});

it('does not resend notifications when an already published announcement is edited', function () {
    $member = User::factory()->create(['membership_status' => 'active']);

    $announcement = Announcement::factory()->create([
        'audience' => 'all',
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    $announcement->update(['title' => 'Updated title']);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(1);
});

it('sends notifications when a draft is published later', function () {
    $member = User::factory()->create(['membership_status' => 'active']);

    $announcement = Announcement::factory()->draft()->create([
        'audience' => 'all',
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);

    $announcement->update(['is_published' => true]);

    expect(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(1);
});

it('respects the board audience for board-only announcements', function () {
    $board = User::factory()->create(['membership_status' => 'active']);
    $member = User::factory()->create(['membership_status' => 'active']);

    foreach (Announcement::BOARD_ROLES as $role) {
        Role::findOrCreate($role);
    }

    $board->assignRole('President');

    Announcement::factory()->create([
        'audience' => 'board',
        'is_published' => true,
        'send_push' => true,
        'send_email' => false,
    ]);

    expect(DB::table('notifications')->where('notifiable_id', $board->id)->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $member->id)->count())->toBe(0);
});
