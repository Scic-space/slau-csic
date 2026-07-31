<?php

namespace Database\Factories;

use App\Models\CtfChallenge;
use App\Models\CtfWriteup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CtfWriteupFactory extends Factory
{
    protected $model = CtfWriteup::class;

    public function definition(): array
    {
        return [
            'ctf_challenge_id' => CtfChallenge::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraphs(3, true),
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
