<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(['general', 'event', 'meeting', 'urgent', 'achievement']),
            'audience' => 'all',
            'target_roles' => null,
            'is_published' => true,
            'send_email' => false,
            'send_push' => false,
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'expires_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn () => [
            'type' => 'urgent',
        ]);
    }
}
