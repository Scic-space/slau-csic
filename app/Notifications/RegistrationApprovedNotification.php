<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApprovedNotification extends Notification
{
    public function __construct(
        public Event $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Registration Approved: {$this->event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your registration for **{$this->event->title}** has been approved.")
            ->action('View Event', url("/events/{$this->event->slug}"))
            ->line('We look forward to seeing you there!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'message' => "Your registration for {$this->event->title} has been approved.",
        ];
    }
}
