<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionApprovalRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Transaction Approval Required: UGX '.number_format($this->transaction->amount, 0))
            ->greeting("Hello {$notifiable->name},")
            ->line('A transaction requires your approval.')
            ->line('Type: '.ucfirst($this->transaction->type))
            ->line("Category: {$this->transaction->category}")
            ->line('Amount: UGX '.number_format($this->transaction->amount, 0))
            ->line("Description: {$this->transaction->description}")
            ->line("Submitted by: {$this->transaction->creator?->name}")
            ->action('Review Transaction', url('/admin/treasurer-dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_requested',
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'category' => $this->transaction->category,
            'description' => $this->transaction->description,
            'message' => 'Approval needed: UGX '.number_format($this->transaction->amount, 0)." {$this->transaction->type} - {$this->transaction->category}",
        ];
    }
}
