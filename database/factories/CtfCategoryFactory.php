<?php

namespace Database\Factories;

use App\Models\CtfCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CtfCategoryFactory extends Factory
{
    protected $model = CtfCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'slug' => Str::slug(fake()->unique()->word()),
            'color' => fake()->hexColor(),
            'icon' => '🏴',
            'sort_order' => 0,
        ];
    }
}
