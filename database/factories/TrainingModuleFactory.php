<?php

namespace Database\Factories;

use App\Models\TrainingModule;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingModuleFactory extends Factory
{
    protected $model = TrainingModule::class;

    public function definition(): array
    {
        return [
            'training_id' => \App\Models\Training::factory(),
            'title' => fake()->randomElement([
                'Introduction to Concepts',
                'Hands-on Lab Exercise',
                'Advanced Techniques',
                'Final Assessment',
                'Review and Summary',
                'Practical Application',
            ]),
            'content' => fake()->paragraphs(3, true),
            'order' => fake()->numberBetween(1, 10),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
            'resources' => null,
        ];
    }
}
