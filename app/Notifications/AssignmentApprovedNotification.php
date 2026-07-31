<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentApprovedNotification extends Notification
{
    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Assignment Approved: {$this->assignment->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("The assignment **{$this->assignment->name}** has been approved.")
            ->line('All suggested members have been assigned to their roles.')
            ->action('View Assignment', url("/admin/assignments/{$this->assignment->id}/edit"))
            ->line('Thank you for using the assignment system.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'assignment_name' => $this->assignment->name,
            'message' => "{$this->assignment->name} has been approved.",
        ];
    }
}
