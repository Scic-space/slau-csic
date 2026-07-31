<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentGeneratedNotification extends Notification
{
    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Assignments Generated: {$this->assignment->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("AI-powered assignments have been generated for **{$this->assignment->name}**.")
            ->line("Confidence: {$this->assignment->confidence_score}%")
            ->line("Fairness: {$this->assignment->fairness_score}%")
            ->action('Review Assignments', url("/admin/assignments/{$this->assignment->id}/edit"))
            ->line('Please review and approve the recommendations.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'assignment_name' => $this->assignment->name,
            'confidence_score' => $this->assignment->confidence_score,
            'fairness_score' => $this->assignment->fairness_score,
            'message' => "Assignments generated for {$this->assignment->name} — review and approve.",
        ];
    }
}
