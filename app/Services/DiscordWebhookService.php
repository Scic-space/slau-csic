<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    public function postAnnouncement(Announcement $announcement): bool
    {
        $webhookUrl = Setting::getValue('discord_announcement_webhook');

        if (! $webhookUrl) {
            return false;
        }

        $embed = [
            'title' => $announcement->title,
            'description' => strip_tags($announcement->content),
            'url' => url('/announcements/'.$announcement->slug),
            'color' => match ($announcement->type) {
                'urgent' => 15548997,
                'event' => 5763719,
                'meeting' => 3447003,
                'achievement' => 15105570,
                default => 9807270,
            },
            'author' => [
                'name' => $announcement->author?->name ?? 'SLAU CSIC',
            ],
            'footer' => [
                'text' => 'SLAU Cybersecurity & Innovations Club',
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'embeds' => [$embed],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Discord webhook failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Discord webhook error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
