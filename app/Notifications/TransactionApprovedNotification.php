<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionApprovedNotification extends Notification
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
            ->subject('Transaction Approved: UGX '.number_format($this->transaction->amount, 0))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your transaction has been approved.')
            ->line('Type: '.ucfirst($this->transaction->type))
            ->line("Category: {$this->transaction->category}")
            ->line('Amount: UGX '.number_format($this->transaction->amount, 0))
            ->line("Approved by: {$this->transaction->approver?->name}")
            ->action('View Transactions', url('/admin/treasurer-dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transaction_approved',
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'category' => $this->transaction->category,
            'approved_by' => $this->transaction->approver?->name,
            'message' => 'Transaction of UGX '.number_format($this->transaction->amount, 0)." approved ({$this->transaction->category})",
        ];
    }
}
