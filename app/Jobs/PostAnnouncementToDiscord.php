<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Services\DiscordWebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PostAnnouncementToDiscord implements ShouldQueue
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function handle(DiscordWebhookService $webhook): void
    {
        $webhook->postAnnouncement($this->announcement);
    }
}
