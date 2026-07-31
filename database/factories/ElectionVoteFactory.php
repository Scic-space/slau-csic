<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElectionVote>
 */
class ElectionVoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $candidate = \App\Models\ElectionCandidate::factory()->create();

        return [
            'election_id' => $candidate->election_id,
            'election_candidate_id' => $candidate->id,
            'user_id' => \App\Models\User::factory(),
            'receipt_code' => \App\Models\ElectionVote::generateReceiptCode(),
        ];
    }

    public function withoutReceipt(): static
    {
        return $this->state(fn () => ['receipt_code' => null]);
    }
}
