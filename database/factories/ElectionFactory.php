<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Election>
 */
class ElectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->slug(4),
            'position' => fake()->randomElement(['President', 'Vice President', 'Secretary', 'Treasurer', 'Head of Projects']),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'open', 'closed']),
            'starts_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'results_visible' => fake()->boolean(20),
            'allow_vote_changes' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'starts_at' => null, 'ends_at' => null]);
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => 'open',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => 'closed',
            'starts_at' => now()->subDays(14),
            'ends_at' => now()->subDays(7),
        ]);
    }
}
