<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Workshop', 'slug' => 'workshop', 'description' => 'Hands-on learning sessions', 'color' => '#22c55e', 'icon' => 'heroicon-o-academic-cap', 'sort_order' => 1],
            ['name' => 'Competition', 'slug' => 'competition', 'description' => 'Competitive events and challenges', 'color' => '#ef4444', 'icon' => 'heroicon-o-trophy', 'sort_order' => 2],
            ['name' => 'CTF', 'slug' => 'ctf', 'description' => 'Capture The Flag cybersecurity competitions', 'color' => '#dc2626', 'icon' => 'heroicon-o-shield-check', 'sort_order' => 3],
            ['name' => 'Bootcamp', 'slug' => 'bootcamp', 'description' => 'Intensive training programs', 'color' => '#a855f7', 'icon' => 'heroicon-o-beaker', 'sort_order' => 4],
            ['name' => 'Awareness Campaign', 'slug' => 'awareness-campaign', 'description' => 'Cybersecurity awareness initiatives', 'color' => '#eab308', 'icon' => 'heroicon-o-megaphone', 'sort_order' => 5],
            ['name' => 'Talk / Seminar', 'slug' => 'talk-seminar', 'description' => 'Presentations and guest lectures', 'color' => '#3b82f6', 'icon' => 'heroicon-o-chat-bubble-left-right', 'sort_order' => 6],
            ['name' => 'Social', 'slug' => 'social', 'description' => 'Social gatherings and networking', 'color' => '#6366f1', 'icon' => 'heroicon-o-user-group', 'sort_order' => 7],
            ['name' => 'Hackathon', 'slug' => 'hackathon', 'description' => 'Collaborative hacking and building events', 'color' => '#f97316', 'icon' => 'heroicon-o-code-bracket', 'sort_order' => 8],
            ['name' => 'Training', 'slug' => 'training', 'description' => 'Structured educational sessions', 'color' => '#14b8a6', 'icon' => 'heroicon-o-book-open', 'sort_order' => 9],
            ['name' => 'Meeting', 'slug' => 'meeting', 'description' => 'Club meetings and general assemblies', 'color' => '#6b7280', 'icon' => 'heroicon-o-calendar-days', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            EventCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Event categories seeded successfully!');
    }
}
