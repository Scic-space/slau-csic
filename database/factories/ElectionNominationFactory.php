<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ElectionNominationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'election_id' => \App\Models\Election::factory(),
            'user_id' => \App\Models\User::factory(),
            'statement' => fake()->paragraph(),
            'manifesto' => fake()->paragraphs(3, true),
            'agenda' => fake()->paragraphs(2, true),
            'status' => 'submitted',
            'submitted_at' => now(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => ['status' => 'submitted']);
    }

    public function underReview(): static
    {
        return $this->state(fn () => ['status' => 'under_review']);
    }

    public function shortlisted(): static
    {
        return $this->state(fn () => ['status' => 'shortlisted']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved', 'reviewed_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected', 'reviewed_at' => now()]);
    }
}
