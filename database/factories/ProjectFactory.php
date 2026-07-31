<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['research', 'development', 'ctf', 'competition', 'community', 'security_audit']),
            'status' => fake()->randomElement(['active', 'completed', 'on_hold']),
            'lead_id' => User::factory(),
        ];
    }
}
