<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingScheduledNotification extends Notification
{
    public function __construct(public Meeting $meeting) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->meeting->scheduled_at?->format('D, M j \a\t g:i A') ?? 'soon';
        $label = $this->meeting->isTeachingSession() ? 'Class' : 'Meeting';
        $labelLower = strtolower($label);

        return (new MailMessage)
            ->subject("New {$label} Scheduled: {$this->meeting->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new internal {$labelLower} **{$this->meeting->title}** has been scheduled for **{$when}**.")
            ->line($this->meeting->location ? "Location: {$this->meeting->location}" : "Check the {$labelLower} page for joining details.")
            ->action("View {$label}", $this->meeting->memberViewUrl());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'meeting_title' => $this->meeting->title,
            'action_url' => $this->meeting->memberViewUrl(),
            'message' => 'A new '.($this->meeting->isTeachingSession() ? 'class' : 'meeting')." '{$this->meeting->title}' has been scheduled.",
        ];
    }
}
