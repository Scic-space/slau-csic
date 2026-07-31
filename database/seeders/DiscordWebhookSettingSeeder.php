<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DiscordWebhookSettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'discord_announcement_webhook'],
            [
                'value' => '',
                'type' => 'string',
                'group' => 'integrations',
                'description' => 'Discord webhook URL for auto-posting announcements',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'discord_invite_url'],
            [
                'value' => '',
                'type' => 'string',
                'group' => 'integrations',
                'description' => 'Discord server invite URL shown in sidebar',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'whatsapp_invite_url'],
            [
                'value' => '',
                'type' => 'string',
                'group' => 'integrations',
                'description' => 'WhatsApp group invite URL shown in sidebar',
            ]
        );
    }
}
