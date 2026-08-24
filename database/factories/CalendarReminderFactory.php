<?php

namespace Database\Factories;

use App\Models\CalendarReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarReminder>
 */
class CalendarReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'color' => fake()->randomElement(['danger', 'success', 'primary', 'warning']),
            'starts_on' => fake()->dateTimeBetween('now', '+1 month'),
            'ends_on' => fn (array $attributes) => $attributes['starts_on'],
        ];
    }
}
