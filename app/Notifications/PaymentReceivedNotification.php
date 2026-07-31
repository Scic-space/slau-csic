<?php

namespace App\Notifications;

use App\Models\FinePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public FinePayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received: UGX '.number_format($this->payment->amount, 0))
            ->greeting("Hello {$notifiable->name},")
            ->line('A payment has been received toward your fine.')
            ->line('Amount: UGX '.number_format($this->payment->amount, 0))
            ->line('Payment Method: '.ucfirst($this->payment->payment_method))
            ->when($this->payment->receipt_number, fn ($mail) => $mail->line("Receipt: {$this->payment->receipt_number}"))
            ->line('Remaining Balance: UGX '.number_format($this->payment->fine->balance, 0))
            ->action('View Your Fines', url('/fines'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'payment_id' => $this->payment->id,
            'fine_id' => $this->payment->fine_id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'receipt_number' => $this->payment->receipt_number,
            'balance' => $this->payment->fine->balance,
            'message' => 'Payment of UGX '.number_format($this->payment->amount, 0).' received. Remaining balance: UGX '.number_format($this->payment->fine->balance, 0),
        ];
    }
}
