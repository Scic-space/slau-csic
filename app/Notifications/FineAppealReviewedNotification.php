<?php

namespace App\Notifications;

use App\Models\FineAppeal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineAppealReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public FineAppeal $appeal) {}

    public function getPreferenceType(): string
    {
        return 'fine_notifications';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $outcome = ucfirst($this->appeal->status);

        return (new MailMessage)
            ->subject("Appeal {$outcome}: Fine #{$this->appeal->fine_id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your fine appeal has been {$this->appeal->status}.")
            ->when($this->appeal->decision_notes, fn ($mail) => $mail->line("Reviewer notes: {$this->appeal->decision_notes}"))
            ->when($this->appeal->status === 'approved', fn ($mail) => $mail->line('Your fine has been waived.'))
            ->when($this->appeal->status === 'rejected', fn ($mail) => $mail->line('Your fine remains due.'))
            ->action('View Your Fines', url('/fines'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appeal_reviewed',
            'appeal_id' => $this->appeal->id,
            'fine_id' => $this->appeal->fine_id,
            'status' => $this->appeal->status,
            'decision_notes' => $this->appeal->decision_notes,
            'message' => "Your fine appeal has been {$this->appeal->status}.",
        ];
    }
}
