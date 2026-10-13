<?php

namespace App\Notifications;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceCorrectionRequestedNotification extends Notification
{
    public function __construct(public AttendanceCorrectionRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Attendance correction requested: {$this->request->meeting->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("**{$this->request->user->name}** says they attended **{$this->request->meeting->title}** but are not marked present.")
            ->when($this->request->message, fn ($message) => $message->line("Member's note: {$this->request->message}"))
            ->action('Review Request', url('/admin/attendance-corrections'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'correction_request_id' => $this->request->id,
            'meeting_id' => $this->request->meeting_id,
            'meeting_title' => $this->request->meeting->title,
            'user_id' => $this->request->user_id,
            'user_name' => $this->request->user->name,
            'message' => "{$this->request->user->name} requested an attendance correction for '{$this->request->meeting->title}'.",
        ];
    }
}
