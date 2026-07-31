<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejectedNotification extends Notification
{
    public function __construct(
        public Event $event,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Registration Update: {$this->event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your registration for **{$this->event->title}** could not be approved.");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        return $message
            ->action('View Event', url("/events/{$this->event->slug}"))
            ->line('If you have questions, please contact the event organizer.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'reason' => $this->reason,
            'message' => "Your registration for {$this->event->title} could not be approved."
                .($this->reason ? " Reason: {$this->reason}" : ''),
        ];
    }
}
