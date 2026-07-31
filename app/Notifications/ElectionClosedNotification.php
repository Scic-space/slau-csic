<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionClosedNotification extends Notification
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
            ->subject("Voting Closed: {$this->election->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Voting has closed for {$this->election->title} ({$this->election->position}).")
            ->line('Results will be available once published by the administration.')
            ->action('View Results', route('voting.results'))
            ->line('Thank you for participating!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'election_closed',
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'position' => $this->election->position,
            'message' => "Voting has closed for {$this->election->title}",
        ];
    }
}
