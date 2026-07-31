<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\BadgeCriteriaType;
use App\Models\BadgeRarity;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // ============================================
            // EVENTS ATTENDED CATEGORY
            // ============================================
            [
                'name' => 'First Contact',
                'description' => 'Attended your first club event — welcome aboard!',
                'icon' => '🎯',
                'criteria_type' => BadgeCriteriaType::EventsAttended,
                'criteria_value' => 1,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 10,
            ],
            [
                'name' => 'Active Member',
                'description' => 'Attended 5 club events — you are showing up and getting involved.',
                'icon' => '🎪',
                'criteria_type' => BadgeCriteriaType::EventsAttended,
                'criteria_value' => 5,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 25,
            ],
            [
                'name' => 'Club Enthusiast',
                'description' => 'Attended 15 events — a true regular at SLAU CSIC gatherings.',
                'icon' => '🏆',
                'criteria_type' => BadgeCriteriaType::EventsAttended,
                'criteria_value' => 15,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 50,
            ],
            [
                'name' => 'Event Veteran',
                'description' => 'Attended 30 events — a pillar of the club community.',
                'icon' => '👑',
                'criteria_type' => BadgeCriteriaType::EventsAttended,
                'criteria_value' => 30,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 100,
            ],

            // ============================================
            // RESOURCE TRACKS COMPLETED CATEGORY
            // ============================================
            [
                'name' => 'Trailblazer',
                'description' => 'Completed your first club resource track — the journey begins.',
                'icon' => '🥇',
                'criteria_type' => BadgeCriteriaType::CtfCompleted,
                'criteria_value' => 1,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 15,
            ],
            [
                'name' => 'Pathfinder',
                'description' => 'Completed 5 resource tracks — making serious progress.',
                'icon' => '🗺️',
                'criteria_type' => BadgeCriteriaType::CtfCompleted,
                'criteria_value' => 5,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 40,
            ],
            [
                'name' => 'Trail Master',
                'description' => 'Completed 15 resource tracks — unmatched dedication to learning.',
                'icon' => '🧭',
                'criteria_type' => BadgeCriteriaType::CtfCompleted,
                'criteria_value' => 15,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 100,
            ],

            // ============================================
            // TOTAL POINTS CATEGORY
            // ============================================
            [
                'name' => 'Rising Star',
                'description' => 'Earned 100 total points — you are on your way up!',
                'icon' => '⭐',
                'criteria_type' => BadgeCriteriaType::TotalPoints,
                'criteria_value' => 100,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 20,
            ],
            [
                'name' => 'Point Collector',
                'description' => 'Earned 500 total points — building an impressive portfolio.',
                'icon' => '💰',
                'criteria_type' => BadgeCriteriaType::TotalPoints,
                'criteria_value' => 500,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 50,
            ],
            [
                'name' => 'High Achiever',
                'description' => 'Earned 1,000 total points — a top-tier club member.',
                'icon' => '🏅',
                'criteria_type' => BadgeCriteriaType::TotalPoints,
                'criteria_value' => 1000,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 150,
            ],
            [
                'name' => 'Club Legend',
                'description' => 'Earned 5,000 total points — an all-time great!',
                'icon' => '👑',
                'criteria_type' => BadgeCriteriaType::TotalPoints,
                'criteria_value' => 5000,
                'rarity' => BadgeRarity::Legendary,
                'points_bonus' => 500,
            ],

            // ============================================
            // TEACHING SESSIONS CATEGORY
            // ============================================
            [
                'name' => 'Curious Mind',
                'description' => 'Attended your first teaching session — knowledge seeker.',
                'icon' => '🎓',
                'criteria_type' => BadgeCriteriaType::TeachingSessions,
                'criteria_value' => 1,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 10,
            ],
            [
                'name' => 'Diligent Student',
                'description' => 'Attended 10 teaching sessions — committed to learning.',
                'icon' => '📚',
                'criteria_type' => BadgeCriteriaType::TeachingSessions,
                'criteria_value' => 10,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 50,
            ],
            [
                'name' => 'Knowledge Seeker',
                'description' => 'Attended 25 teaching sessions — a true scholar of cybersecurity.',
                'icon' => '📖',
                'criteria_type' => BadgeCriteriaType::TeachingSessions,
                'criteria_value' => 25,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 150,
            ],

            // ============================================
            // STREAK DAYS CATEGORY
            // ============================================
            [
                'name' => 'Consistent',
                'description' => 'Maintained a 3-day attendance streak — building momentum.',
                'icon' => '🔥',
                'criteria_type' => BadgeCriteriaType::StreakDays,
                'criteria_value' => 3,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 10,
            ],
            [
                'name' => 'Committed',
                'description' => 'Maintained a 10-day attendance streak — discipline in action.',
                'icon' => '⚡',
                'criteria_type' => BadgeCriteriaType::StreakDays,
                'criteria_value' => 10,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 50,
            ],
            [
                'name' => 'Unstoppable',
                'description' => 'Maintained a 30-day attendance streak — relentless dedication.',
                'icon' => '💪',
                'criteria_type' => BadgeCriteriaType::StreakDays,
                'criteria_value' => 30,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 150,
            ],
            [
                'name' => 'Iron Will',
                'description' => 'Maintained a 100-day attendance streak — absolutely legendary!',
                'icon' => '🛡️',
                'criteria_type' => BadgeCriteriaType::StreakDays,
                'criteria_value' => 100,
                'rarity' => BadgeRarity::Legendary,
                'points_bonus' => 500,
            ],

            // ============================================
            // CTF SCORE CATEGORY
            // ============================================
            [
                'name' => 'Puzzle Solver',
                'description' => 'Scored 100 points in CTF challenges — you are getting the hang of it.',
                'icon' => '🧩',
                'criteria_type' => BadgeCriteriaType::CtfScore,
                'criteria_value' => 100,
                'rarity' => BadgeRarity::Common,
                'points_bonus' => 10,
            ],
            [
                'name' => 'Flag Finder',
                'description' => 'Scored 500 points in CTF challenges — skilled and persistent.',
                'icon' => '🔍',
                'criteria_type' => BadgeCriteriaType::CtfScore,
                'criteria_value' => 500,
                'rarity' => BadgeRarity::Rare,
                'points_bonus' => 50,
            ],
            [
                'name' => 'Binary Breaker',
                'description' => 'Scored 2,000 points in CTF challenges — formidable competitor.',
                'icon' => '🕵️',
                'criteria_type' => BadgeCriteriaType::CtfScore,
                'criteria_value' => 2000,
                'rarity' => BadgeRarity::Epic,
                'points_bonus' => 200,
            ],
            [
                'name' => 'Ninja Hacker',
                'description' => 'Scored 10,000 points in CTF challenges — elite-level hacker!',
                'icon' => '🥷',
                'criteria_type' => BadgeCriteriaType::CtfScore,
                'criteria_value' => 10000,
                'rarity' => BadgeRarity::Legendary,
                'points_bonus' => 1000,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($badge['name'])],
                $badge,
            );
        }
    }
}
