<?php

use App\Events\NewNotificationBroadcast;
use App\Models\User;
use App\Notifications\BroadcastMessage;
use App\Notifications\NotificationTypeConfig;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('builds a valid payload from the persisted notification', function () {
    $user = User::factory()->create();
    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => BroadcastMessage::class,
        'data' => ['subject' => 'Welcome aboard', 'body' => 'Thanks for joining.'],
    ]);

    $event = new NewNotificationBroadcast($notification);

    expect($event->userId)->toBe($user->id)
        ->and($event->message)->toBe('Welcome aboard')
        ->and($event->type)->toBe('BroadcastMessage')
        ->and($event->category)->toBe(NotificationTypeConfig::for(BroadcastMessage::class)['category'])
        ->and($event->color)->toBe(NotificationTypeConfig::for(BroadcastMessage::class)['bgColor'])
        ->and($event->actionUrl)->toBeNull()
        ->and($event->unreadCount)->toBe(1)
        ->and($event->createdAt)->toBeString()
        ->and($event->broadcastAs())->toBe('notification.new')
        ->and($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);
});

it('falls back to the configured colors when the config has no color key', function () {
    $user = User::factory()->create();
    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => BroadcastMessage::class,
        'data' => ['subject' => 'Hey', 'body' => 'What up'],
    ]);

    $payload = (new NewNotificationBroadcast($notification))->broadcastWith();

    expect($payload['color'])->toBe(NotificationTypeConfig::for(BroadcastMessage::class)['bgColor']);
});
