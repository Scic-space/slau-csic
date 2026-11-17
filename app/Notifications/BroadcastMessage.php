<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BroadcastMessage extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        public string $subject,
        public string $body,
        public array $channels = ['mail', 'database'],
        public ?Announcement $announcement = null,
        public ?string $actionUrl = null,
    ) {}

    public function getPreferenceType(): string
    {
        return 'broadcast_messages';
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello '.($notifiable->name ?? 'there').'!')
            ->line($this->announcement
                ? 'A new announcement has been published.'
                : 'You have a new message from '.config('app.name').'.')
            ->line('**'.($this->announcement?->title ?? $this->subject).'**');

        if ($this->announcement?->type) {
            $message->line('Type: '.ucfirst(str_replace('_', ' ', $this->announcement->type)));
        }

        foreach ($this->bodyLines() as $line) {
            $message->line($line);
        }

        $actionUrl = $this->resolveActionUrl();

        if ($actionUrl) {
            $message->action('View Announcement', $actionUrl);
        }

        return $message->line('You can review all announcements in the club portal.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'body' => $this->body,
            'message' => $this->subject,
            'action_url' => $this->resolveActionUrl(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function bodyLines(): array
    {
        $lines = preg_split('/\R+/u', $this->body) ?: [];

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function resolveActionUrl(): ?string
    {
        if ($this->actionUrl) {
            return $this->actionUrl;
        }

        if ($this->announcement) {
            return route('announcements.show', $this->announcement->slug);
        }

        return null;
    }
}
