<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionResultsPublishedNotification extends Notification
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
            ->subject("Results Published: {$this->election->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Results for {$this->election->title} ({$this->election->position}) have been published.")
            ->action('View Results', route('voting.results'))
            ->line('Thank you for participating in the election process.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'election_results_published',
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'position' => $this->election->position,
            'message' => "Results published for {$this->election->title}",
        ];
    }
}
