<?php

namespace Database\Factories;

use App\Models\CtfCompetition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CtfCompetitionFactory extends Factory
{
    protected $model = CtfCompetition::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(6),
            'status' => 'published',
            'is_public' => true,
            'max_score' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(14),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(14),
            'end_date' => now()->subDays(7),
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
