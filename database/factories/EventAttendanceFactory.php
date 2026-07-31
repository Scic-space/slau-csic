<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventAttendanceFactory extends Factory
{
    protected $model = \App\Models\EventAttendance::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'member_id' => User::factory(),
            'status' => 'present',
            'checked_in_at' => now(),
            'recorded_at' => now(),
        ];
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'checked_in_at' => null,
        ]);
    }

    public function excused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'excused',
            'checked_in_at' => null,
        ]);
    }
}
