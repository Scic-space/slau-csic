<?php

namespace Database\Seeders;

use App\Models\Election;
use App\Models\ElectionNomination;
use App\Models\User;
use Illuminate\Database\Seeder;

class ElectionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::activeMembers()->get();

        // Open election with candidates
        $openElection = Election::factory()->open()->create([
            'title' => '2026 Cabinet Elections',
            'slug' => '2026-cabinet-elections',
            'position' => 'President',
            'description' => 'Cast your vote for the next SLAU CSIC President. Review each candidate\'s manifesto and agenda before casting your ballot.',
            'applications_starts_at' => now()->subDays(14),
            'applications_ends_at' => now()->subDays(1),
        ]);

        $openElection->candidates()->createMany([
            ['name' => 'Alice Kamau', 'user_id' => $users->random()->id ?? null, 'manifesto' => 'Bringing innovation through collaboration and hands-on cybersecurity workshops.', 'agenda' => 'Monthly workshops, industry partnerships, CTF bootcamps', 'sort_order' => 0],
            ['name' => 'Bob Ochieng', 'user_id' => $users->random()->id ?? null, 'manifesto' => 'Building a stronger community focused on academic excellence and research.', 'agenda' => 'Research papers, hackathons, mentorship program', 'sort_order' => 1],
            ['name' => 'Carol Wanjiku', 'user_id' => $users->random()->id ?? null, 'manifesto' => 'Expanding the club\'s reach through outreach programs and inter-university collaboration.', 'agenda' => 'Outreach programs, inter-university events, industry talks', 'sort_order' => 2],
        ]);

        // Sample applications for the open election
        $sampleApplicants = $users->count() >= 5 ? $users->random(3) : User::factory(3)->create(['membership_status' => 'active', 'membership_type' => 'active']);
        $statuses = ['submitted', 'under_review', 'shortlisted', 'approved', 'rejected'];
        foreach ($sampleApplicants as $i => $applicant) {
            ElectionNomination::create([
                'election_id' => $openElection->id,
                'user_id' => $applicant->id,
                'statement' => fake()->paragraph(),
                'manifesto' => fake()->paragraphs(3, true),
                'agenda' => fake()->paragraphs(2, true),
                'status' => $statuses[$i] ?? 'submitted',
                'submitted_at' => now()->subDays(rand(1, 10)),
                'reviewed_at' => in_array($statuses[$i] ?? 'submitted', ['approved', 'rejected']) ? now()->subHours(rand(1, 48)) : null,
            ]);
        }

        // Closed election with results
        $closedElection = Election::factory()->closed()->create([
            'title' => '2025 Cabinet Elections',
            'slug' => '2025-cabinet-elections',
            'position' => 'President',
            'description' => 'The previous cabinet election. Results are displayed below.',
            'results_visible' => true,
        ]);

        $closedCandidates = collect([
            ['name' => 'Daniel Mwangi', 'sort_order' => 0],
            ['name' => 'Esther Nyambura', 'sort_order' => 1],
            ['name' => 'Francis Kiprop', 'sort_order' => 2],
        ]);

        $closedCandidates->each(fn ($data) => $closedElection->candidates()->create($data));

        // Draft election (upcoming)
        $draftElection = Election::factory()->draft()->create([
            'title' => 'Upcoming Vice President Election',
            'slug' => 'upcoming-vice-president-election',
            'position' => 'Vice President',
            'description' => 'Nominations and campaigning are currently in progress.',
            'applications_starts_at' => now()->subDays(7),
            'applications_ends_at' => now()->addDays(7),
        ]);

        // Sample applications for draft election
        if ($users->count() >= 3) {
            $draftApplicants = $users->random(2);
            foreach ($draftApplicants as $i => $applicant) {
                ElectionNomination::create([
                    'election_id' => $draftElection->id,
                    'user_id' => $applicant->id,
                    'statement' => fake()->paragraph(),
                    'manifesto' => fake()->paragraphs(3, true),
                    'agenda' => fake()->paragraphs(2, true),
                    'status' => $i === 0 ? 'submitted' : 'under_review',
                    'submitted_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }
    }
}
