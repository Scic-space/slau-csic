<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\Competition;
use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\GamificationStat;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\QuestionBankOption;
use App\Models\QuestionBankQuestion;
use App\Models\SocialLink;
use App\Models\User;
use App\Models\UserPrivacy;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@slau-csic.org')->first();

        // ============================================
        // 1. CREATE SAMPLE STUDENT
        // ============================================

        $student = User::firstOrCreate(
            ['email' => 'student@slau-csic.org'],
            [
                'name' => 'Demo Student',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'student_id' => 'STU001',
                'phone' => '+254700123456',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'membership_type' => 'active',
                'membership_status' => 'active',
                'joined_at' => now()->subMonths(6),
                'bio' => 'Cybersecurity enthusiast passionate about CTFs and penetration testing.',
                'github_username' => 'demo-student',
                'linkedin_url' => 'https://linkedin.com/in/demo-student',
                'attendance_count' => 5,
                'score' => 450,
                'rank' => 'silver',
            ]
        );

        $student->assignRole('member');

        Membership::firstOrCreate(
            ['user_id' => $student->id],
            [
                'type' => 'active',
                'status' => 'active',
                'approved_by' => $admin->id,
                'approved_at' => now()->subMonths(6),
                'joined_at' => now()->subMonths(6),
            ]
        );

        MemberProfile::firstOrCreate(
            ['user_id' => $student->id],
            [
                'student_id' => 'STU001',
                'phone' => '+254700123456',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Cybersecurity enthusiast passionate about CTFs and penetration testing.',
                'headline' => 'Aspiring Security Analyst',
            ]
        );

        SocialLink::firstOrCreate(
            ['user_id' => $student->id],
            [
                'github_username' => 'demo-student',
                'linkedin_url' => 'https://linkedin.com/in/demo-student',
                'discord_username' => 'demostudent#1234',
                'is_discord_member' => true,
            ]
        );

        UserPrivacy::firstOrCreate(
            ['user_id' => $student->id],
            []
        );

        GamificationStat::firstOrCreate(
            ['user_id' => $student->id],
            [
                'attendance_count' => 5,
                'total_sessions_attended' => 5,
                'current_streak' => 2,
                'longest_streak' => 4,
                'score' => 450,
                'rank' => 'silver',
            ]
        );

        $this->command->info('Sample student created: student@slau-csic.org / password');

        // ============================================
        // 2. CREATE SAMPLE EVENTS
        // ============================================

        $categories = EventCategory::all();

        Event::firstOrCreate(
            ['slug' => 'web-security-workshop'],
            [
                'title' => 'Web Security Workshop',
                'description' => 'Hands-on workshop covering OWASP Top 10, SQL injection, XSS, and CSRF. Bring your laptop with Burp Suite installed.',
                'type' => 'workshop',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(7)->addHours(3),
                'location' => 'Cyber Lab, Engineering Building',
                'max_participants' => 30,
                'registration_required' => true,
                'is_public' => true,
                'registration_deadline' => now()->addDays(5),
                'status' => 'published',
                'organizer_id' => $admin->id,
                'requirements' => 'Laptop with Burp Suite Community Edition',
                'registration_fee' => 0,
            ]
        );

        Event::firstOrCreate(
            ['slug' => 'ctf-bootcamp-2026'],
            [
                'title' => 'CTF Bootcamp 2026',
                'description' => 'A three-day intensive bootcamp covering cryptography, reverse engineering, pwn, and forensics for CTF competitions.',
                'type' => 'bootcamp',
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(16),
                'location' => 'Online (Google Meet)',
                'max_participants' => 50,
                'registration_required' => true,
                'is_public' => true,
                'registration_deadline' => now()->addDays(12),
                'status' => 'published',
                'organizer_id' => $admin->id,
                'registration_fee' => 0,
            ]
        );

        Event::firstOrCreate(
            ['slug' => 'monthly-general-meeting-march'],
            [
                'title' => 'Monthly General Meeting - March',
                'description' => 'Monthly club meeting to discuss ongoing projects, upcoming events, and general announcements. Attendance is mandatory for active members.',
                'type' => 'meeting',
                'start_date' => now()->addDays(3),
                'end_date' => now()->addDays(3)->addHours(1),
                'location' => 'Room 301, Engineering Building',
                'max_participants' => 100,
                'registration_required' => true,
                'is_public' => false,
                'registration_deadline' => now()->addDays(2),
                'status' => 'published',
                'organizer_id' => $admin->id,
                'registration_fee' => 0,
            ]
        );

        $this->command->info('Sample events created.');

        // ============================================
        // 3. CREATE SAMPLE EXAM WITH QUESTIONS
        // ============================================

        $exam = Exam::firstOrCreate(
            [
                'title' => 'Cybersecurity Fundamentals Assessment',
                'user_id' => $admin->id,
            ],
            [
                'description' => 'A comprehensive assessment covering network security, cryptography, web security, and basic penetration testing concepts.',
                'duration_minutes' => 60,
                'passing_score' => 70,
                'status' => 'published',
            ]
        );

        $questions = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'What does SQL injection exploit in a web application?',
                'marks' => 10,
                'explanation' => 'SQL injection exploits improper sanitization of user input in SQL queries.',
                'options' => [
                    ['text' => 'Improper input sanitization in SQL queries', 'correct' => true],
                    ['text' => 'Weak encryption algorithms', 'correct' => false],
                    ['text' => 'Buffer overflow vulnerabilities', 'correct' => false],
                    ['text' => 'DNS misconfiguration', 'correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Which encryption algorithm is considered symmetric?',
                'marks' => 10,
                'explanation' => 'AES is a symmetric encryption algorithm that uses the same key for encryption and decryption.',
                'options' => [
                    ['text' => 'RSA', 'correct' => false],
                    ['text' => 'AES', 'correct' => true],
                    ['text' => 'DSA', 'correct' => false],
                    ['text' => 'ECDSA', 'correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'What is the purpose of a firewall?',
                'marks' => 10,
                'explanation' => 'A firewall monitors and controls incoming and outgoing network traffic based on security rules.',
                'options' => [
                    ['text' => 'Monitor and control network traffic', 'correct' => true],
                    ['text' => 'Encrypt all network communications', 'correct' => false],
                    ['text' => 'Detect software vulnerabilities', 'correct' => false],
                    ['text' => 'Manage user passwords', 'correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Which of the following is a common XSS attack type?',
                'marks' => 10,
                'explanation' => 'Stored XSS (persistent) is a common type where malicious scripts are permanently stored on the target server.',
                'options' => [
                    ['text' => 'Stored XSS', 'correct' => true],
                    ['text' => 'SQL injection', 'correct' => false],
                    ['text' => 'Man-in-the-middle', 'correct' => false],
                    ['text' => 'ARP spoofing', 'correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'What port does HTTPS typically use?',
                'marks' => 10,
                'explanation' => 'HTTPS uses port 443 by default, while HTTP uses port 80.',
                'options' => [
                    ['text' => '443', 'correct' => true],
                    ['text' => '80', 'correct' => false],
                    ['text' => '22', 'correct' => false],
                    ['text' => '8080', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $index => $qData) {
            $question = QuestionBankQuestion::firstOrCreate(
                [
                    'question_text' => $qData['question_text'],
                    'user_id' => $admin->id,
                ],
                [
                    'type' => $qData['type'],
                    'marks' => $qData['marks'],
                    'explanation' => $qData['explanation'] ?? null,
                ]
            );

            foreach ($qData['options'] as $optIndex => $opt) {
                QuestionBankOption::firstOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_text' => $opt['text'],
                    ],
                    [
                        'is_correct' => $opt['correct'],
                        'order' => $optIndex + 1,
                    ]
                );
            }

            ExamQuestion::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'question_bank_question_id' => $question->id,
                ],
                [
                    'custom_marks' => $qData['marks'],
                    'order' => $index + 1,
                ]
            );
        }

        $this->command->info('Sample exam created with 5 questions.');

        // ============================================
        // 4. CREATE SAMPLE CTF COMPETITION
        // ============================================

        $ctfCategory = CtfCategory::firstOrCreate(
            ['slug' => 'web'],
            ['name' => 'Web', 'color' => '#3b82f6', 'sort_order' => 0]
        );

        $ctfCategory2 = CtfCategory::firstOrCreate(
            ['slug' => 'crypto'],
            ['name' => 'Crypto', 'color' => '#8b5cf6', 'sort_order' => 1]
        );

        $competition = CtfCompetition::firstOrCreate(
            ['slug' => 'slau-internal-ctf-2026'],
            [
                'title' => 'SLAU Internal CTF 2026',
                'description' => 'The annual SLAU CSIC internal Capture The Flag competition. Test your skills in web exploitation, cryptography, forensics, and binary exploitation.',
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(6),
                'status' => 'published',
                'is_public' => true,
            ]
        );

        $challenges = [
            [
                'title' => 'SQL Injection Lab',
                'description' => 'Find and exploit a SQL injection vulnerability in the login form to retrieve the admin password.',
                'flag' => 'SLAU_CSIC{sql_1nj3ct10n_m4st3r}',
                'points' => 100,
                'difficulty' => 'easy',
                'category_id' => $ctfCategory->id,
                'is_active' => true,
                'max_attempts' => 10,
            ],
            [
                'title' => 'XSS Challenge',
                'description' => 'Craft a cross-site scripting payload that executes alert(document.cookie) in the victim\'s browser.',
                'flag' => 'SLAU_CSIC{xss_pr0t3ct10n_3ss3nt14l}',
                'points' => 150,
                'difficulty' => 'medium',
                'category_id' => $ctfCategory->id,
                'is_active' => true,
                'max_attempts' => 5,
            ],
            [
                'title' => 'Caesar\'s Secret',
                'description' => 'Decrypt the message to find the hidden flag: "FODU_IRG{plqhyq_fxqlqj}"',
                'flag' => 'SLAU_CSIC{simple_caesar_cipher}',
                'points' => 50,
                'difficulty' => 'easy',
                'category_id' => $ctfCategory2->id,
                'is_active' => true,
                'max_attempts' => 20,
            ],
            [
                'title' => 'Hash Detective',
                'description' => 'Identify and crack the hash: 5d41402abc4b2a76b9719d911017c592',
                'flag' => 'SLAU_CSIC{h4sh_cr4ck1ng_101}',
                'points' => 75,
                'difficulty' => 'easy',
                'category_id' => $ctfCategory2->id,
                'is_active' => true,
                'max_attempts' => 10,
            ],
        ];

        foreach ($challenges as $cData) {
            $flagHash = hash('sha256', strtolower($cData['flag']));
            $challenge = CtfChallenge::firstOrCreate(
                [
                    'ctf_competition_id' => $competition->id,
                    'slug' => \Illuminate\Support\Str::slug($cData['title']),
                ],
                [
                    'ctf_category_id' => $cData['category_id'],
                    'title' => $cData['title'],
                    'description' => $cData['description'],
                    'flag_hash' => $flagHash,
                    'points' => $cData['points'],
                    'difficulty' => $cData['difficulty'],
                    'is_active' => $cData['is_active'],
                    'max_attempts' => $cData['max_attempts'],
                ]
            );

            if (isset($cData['hint'])) {
                $challenge->hints()->firstOrCreate(
                    ['tier' => 0],
                    [
                        'content' => $cData['hint'],
                        'cost' => $cData['hint_cost'],
                    ]
                );
            }
        }

        $this->command->info('Sample CTF competition created with 4 challenges.');

        // ============================================
        // 5. CREATE COMPETITION CHALLENGES (Admin Resource)
        // ============================================

        $competition = Competition::firstOrCreate(
            ['name' => 'SLAU CSIC Internal CTF 2026'],
            [
                'description' => 'Internal CTF competition for SLAU CSIC members featuring web exploitation, cryptography, and reverse engineering challenges.',
                'type' => 'ctf',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(7),
                'location' => 'Cyber Lab, Engineering Building',
                'is_team_based' => false,
                'participation_status' => 'registered',
            ]
        );

        if (! $competition->participants()->where('user_id', $student->id)->exists()) {
            $competition->participants()->create([
                'user_id' => $student->id,
                'team_name' => null,
                'role' => 'member',
            ]);
        }

        $challengeData = [
            [
                'title' => 'Web: Login Bypass',
                'description' => 'Find the SQL injection vulnerability in the login form and retrieve the admin password. The application uses unsanitized input directly in queries.',
                'type' => 'flag',
                'points' => 100,
                'answer' => 'flag{sq1_1nj3ct10n_m4st3r}',
                'sort_order' => 1,
            ],
            [
                'title' => 'Crypto: Caesar\'s Challenge',
                'description' => 'Decrypt the following ciphertext: "WKH_VHFUHW_LV_QRW_LQ_WKH_BRGH". Hint: Julius Caesar would know the answer.',
                'type' => 'flag',
                'points' => 50,
                'answer' => 'flag{caesar_cipher_basics}',
                'sort_order' => 2,
            ],
            [
                'title' => 'Crypto: Hash Cracking 101',
                'description' => 'Identify and crack this MD5 hash: "d8578edf8458ce06fbc5bb76a58c5ca4". Upload the original password as the flag in flag{password} format.',
                'type' => 'text',
                'points' => 75,
                'answer' => 'flag{qwerty}',
                'sort_order' => 3,
            ],
            [
                'title' => 'Web: XSS Challenge',
                'description' => 'A blog application has a vulnerable comment section. Craft an XSS payload that steals cookies. Submit the payload as the answer.',
                'type' => 'text',
                'points' => 150,
                'answer' => 'flag{xss_pr3v3nt10n_3ss3nt14l}',
                'sort_order' => 4,
            ],
            [
                'title' => 'Reversing: Secret String',
                'description' => 'A binary contains a hidden string that is XOR-encoded with the key 0x2A. The encoded bytes are: 0x0A 0x1C 0x1B 0x1A 0x58 0x09 0x58 0x1A 0x1B 0x13.',
                'type' => 'flag',
                'points' => 200,
                'answer' => 'flag{r3v3rs1ng_1s_fun}',
                'sort_order' => 5,
            ],
        ];

        foreach ($challengeData as $cData) {
            $challenge = Challenge::firstOrCreate(
                [
                    'competition_id' => $competition->id,
                    'title' => $cData['title'],
                ],
                [
                    'competition_id' => $competition->id,
                    'title' => $cData['title'],
                    'description' => $cData['description'],
                    'type' => $cData['type'],
                    'points' => $cData['points'],
                    'answer' => $cData['answer'],
                    'sort_order' => $cData['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Seed demo submissions (one per challenge, unique constraint on challenge_id + user_id)
        $allChallenges = $competition->challenges()->get();

        foreach ($allChallenges as $index => $challenge) {
            if ($challenge->isSolvedBy($student->id)) {
                continue;
            }

            match ($index) {
                0 => ChallengeSubmission::create([
                    'challenge_id' => $challenge->id,
                    'user_id' => $student->id,
                    'answer' => 'flag{sql_1nj3ct10n_m4st3r}',
                    'is_correct' => true,
                    'points_awarded' => $challenge->points,
                    'submitted_at' => now()->subHour(),
                ]),
                1 => ChallengeSubmission::create([
                    'challenge_id' => $challenge->id,
                    'user_id' => $student->id,
                    'answer' => 'flag{c4esar_w4s_h3r3}',
                    'is_correct' => false,
                    'points_awarded' => 0,
                    'submitted_at' => now()->subMinutes(30),
                ]),
                4 => ChallengeSubmission::create([
                    'challenge_id' => $challenge->id,
                    'user_id' => $student->id,
                    'answer' => 'flag{r3v3rs1ng_1s_fun}',
                    'is_correct' => true,
                    'points_awarded' => $challenge->points,
                    'submitted_at' => now()->subMinutes(15),
                ]),
                default => null,
            };
        }

        $this->command->info('Sample competition challenges created with demo submissions.');
        $this->command->info('');
        $this->command->info('============================================');
        $this->command->info('Demo Data Seeding Complete!');
        $this->command->info('============================================');
        $this->command->info('Admin:     admin@slau-csic.org / password');
        $this->command->info('Student:   student@slau-csic.org / password');
        $this->command->info('============================================');
    }
}
