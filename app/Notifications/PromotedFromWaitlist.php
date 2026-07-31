<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromotedFromWaitlist extends Notification
{
    public function __construct(public Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Spot Available: {$this->event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A spot has opened up for **{$this->event->title}**.")
            ->line('You have been automatically promoted from the waitlist and are now registered!')
            ->action('View Event', url("/events/{$this->event->slug}"))
            ->line('Please update your RSVP if your plans have changed.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'message' => "A spot opened up for {$this->event->title}. You've been promoted from the waitlist!",
        ];
    }
}
