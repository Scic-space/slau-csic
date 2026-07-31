<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCancelledNotification extends Notification
{
    public function __construct(public Event $event, public ?string $reason = null) {}

    public function getPreferenceType(): string
    {
        return 'event_cancellations';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Event Cancelled: {$this->event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We regret to inform you that **{$this->event->title}** has been cancelled.")
            ->when($this->reason, fn ($message) => $message->line("Reason: {$this->reason}"))
            ->line('Please visit the events page to find other upcoming events.')
            ->action('View Events', url('/events'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'reason' => $this->reason,
            'message' => "Event '{$this->event->title}' has been cancelled."
                .($this->reason ? " Reason: {$this->reason}" : ''),
        ];
    }
}
