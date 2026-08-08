<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'kevinssali23@gmail.com')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first()
            ?? User::first();

        $polls = [
            [
                'question' => 'What topic should we cover in the next workshop?',
                'description' => 'Help us decide the focus of our upcoming hands-on session.',
                'is_published' => true,
                'allow_multiple' => false,
                'expires_at' => now()->addDays(14),
                'options' => ['Network Penetration Testing', 'Web Application Security', 'Malware Analysis', 'Digital Forensics'],
            ],
            [
                'question' => 'Best day for weekly meetings?',
                'description' => 'We are considering changing our regular meeting schedule.',
                'is_published' => true,
                'allow_multiple' => true,
                'expires_at' => now()->addDays(7),
                'options' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            ],
            [
                'question' => 'Should we organize a CTF this semester?',
                'description' => 'A Capture The Flag competition for all club members.',
                'is_published' => true,
                'allow_multiple' => false,
                'expires_at' => now()->subDays(3),
                'options' => ['Yes, definitely!', 'Maybe', 'No, not this time'],
            ],
            [
                'question' => 'Preferred venue for the annual cybersecurity conference?',
                'description' => 'Vote on where we should host this year\'s conference.',
                'is_published' => true,
                'allow_multiple' => false,
                'expires_at' => now()->subDays(5),
                'options' => ['University Auditorium', 'CSIC Lab Block C', 'Online (Zoom)'],
            ],
            [
                'question' => 'Which programming language should we focus on for scripting?',
                'description' => 'Choose the primary scripting language for upcoming tutorials.',
                'is_published' => false,
                'allow_multiple' => false,
                'expires_at' => now()->addDays(21),
                'options' => ['Python', 'Bash', 'PowerShell', 'Go'],
            ],
        ];

        foreach ($polls as $data) {
            $options = $data['options'];
            unset($data['options']);

            $poll = Poll::create(array_merge($data, [
                'created_by' => $admin->id,
            ]));

            foreach ($options as $i => $label) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'label' => $label,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
