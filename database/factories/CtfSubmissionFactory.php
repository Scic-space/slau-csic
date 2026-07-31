<?php

namespace Database\Factories;

use App\Models\CtfChallenge;
use App\Models\CtfSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CtfSubmissionFactory extends Factory
{
    protected $model = CtfSubmission::class;

    public function definition(): array
    {
        return [
            'ctf_challenge_id' => CtfChallenge::factory(),
            'user_id' => User::factory(),
            'submitted_flag' => 'SLAU_CSIC{test_flag}',
            'is_correct' => true,
            'points_awarded' => 100,
            'attempt_number' => 1,
            'ip_address' => fake()->ipv4(),
            'submitted_at' => now(),
        ];
    }

    public function incorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
            'points_awarded' => 0,
        ]);
    }
}
