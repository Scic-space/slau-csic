<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class EmailVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your SLAU-CSIC Email Verification Code')
            ->markdown('emails.verification-code', [
                'name' => Str::title($notifiable->name),
                'code' => $this->code,
            ]);
    }
}
