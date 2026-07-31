<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingReminderNotification extends Notification
{
    public function __construct(public Meeting $meeting, public string $type = '24h') {}

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
        $date = $this->meeting->scheduled_at->format('l, F j, Y \a\t g:i A');
        $prefix = $this->type === '1h' ? 'starts in 1 hour' : 'starts tomorrow';

        return (new MailMessage)
            ->subject("Reminder: {$this->meeting->title} {$prefix}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder that **{$this->meeting->title}** {$prefix}!")
            ->line("Date: {$date}")
            ->line("Location: {$this->meeting->location}")
            ->when($this->meeting->hasMeetingLink(), fn ($message) => $message->line("Join online: {$this->meeting->meeting_link}"))
            ->action('View Meeting Details', url("/admin/meetings/{$this->meeting->id}/edit"))
            ->line('Please make sure to attend on time.');
    }

    public function toArray(object $notifiable): array
    {
        $when = $this->type === '1h' ? 'in 1 hour' : 'tomorrow';
        $time = $this->meeting->scheduled_at->format('g:i A');

        return [
            'meeting_id' => $this->meeting->id,
            'meeting_title' => $this->meeting->title,
            'type' => $this->type,
            'message' => "Reminder: {$this->meeting->title} {$when} at {$time}",
        ];
    }
}
