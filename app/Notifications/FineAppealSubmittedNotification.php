<?php

namespace App\Notifications;

use App\Models\FineAppeal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineAppealSubmittedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Fine Appeal Submitted')
            ->greeting("Hello {$notifiable->name},")
            ->line('A fine appeal has been submitted and requires review.')
            ->line("Appeal Reason: {$this->appeal->formatted_appeal_reason}")
            ->line("Explanation: {$this->appeal->explanation}")
            ->line('Fine Amount: UGX '.number_format($this->appeal->fine->amount, 0))
            ->action('Review Appeal', url('/admin/fines'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appeal_submitted',
            'appeal_id' => $this->appeal->id,
            'fine_id' => $this->appeal->fine_id,
            'appeal_reason' => $this->appeal->appeal_reason,
            'message' => 'A fine appeal has been submitted for review.',
        ];
    }
}
