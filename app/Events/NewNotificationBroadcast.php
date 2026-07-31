<?php

namespace App\Events;

use App\Notifications\NotificationTypeConfig;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NewNotificationBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $userId;

    public string $message;

    public string $type;

    public string $category;

    public string $icon;

    public string $color;

    public ?string $actionUrl;

    public string $createdAt;

    public int $unreadCount;

    public function __construct(
        public Notification $notification,
    ) {
        $this->userId = $this->notification->notifiable_id;
        $this->message = $this->notification->data['message'] ?? $this->notification->data['subject'] ?? 'New notification';
        $this->type = class_basename($this->notification->type);
        $this->actionUrl = $this->notification->data['action_url'] ?? null;
        $this->createdAt = $this->notification->created_at->toISOString();

        $config = NotificationTypeConfig::for($this->notification->type);
        $this->category = $config['category'];
        $this->icon = $config['icon'];
        $this->color = $config['color'];

        $this->unreadCount = $this->notification->notifiable->unreadNotifications()->count();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'category' => $this->category,
            'icon' => $this->icon,
            'color' => $this->color,
            'action_url' => $this->actionUrl,
            'created_at' => $this->createdAt,
            'unread_count' => $this->unreadCount,
        ];
    }
}
