<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRejectionNotification extends Notification
{
    public function __construct(
        public User $rejectedBy,
        public ?string $notes = null
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
        return (new MailMessage)
            ->subject('Update on Your Club Membership Application')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('We regret to inform you that your membership application to the Cybersecurity & Innovations Club has been reviewed and could not be approved at this time.')
            ->when($this->notes, function ($message) {
                $message->line('**Reason:** '.$this->notes);
            })
            ->line('If you believe this decision was made in error or would like to understand more about the decision, please feel free to reach out to the club administration.')
            ->line('You may reapply in the future if circumstances change.')
            ->salutation('Best regards, The Cybersecurity & Innovations Club Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_rejected',
            'rejected_by' => $this->rejectedBy->name,
            'rejected_by_id' => $this->rejectedBy->id,
            'notes' => $this->notes,
            'message' => 'Your membership application was not approved.',
        ];
    }
}
