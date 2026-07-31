<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification
{
    public function __construct(public Event $event) {}

    public function getPreferenceType(): string
    {
        return 'event_reminders';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->event->start_date->format('l, F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject("Reminder: {$this->event->title} starts tomorrow")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder that **{$this->event->title}** is happening tomorrow!")
            ->line("Date: {$date}")
            ->line("Location: {$this->event->location}")
            ->when($this->event->virtual_link, fn ($message) => $message->line("Join online: {$this->event->virtual_link}"))
            ->action('View Event Details', url("/events/{$this->event->slug}"))
            ->line('We look forward to seeing you there!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'message' => "Reminder: {$this->event->title} starts tomorrow at {$this->event->start_date->format('g:i A')}",
        ];
    }
}
