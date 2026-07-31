<?php

namespace Database\Factories;

use App\Models\CtfCompetition;
use App\Models\CtfTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CtfTeamFactory extends Factory
{
    protected $model = CtfTeam::class;

    public function definition(): array
    {
        return [
            'ctf_competition_id' => CtfCompetition::factory(),
            'name' => fake()->unique()->words(2, true),
            'slug' => Str::slug(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(),
            'invite_code' => Str::random(16),
            'captain_id' => User::factory(),
            'is_open' => true,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => false,
        ]);
    }
}
