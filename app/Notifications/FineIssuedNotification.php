<?php

namespace App\Notifications;

use App\Models\Fine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(public Fine $fine) {}

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
            ->subject('Fine Issued: '.($this->fine->fineType?->name ?? 'Club Fine'))
            ->greeting("Hello {$notifiable->name},")
            ->line('A fine has been issued against you.')
            ->line("Reason: {$this->fine->reason}")
            ->line('Amount: UGX '.number_format($this->fine->amount, 0))
            ->line("Due Date: {$this->fine->due_date->format('M j, Y')}")
            ->action('View Your Fines', url('/fines'))
            ->line('You may appeal this fine within 7 days of issuance.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fine_issued',
            'fine_id' => $this->fine->id,
            'amount' => $this->fine->amount,
            'reason' => $this->fine->reason,
            'due_date' => $this->fine->due_date->toDateString(),
            'message' => 'A fine of UGX '.number_format($this->fine->amount, 0)." has been issued: {$this->fine->reason}",
        ];
    }
}
