<?php

namespace App\Notifications;

use App\Models\Competition;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompetitionReminderNotification extends Notification
{
    public function __construct(public Competition $competition, public string $type = '24h') {}

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
        $date = $this->competition->start_date->format('l, F j, Y \a\t g:i A');
        $prefix = $this->type === '1h' ? 'starts in 1 hour' : 'starts tomorrow';

        return (new MailMessage)
            ->subject("Reminder: {$this->competition->name} {$prefix}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder that **{$this->competition->name}** {$prefix}!")
            ->line("Date: {$date}")
            ->when($this->competition->location, fn ($message) => $message->line("Location: {$this->competition->location}"))
            ->when($this->competition->is_team_based, fn ($message) => $message->line('This is a team-based competition.'))
            ->action('View Competition Details', url("/competitions/{$this->competition->id}"))
            ->line('Good luck and have fun!');
    }

    public function toArray(object $notifiable): array
    {
        $when = $this->type === '1h' ? 'in 1 hour' : 'tomorrow';

        return [
            'competition_id' => $this->competition->id,
            'competition_name' => $this->competition->name,
            'type' => $this->type,
            'message' => "Reminder: {$this->competition->name} {$when}",
        ];
    }
}
