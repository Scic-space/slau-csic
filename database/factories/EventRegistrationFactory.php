<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'status' => 'registered',
            'rsvp_status' => 'attending',
            'registered_at' => now(),
        ];
    }

    public function waitlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waitlist',
            'waitlisted_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'attended_at' => now(),
        ]);
    }
}
