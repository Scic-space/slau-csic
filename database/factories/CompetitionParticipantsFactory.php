<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionParticipants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionParticipantsFactory extends Factory
{
    protected $model = CompetitionParticipants::class;

    public function definition(): array
    {
        $competition = Competition::inRandomOrder()->first() ?? Competition::factory()->create();

        return [
            'competition_id' => $competition->id,
            'user_id' => User::factory(),
            'team_name' => $competition->is_team_based ? fake()->company() : null,
            'role' => fake()->randomElement(['leader', 'member']),
        ];
    }

    public function leader(): static
    {
        return $this->state(fn (array $attrs) => [
            'role' => 'leader',
        ]);
    }
}
