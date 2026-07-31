<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElectionCandidate>
 */
class ElectionCandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'election_id' => \App\Models\Election::factory(),
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->name(),
            'photo' => null,
            'manifesto' => fake()->paragraph(),
            'agenda' => fake()->paragraph(2),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
