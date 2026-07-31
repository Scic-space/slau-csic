<?php

namespace Database\Factories;

use App\Models\CtfTeam;
use App\Models\CtfTeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CtfTeamMemberFactory extends Factory
{
    protected $model = CtfTeamMember::class;

    public function definition(): array
    {
        return [
            'ctf_team_id' => CtfTeam::factory(),
            'user_id' => User::factory(),
            'role' => 'member',
        ];
    }

    public function captain(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'captain',
        ]);
    }
}
