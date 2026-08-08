<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberSuspendedNotification extends Notification
{
    public function __construct(
        public string $reason,
        public ?\DateTime $until = null
    ) {}

    public function getPreferenceType(): string
    {
        return 'membership_updates';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Club Membership Has Been Suspended')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('Your membership in the Cybersecurity & Innovations Club has been suspended.')
            ->line('**Reason:** '.$this->reason);

        if ($this->until) {
            $message->line('**Suspended until:** '.$this->until->format('F j, Y'));
            $message->line('Your membership will be automatically reinstated after that date.');
        } else {
            $message->line('This suspension is indefinite. Please contact the club administration for more information.');
        }

        return $message
            ->line('If you have any questions, please reach out to the club administration.')
            ->salutation('Best regards, The Cybersecurity & Innovations Club Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_suspended',
            'reason' => $this->reason,
            'suspended_until' => $this->until?->format('Y-m-d'),
            'message' => 'Your membership has been suspended.',
        ];
    }
}
