<?php

namespace App\Notifications;

use App\Models\Competition;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompetitionResultsNotification extends Notification
{
    public function __construct(public Competition $competition) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Results: {$this->competition->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Results for **{$this->competition->name}** have been published!");

        if ($this->competition->club_ranking) {
            $message->line("Club Ranking: #{$this->competition->club_ranking}");
        }

        if ($this->competition->achievements) {
            $message->line("Achievements: {$this->competition->achievements}");
        }

        $message->action('View Results', url("/competitions/{$this->competition->id}"));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'competition_id' => $this->competition->id,
            'competition_name' => $this->competition->name,
            'club_ranking' => $this->competition->club_ranking,
            'achievements' => $this->competition->achievements,
            'message' => "Results published for {$this->competition->name}",
        ];
    }
}
