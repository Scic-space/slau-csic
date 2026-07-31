<?php

namespace Database\Factories;

use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        $title = fake()->unique()->randomElement([
            'Cybersecurity Fundamentals',
            'Network Security Basics',
            'Ethical Hacking Workshop',
            'Web Application Security',
            'Cryptography Principles',
            'Incident Response Training',
            'Cloud Security Overview',
            'Digital Forensics Introduction',
            'Advanced Penetration Testing',
            'Malware Analysis Basics',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['ethical_hacking', 'digital_forensics', 'network_security', 'web_security', 'mobile_security', 'ctf', 'programming', 'other']),
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'objectives' => fake()->optional()->paragraph(),
            'prerequisites' => fake()->optional()->sentence(),
            'duration_hours' => fake()->randomElement([4, 8, 12, 16, 24]),
            'max_enrollments' => fake()->optional()->numberBetween(20, 100),
            'is_published' => true,
            'instructor_id' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
