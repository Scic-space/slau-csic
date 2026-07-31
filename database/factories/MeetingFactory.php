<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Weekly General Meeting',
                'Executive Board Meeting',
                'Project Sync',
                'Budget Planning Session',
                'Event Planning Meeting',
                'Member Orientation',
                'CTF Strategy Meeting',
                'Workshop Preparation',
            ]),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(['general', 'executive', 'special', 'training', 'workshop']),
            'scheduled_at' => fake()->dateTimeBetween('-1 month', '+2 months'),
            'location' => fake()->randomElement(['Room 301', 'Conference Room A', 'IT Lab', 'Online', 'Auditorium']),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 60, 90, 120]),
            'expected_attendees' => fake()->numberBetween(10, 50),
            'late_threshold_minutes' => 15,
            'meeting_link' => fake()->optional(0.3)->url(),
            'created_by' => User::factory(),
            'attendance_open' => false,
            'agenda' => fake()->optional()->paragraph(),
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => fake()->dateTimeBetween('+1 hour', '+2 months'),
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => fake()->dateTimeBetween('-2 months', '-1 hour'),
            'started_at' => fake()->dateTimeBetween('-2 months', '-1 hour'),
            'ended_at' => fn (array $attrs) => Carbon::parse($attrs['started_at'] ?? now()->subDay())->addMinutes($attrs['duration_minutes'] ?? 60),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subMinutes(30),
            'attendance_open' => true,
            'started_at' => now()->subMinutes(30),
        ]);
    }

    public function teachingSession(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'teaching_session',
        ]);
    }

    public function withAttendance(int $count = 10): static
    {
        return $this->afterCreating(function (Meeting $meeting) use ($count) {
            $users = User::factory($count)->create();

            foreach ($users as $user) {
                $meeting->recordAttendance($user, 'qr_code', [
                    'status' => fake()->randomElement(['present', 'present', 'present', 'late']),
                ]);
            }
        });
    }
}
