<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventPublishedNotification extends Notification
{
    public function __construct(public Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->event->start_date?->format('l, F j, Y \a\t g:i A') ?? 'soon';

        return (new MailMessage)
            ->subject("New Event: {$this->event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new event **{$this->event->title}** has been published for **{$date}**.")
            ->line($this->event->location ? "Location: {$this->event->location}" : 'Check the event page for details.')
            ->action('View Event Details', url("/events/{$this->event->slug}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'message' => "New event published: {$this->event->title}",
        ];
    }
}
