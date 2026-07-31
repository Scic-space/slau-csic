<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Competition;
use App\Models\CompetitionParticipants;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $ctf = Competition::factory()->ctf()->participating()->create([
            'name' => 'SLAU CTF 2025',
            'description' => 'Annual SLAU Capture The Flag competition featuring web exploitation, reverse engineering, and cryptography challenges.',
            'location' => 'Virtual',
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(10),
        ]);

        $hackathon = Competition::factory()->hackathon()->create([
            'participation_status' => 'registered',
            'name' => 'CodeFest Hackathon 2025',
            'description' => 'A 48-hour hackathon focused on building innovative cybersecurity solutions.',
            'location' => 'SLAU Innovation Hub',
            'start_date' => now()->addDays(14),
            'end_date' => now()->addDays(16),
        ]);

        $coding = Competition::factory()->coding()->completed()->ranked()->create([
            'name' => 'AlgoCup 2025',
            'description' => 'Algorithmic coding competition testing problem-solving and data structure skills.',
            'location' => 'SLAU Computer Lab',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonths(2)->addDays(1),
            'club_ranking' => 5,
            'achievements' => 'Top 10 finish in regional qualifiers.',
        ]);

        $cyber = Competition::factory()->cybersecurity()->completed()->create([
            'name' => 'Cyber Defense Challenge',
            'description' => 'Blue team defense competition focused on incident response and network security.',
            'location' => 'Nairobi Cyber Hub',
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subMonths(3)->addDays(2),
        ]);

        Competition::factory(6)->create();

        $this->addParticipants($ctf, 12);
        $this->addParticipants($hackathon, 8);
        $this->addParticipants($coding, 10);
        $this->addParticipants($cyber, 6);

        $this->addChallenges($ctf, 5);
    }

    private function addChallenges(Competition $competition, int $count): void
    {
        Challenge::factory($count)->create([
            'competition_id' => $competition->id,
        ]);
    }

    private function addParticipants(Competition $competition, int $count): void
    {
        $users = User::inRandomOrder()->limit($count)->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            CompetitionParticipants::create([
                'competition_id' => $competition->id,
                'user_id' => $user->id,
                'team_name' => $competition->is_team_based ? fake()->company() : null,
                'role' => fake()->randomElement(['leader', 'member']),
            ]);
        }
    }
}
