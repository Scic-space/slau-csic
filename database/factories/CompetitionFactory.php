<?php

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    /** @var list<string> */
    private static array $names = [
        'Regional CTF Qualifiers',
        'Annual Hackathon Challenge',
        'Code Sprint Tournament',
        'Cybersecurity Bootcamp',
        'Algorithm Showdown',
        'Web Security Challenge',
        'Reverse Engineering Cup',
        'Data Science Hackathon',
        'Capture The Flag Open',
        'Cloud Security Contest',
        'Binary Exploitation Finals',
        'Crypto Challenge Series',
        'Network Defense Challenge',
        'AI Security Hackathon',
        'Mobile App Security CTF',
    ];

    /** @var list<string> */
    private static array $descriptions = [
        'A competitive event where participants solve security challenges across web, crypto, and binary exploitation categories.',
        'A fast-paced hackathon focused on building innovative security tools and solutions for real-world problems.',
        'An algorithm and data structure competition testing problem-solving skills under time pressure.',
        'A team-based cybersecurity competition focused on incident response, threat hunting, and network defense.',
        'Participants compete to find and exploit vulnerabilities in deliberately vulnerable applications and systems.',
        'A coding competition where teams build creative solutions to challenging technical problems within a limited timeframe.',
        'A competition focused on reverse engineering malware, analyzing binaries, and understanding system internals.',
        'Teams compete in a capture the flag format with challenges spanning cryptography, forensics, and web security.',
        'A beginner-friendly cybersecurity competition designed to introduce students to security concepts and tools.',
        'An advanced competition featuring real-world inspired scenarios in cloud security, DevSecOps, and infrastructure defense.',
    ];

    /** @var list<string> */
    private static array $achievements = [
        'Finished in the top 10 overall.',
        'Won first place in the web challenges category.',
        'Best newcomer team award.',
        'Qualified for the international finals round.',
        'Achieved perfect score in the cryptography track.',
        'Recognized for most creative solution.',
        'Second place in the team division.',
        'Highest score among university teams.',
        'Completed all challenges within the time limit.',
        'Awarded honorable mention for technical excellence.',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(self::$names),
            'description' => fake()->randomElement(self::$descriptions),
            'type' => fake()->randomElement(['ctf', 'hackathon', 'coding', 'cybersecurity']),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'location' => fake()->city().', '.fake()->country(),
            'website_url' => fake()->url(),
            'is_team_based' => fake()->boolean(),
            'max_team_size' => fn (array $attrs) => $attrs['is_team_based'] ? fake()->numberBetween(2, 5) : null,
            'participation_status' => fake()->randomElement(['registered', 'participating', 'completed']),
            'club_ranking' => fake()->optional(0.3)->numberBetween(1, 50),
            'achievements' => fake()->optional(0.4)->randomElement(self::$achievements),
        ];
    }

    public function ctf(): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => 'ctf',
            'is_team_based' => true,
            'max_team_size' => 5,
        ]);
    }

    public function hackathon(): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => 'hackathon',
            'is_team_based' => true,
            'max_team_size' => 4,
        ]);
    }

    public function coding(): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => 'coding',
            'is_team_based' => false,
            'max_team_size' => null,
        ]);
    }

    public function cybersecurity(): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => 'cybersecurity',
            'is_team_based' => false,
            'max_team_size' => null,
        ]);
    }

    public function participating(): static
    {
        return $this->state(fn (array $attrs) => [
            'participation_status' => 'participating',
            'start_date' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+3 weeks'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => [
            'participation_status' => 'completed',
            'start_date' => fake()->dateTimeBetween('-3 months', '-2 months'),
            'end_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'club_ranking' => fake()->numberBetween(1, 30),
        ]);
    }

    public function ranked(): static
    {
        return $this->state(fn (array $attrs) => [
            'club_ranking' => fake()->numberBetween(1, 10),
            'achievements' => fake()->randomElement(self::$achievements),
        ]);
    }
}
