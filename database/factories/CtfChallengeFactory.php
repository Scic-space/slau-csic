<?php

namespace Database\Factories;

use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CtfChallengeFactory extends Factory
{
    protected $model = CtfChallenge::class;

    public function definition(): array
    {
        return [
            'ctf_competition_id' => CtfCompetition::factory(),
            'ctf_category_id' => CtfCategory::inRandomOrder()->first() ?? CtfCategory::factory(),
            'title' => fake()->sentence(4),
            'slug' => Str::slug(fake()->unique()->sentence(3)),
            'description' => fake()->paragraph(),
            'flag_hash' => hash('sha256', 'SLAU_CSIC{'.fake()->uuid.'}'),
            'flag_case_sensitive' => true,
            'points' => fake()->randomElement([50, 100, 150, 200, 300, 500]),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard', 'insane']),
            'is_active' => true,
            'max_attempts' => 0,
            'tags' => fake()->optional()->randomElements(['web', 'sql', 'xss', 'crypto', 'reverse'], fake()->numberBetween(1, 3)),
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withFlag(string $flag): static
    {
        return $this->state(fn (array $attributes) => [
            'flag_hash' => hash('sha256', $flag),
        ]);
    }

    public function limitedAttempts(int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'max_attempts' => $max,
        ]);
    }
}
