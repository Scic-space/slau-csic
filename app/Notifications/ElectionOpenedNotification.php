<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionOpenedNotification extends Notification
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
            ->subject("Voting Open: {$this->election->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Voting is now open for {$this->election->title} ({$this->election->position}).")
            ->line('Review the candidates and cast your ballot before voting closes.')
            ->action('Cast Your Vote', route('voting.index'))
            ->line('Every vote counts!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'election_opened',
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'position' => $this->election->position,
            'message' => "Voting is now open for {$this->election->title}",
        ];
    }
}
