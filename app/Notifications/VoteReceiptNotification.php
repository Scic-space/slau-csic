<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VoteReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Election $election,
        public string $receiptCode,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Vote Receipt - {$this->election->title}")
            ->greeting('Your vote has been recorded!')
            ->line("You successfully cast your ballot for the **{$this->election->title}** election ({$this->election->position}).")
            ->line("Your receipt code: **{$this->receiptCode}**")
            ->line('Save this code to verify your vote was counted.')
            ->action('Verify Your Vote', route('voting.verify.form'))
            ->line('This receipt proves your vote was recorded. Do not share it with others.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vote_receipt',
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'receipt_code' => $this->receiptCode,
            'message' => "Your vote receipt for {$this->election->title}: {$this->receiptCode}",
        ];
    }
}
