<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => Str::slug(fake()->word()),
            'description' => fake()->sentence(),
            'criteria_type' => 'total_points',
            'criteria_value' => 100,
            'points_bonus' => 0,
            'icon' => '🏆',
        ];
    }
}
