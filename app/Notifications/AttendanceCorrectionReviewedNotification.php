<?php

namespace App\Notifications;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceCorrectionReviewedNotification extends Notification
{
    public function __construct(
        public AttendanceCorrectionRequest $request,
        public bool $approved,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->approved
                ? "Attendance confirmed: {$this->request->meeting->title}"
                : "Attendance correction declined: {$this->request->meeting->title}")
            ->greeting("Hello {$notifiable->name}!");

        if ($this->approved) {
            return $message
                ->line("Good news — your attendance at **{$this->request->meeting->title}** has been confirmed and recorded.")
                ->action('View Class', route('classes.show', $this->request->meeting));
        }

        return $message
            ->line("After review, your attendance correction request for **{$this->request->meeting->title}** was not approved.")
            ->line('If you believe this is a mistake, please speak to the class instructor.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'correction_request_id' => $this->request->id,
            'meeting_id' => $this->request->meeting_id,
            'meeting_title' => $this->request->meeting->title,
            'approved' => $this->approved,
            'message' => $this->approved
                ? "Your attendance at '{$this->request->meeting->title}' was confirmed."
                : "Your attendance correction for '{$this->request->meeting->title}' was declined.",
        ];
    }
}
