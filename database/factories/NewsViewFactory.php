<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsView>
 */
class NewsViewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_id' => News::factory(),
            'viewer_key' => fake()->sha256(),
            'viewed_at' => now(),
        ];
    }
}
