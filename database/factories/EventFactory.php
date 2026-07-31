<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $endDate = (clone $startDate)->modify('+2 hours');

        return [
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['workshop', 'competition', 'social', 'talk', 'hackathon', 'bootcamp']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => $this->faker->address(),
            'banner_image' => 'events/'.$this->faker->uuid().'.jpg',
            'gallery' => [],
            'max_participants' => $this->faker->numberBetween(10, 100),
            'registration_required' => true,
            'is_public' => $this->faker->boolean(70),
            'registration_deadline' => (clone $startDate)->modify('-1 day'),
            'status' => 'scheduled',
            'organizer_id' => \App\Models\User::factory(),
            'requirements' => $this->faker->sentence(),
            'registration_fee' => 0,
            'external_link' => $this->faker->optional()->url(),
        ];
    }
}
