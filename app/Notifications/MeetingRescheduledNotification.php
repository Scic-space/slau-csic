<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingRescheduledNotification extends Notification
{
    public function __construct(
        public Meeting $meeting,
        public string $oldDate,
        public string $newDate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Meeting Rescheduled: {$this->meeting->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("**{$this->meeting->title}** has been rescheduled.")
            ->line("Old date: {$this->oldDate}")
            ->line("New date: {$this->newDate}")
            ->line("Location: {$this->meeting->location}")
            ->action('View Meeting Details', url("/admin/meetings/{$this->meeting->id}/edit"))
            ->line('Please update your calendar accordingly.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'meeting_title' => $this->meeting->title,
            'old_date' => $this->oldDate,
            'new_date' => $this->newDate,
            'message' => "Meeting '{$this->meeting->title}' rescheduled from {$this->oldDate} to {$this->newDate}",
        ];
    }
}
