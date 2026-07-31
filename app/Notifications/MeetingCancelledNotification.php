<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingCancelledNotification extends Notification
{
    public function __construct(public Meeting $meeting, public ?string $reason = null) {}

    public function getPreferenceType(): string
    {
        return 'event_cancellations';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Meeting Cancelled: {$this->meeting->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We regret to inform you that **{$this->meeting->title}** has been cancelled.")
            ->when($this->reason, fn ($message) => $message->line("Reason: {$this->reason}"))
            ->line('Please check the schedule for other upcoming meetings.')
            ->action('View Schedule', url('/admin/meetings'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'meeting_title' => $this->meeting->title,
            'reason' => $this->reason,
            'message' => "Meeting '{$this->meeting->title}' has been cancelled."
                .($this->reason ? " Reason: {$this->reason}" : ''),
        ];
    }
}
