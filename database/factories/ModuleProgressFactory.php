<?php

namespace Database\Factories;

use App\Models\ModuleProgress;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleProgressFactory extends Factory
{
    protected $model = ModuleProgress::class;

    public function definition(): array
    {
        return [
            'training_module_id' => TrainingModule::factory(),
            'user_id' => User::factory(),
            'completed' => false,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}
