<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Election $election) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: {$this->election->title} closes soon")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that voting for {$this->election->title} ({$this->election->position}) closes soon.")
            ->line("Deadline: {$this->election->ends_at->format('F j, Y g:i A')}")
            ->action('Cast Your Vote', url('/voting'))
            ->line("Don't miss your chance to vote!");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'election_reminder',
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'message' => "Reminder: {$this->election->title} closes {$this->election->ends_at->diffForHumans()}",
        ];
    }
}
