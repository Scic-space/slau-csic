<?php

namespace App\Notifications;

use App\Models\Fine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineOverdueNotification extends Notification
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
            ->subject('Overdue Fine Reminder: '.($this->fine->fineType?->name ?? 'Club Fine'))
            ->greeting("Hello {$notifiable->name},")
            ->line('This is a reminder that you have an overdue fine.')
            ->line("Reason: {$this->fine->reason}")
            ->line('Amount: UGX '.number_format($this->fine->amount, 0))
            ->line('Balance: UGX '.number_format($this->fine->balance, 0))
            ->line("Due Date was: {$this->fine->due_date->format('M j, Y')}")
            ->action('Pay Your Fine', url('/fines'))
            ->line('Please pay at your earliest convenience to avoid further action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fine_overdue_reminder',
            'fine_id' => $this->fine->id,
            'amount' => $this->fine->amount,
            'balance' => $this->fine->balance,
            'reason' => $this->fine->reason,
            'due_date' => $this->fine->due_date->toDateString(),
            'message' => 'Reminder: You have an overdue fine of UGX '.number_format($this->fine->balance, 0),
        ];
    }
}
