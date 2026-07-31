<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Models\ElectionNomination;
use App\Models\ElectionVote;
use App\Models\User;
use Illuminate\Console\Command;

class SeedVotingData extends Command
{
    protected $signature = 'elections:seed-voting-data';

    protected $description = 'Seed test data for the cabinet voting system';

    public function handle(): int
    {
        $admin = User::where('email', 'admin@slau-csic.org')->first();
        if (! $admin) {
            $this->error('No admin user found. Run the DatabaseSeeder first.');

            return 1;
        }

        $admin->assignRole('admin');
        $admin->givePermissionTo('vote_in_elections');
        $this->info('Admin role assigned.');

        $student = User::where('email', 'student@slau-csic.org')->first();

        $members = collect([$student]);
        for ($i = 1; $i <= 6; $i++) {
            $m = User::firstOrCreate(
                ['email' => "member{$i}@slau-csic.org"],
                [
                    'name' => "Member {$i}",
                    'membership_status' => 'active',
                    'membership_type' => 'active',
                    'password' => bcrypt('password'),
                ]
            );
            if (! $m->hasRole('member')) {
                $m->assignRole('member');
                $m->givePermissionTo('vote_in_elections');
            }
            $members->push($m);
        }
        $this->info("{$members->count()} members ready.");

        // --- 1. Open election ready for voting ---
        $e1 = Election::firstOrCreate(
            ['slug' => '2026-cabinet-president'],
            [
                'title' => '2026 Cabinet President',
                'position' => 'President',
                'description' => 'Vote for the next SLAU CSIC Club President. Each member gets one ballot. Review the candidates\' manifestos and agendas before casting your vote.',
                'status' => 'open',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(7),
                'allow_vote_changes' => true,
                'applications_starts_at' => now()->subDays(21),
                'applications_ends_at' => now()->subDays(3),
                'results_visible' => false,
                'is_test_ballot' => false,
            ]
        );

        $candidateData = [
            ['name' => 'Alice Kamau', 'manifesto' => 'I believe in building a stronger cybersecurity community through hands-on workshops, CTF competitions, and industry mentorships. My goal is to make SLAU CSIC the go-to club for aspiring security professionals.', 'agenda' => "1. Weekly CTF practice sessions\n2. Monthly industry guest speakers\n3. Partnerships with local tech companies\n4. Annual cybersecurity conference", 'sort_order' => 0],
            ['name' => 'Bob Ochieng', 'manifesto' => 'Academic excellence and research should be at the core of our club. I will focus on creating research groups, publishing papers, and representing the university in national competitions.', 'agenda' => "1. Research paper writing workshops\n2. Inter-university competition teams\n3. Academic mentorship pairing\n4. Library resource fund", 'sort_order' => 1],
            ['name' => 'Carol Wanjiku', 'manifesto' => 'Outreach and inclusion are my priorities. I want to expand the club beyond campus, collaborate with other universities, and create programs that welcome students from all backgrounds.', 'agenda' => "1. High school outreach programs\n2. Inter-university collaboration events\n3. Beginner-friendly bootcamps\n4. Diversity & inclusion initiatives", 'sort_order' => 2],
            ['name' => 'David Mutua', 'manifesto' => 'Technology and innovation drive everything I do. I plan to introduce new tech tracks like AI/ML security, blockchain, and cloud computing to keep our club ahead of the curve.', 'agenda' => "1. AI/ML security track launch\n2. Cloud computing workshops\n3. Innovation lab setup\n4. Startup incubation program", 'sort_order' => 3],
        ];

        foreach ($candidateData as $c) {
            $e1->candidates()->firstOrCreate(
                ['name' => $c['name']],
                $c
            );
        }
        $this->info("Open election created: {$e1->title} ({$e1->candidates()->count()} candidates)");

        // --- 2. Closed election with published results + votes ---
        $e2 = Election::firstOrCreate(
            ['slug' => '2025-vice-president-election'],
            [
                'title' => '2025 Vice President Election',
                'position' => 'Vice President',
                'description' => 'Previous election for Vice President. Results have been published.',
                'status' => 'closed',
                'starts_at' => now()->subDays(14),
                'ends_at' => now()->subDays(7),
                'results_visible' => true,
                'allow_vote_changes' => false,
                'is_test_ballot' => false,
            ]
        );

        $c1 = $e2->candidates()->firstOrCreate(['name' => 'Esther Nyambura'], ['manifesto' => 'Committed to service.', 'sort_order' => 0]);
        $c2 = $e2->candidates()->firstOrCreate(['name' => 'Francis Kiprop'], ['manifesto' => 'Driven by excellence.', 'sort_order' => 1]);
        $c3 = $e2->candidates()->firstOrCreate(['name' => 'Grace Akinyi'], ['manifesto' => 'Focused on growth.', 'sort_order' => 2]);

        foreach ($members as $voter) {
            ElectionVote::updateOrCreate(
                ['election_id' => $e2->id, 'user_id' => $voter->id],
                ['election_candidate_id' => $c1->id, 'receipt_code' => ElectionVote::receiptHash(ElectionVote::generateReceiptCode())]
            );
        }
        ElectionVote::updateOrCreate(
            ['election_id' => $e2->id, 'user_id' => $members->get(1)->id],
            ['election_candidate_id' => $c2->id, 'receipt_code' => ElectionVote::receiptHash(ElectionVote::generateReceiptCode())]
        );
        $this->info("Closed election created: {$e2->title} ({$e2->votes()->count()} votes, results visible)");

        // --- 3. Draft election accepting applications ---
        $e3 = Election::firstOrCreate(
            ['slug' => '2026-secretary-election'],
            [
                'title' => '2026 Secretary Election',
                'position' => 'Secretary',
                'description' => 'Nominations are now open! If you want to run for Secretary, submit your application through the nominations page.',
                'status' => 'draft',
                'starts_at' => null,
                'ends_at' => null,
                'allow_vote_changes' => true,
                'applications_starts_at' => now()->subDays(7),
                'applications_ends_at' => now()->addDays(14),
                'results_visible' => false,
                'is_test_ballot' => false,
            ]
        );

        if ($e3->nominations()->count() === 0 && $members->count() > 2) {
            ElectionNomination::create([
                'election_id' => $e3->id,
                'user_id' => $members->get(2)->id,
                'statement' => 'I am organized and passionate about keeping our club running smoothly.',
                'manifesto' => 'Transparent communication, timely meeting notes, and a well-maintained member directory are essential.',
                'agenda' => "1. Digital meeting notes system\n2. Monthly newsletter\n3. Member feedback portal",
                'status' => 'submitted',
                'submitted_at' => now()->subDays(2),
            ]);
            ElectionNomination::create([
                'election_id' => $e3->id,
                'user_id' => $members->get(3)->id,
                'statement' => 'I have strong communication skills and attention to detail.',
                'manifesto' => 'A good secretary ensures no member is left behind. I will focus on inclusive communication.',
                'agenda' => "1. WhatsApp group management\n2. Event communication plan\n3. New member welcome process",
                'status' => 'under_review',
                'submitted_at' => now()->subDays(5),
                'reviewed_at' => now()->subDays(1),
            ]);
        }
        $this->info("Draft election created: {$e3->title} ({$e3->nominations()->count()} nominations)");

        // --- 4. Closed election, results not yet visible ---
        $e4 = Election::firstOrCreate(
            ['slug' => '2025-treasurer-election'],
            [
                'title' => '2025 Treasurer Election',
                'position' => 'Treasurer',
                'description' => 'Results pending publication.',
                'status' => 'closed',
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->subDays(14),
                'results_visible' => false,
                'allow_vote_changes' => false,
                'is_test_ballot' => false,
            ]
        );
        $e4->candidates()->firstOrCreate(['name' => 'Henry Odhiambo'], ['manifesto' => 'Numbers tell stories.', 'sort_order' => 0]);
        $e4->candidates()->firstOrCreate(['name' => 'Irene Wambui'], ['manifesto' => 'Transparency in finances.', 'sort_order' => 1]);
        $this->info("Closed election (hidden results): {$e4->title}");

        $this->newLine();
        $this->info('=== VOTING DATA SEEDING COMPLETE ===');
        $this->newLine();
        $this->info('Member login:  member1@slau-csic.org / password');
        $this->info('Admin login:   admin@slau-csic.org / password');
        $this->newLine();
        $this->info('Elections available:');
        $this->info('  1. 2026 Cabinet President (OPEN - ready to vote)');
        $this->info('  2. 2025 Vice President (CLOSED - results visible)');
        $this->info('  3. 2026 Secretary (DRAFT - accepting nominations)');
        $this->info('  4. 2025 Treasurer (CLOSED - results hidden)');

        return 0;
    }
}
