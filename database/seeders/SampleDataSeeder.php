<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\GamificationStat;
use App\Models\Meeting;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\SocialLink;
use App\Models\User;
use App\Models\UserPrivacy;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@slau-csic.org')->first();

        // ============================================
        // 1. CREATE 30 REALISTIC STUDENTS
        // ============================================

        $students = [
            [
                'name' => 'Grace Mwangi',
                'email' => 'grace.mwangi@students.slau-csic.org',
                'student_id' => 'STU101',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Full-stack developer and CTF enthusiast. Lead developer for the club portal project.',
                'headline' => 'Aspiring Security Engineer',
                'github_username' => 'gracie-mwangi',
                'linkedin_url' => 'https://linkedin.com/in/grace-mwangi',
                'discord_username' => 'gracie_codes#7821',
                'score' => 2850,
                'rank' => 'gold',
                'total_sessions_attended' => 34,
                'current_streak' => 8,
                'longest_streak' => 15,
            ],
            [
                'name' => 'Peter Kamau',
                'email' => 'peter.kamau@students.slau-csic.org',
                'student_id' => 'STU102',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Bug bounty hunter and penetration testing enthusiast. Won 2nd place at National CTF 2025.',
                'headline' => 'Penetration Tester | Bug Bounty Hunter',
                'github_username' => 'peter-kamau',
                'linkedin_url' => 'https://linkedin.com/in/peter-kamau',
                'discord_username' => 'p3t3r_sec#4521',
                'score' => 3200,
                'rank' => 'gold',
                'total_sessions_attended' => 28,
                'current_streak' => 12,
                'longest_streak' => 20,
            ],
            [
                'name' => 'Amina Hassan',
                'email' => 'amina.hassan@students.slau-csic.org',
                'student_id' => 'STU103',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'Passionate about cryptography and network security. Learning reverse engineering in my free time.',
                'headline' => 'Cryptography Enthusiast',
                'github_username' => 'amina-crypto',
                'linkedin_url' => 'https://linkedin.com/in/amina-hassan',
                'discord_username' => 'amina_crypt#3342',
                'score' => 980,
                'rank' => 'silver',
                'total_sessions_attended' => 15,
                'current_streak' => 5,
                'longest_streak' => 8,
            ],
            [
                'name' => 'John Ochieng',
                'email' => 'john.ochieng@students.slau-csic.org',
                'student_id' => 'STU104',
                'program' => 'Software Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Mobile app developer exploring cybersecurity. Built the club attendance tracking app.',
                'headline' => 'Mobile Dev | Security Newbie',
                'github_username' => 'john-ochieng',
                'linkedin_url' => 'https://linkedin.com/in/john-ochieng',
                'discord_username' => 'john_dev#9087',
                'score' => 1250,
                'rank' => 'silver',
                'total_sessions_attended' => 18,
                'current_streak' => 3,
                'longest_streak' => 7,
            ],
            [
                'name' => 'Faith Nyambura',
                'email' => 'faith.nyambura@students.slau-csic.org',
                'student_id' => 'STU105',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Cloud security specialist with a passion for DevSecOps. AWS Certified Solutions Architect.',
                'headline' => 'Cloud Security Analyst',
                'github_username' => 'faith-cloud',
                'linkedin_url' => 'https://linkedin.com/in/faith-nyambura',
                'discord_username' => 'faith_secops#5612',
                'score' => 2100,
                'rank' => 'gold',
                'total_sessions_attended' => 25,
                'current_streak' => 6,
                'longest_streak' => 14,
            ],
            [
                'name' => 'Samuel Kiprop',
                'email' => 'samuel.kiprop@students.slau-csic.org',
                'student_id' => 'STU106',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Red team enthusiast. Specializes in social engineering and physical security assessments.',
                'headline' => 'Red Team Operator',
                'github_username' => 'sam-kiprop',
                'linkedin_url' => 'https://linkedin.com/in/samuel-kiprop',
                'discord_username' => 'sam_redteam#2345',
                'score' => 1800,
                'rank' => 'silver',
                'total_sessions_attended' => 22,
                'current_streak' => 4,
                'longest_streak' => 11,
            ],
            [
                'name' => 'Esther Wanjiku',
                'email' => 'esther.wanjiku@students.slau-csic.org',
                'student_id' => 'STU107',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'Web security researcher and OWASP chapter lead. Organizing the upcoming OWASP meetup.',
                'headline' => 'Web Security Researcher',
                'github_username' => 'esther-wanjiku',
                'linkedin_url' => 'https://linkedin.com/in/esther-wanjiku',
                'discord_username' => 'esther_websec#6789',
                'score' => 750,
                'rank' => 'silver',
                'total_sessions_attended' => 10,
                'current_streak' => 2,
                'longest_streak' => 5,
            ],
            [
                'name' => 'David Muthama',
                'email' => 'david.muthama@students.slau-csic.org',
                'student_id' => 'STU108',
                'program' => 'Computer Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Hardware security and IoT enthusiast. Built a home lab for practicing embedded security.',
                'headline' => 'IoT Security Specialist',
                'github_username' => 'david-muthama',
                'linkedin_url' => 'https://linkedin.com/in/david-muthama',
                'discord_username' => 'david_hardware#1234',
                'score' => 1950,
                'rank' => 'silver',
                'total_sessions_attended' => 20,
                'current_streak' => 7,
                'longest_streak' => 13,
            ],
            [
                'name' => 'Sarah Chebet',
                'email' => 'sarah.chebet@students.slau-csic.org',
                'student_id' => 'STU109',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Digital forensics and incident response specialist. Member of the Cyber Defense team.',
                'headline' => 'DFIR Analyst',
                'github_username' => 'sarah-chebet',
                'linkedin_url' => 'https://linkedin.com/in/sarah-chebet',
                'discord_username' => 'sarah_dfir#4567',
                'score' => 1400,
                'rank' => 'silver',
                'total_sessions_attended' => 19,
                'current_streak' => 3,
                'longest_streak' => 9,
            ],
            [
                'name' => 'Brian Kipngeno',
                'email' => 'brian.kipngeno@students.slau-csic.org',
                'student_id' => 'STU110',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 1,
                'bio' => 'Freshman eager to learn ethical hacking. Completed Google Cybersecurity Certificate.',
                'headline' => 'Aspiring Ethical Hacker',
                'github_username' => 'brian-kipngeno',
                'linkedin_url' => 'https://linkedin.com/in/brian-kipngeno',
                'discord_username' => 'brian_newbie#8901',
                'score' => 320,
                'rank' => 'bronze',
                'total_sessions_attended' => 6,
                'current_streak' => 1,
                'longest_streak' => 3,
            ],
            [
                'name' => 'Mary Akinyi',
                'email' => 'mary.akinyi@students.slau-csic.org',
                'student_id' => 'STU111',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Cyber threat intelligence analyst. Researching APT groups and malware analysis.',
                'headline' => 'Threat Intelligence Analyst',
                'github_username' => 'mary-akinyi',
                'linkedin_url' => 'https://linkedin.com/in/mary-akinyi',
                'discord_username' => 'mary_cti#1122',
                'score' => 2600,
                'rank' => 'gold',
                'total_sessions_attended' => 30,
                'current_streak' => 10,
                'longest_streak' => 18,
            ],
            [
                'name' => 'Daniel Njenga',
                'email' => 'daniel.njenga@students.slau-csic.org',
                'student_id' => 'STU112',
                'program' => 'Software Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'Python developer diving into security automation. Built a vulnerability scanner as a side project.',
                'headline' => 'Security Automation Engineer',
                'github_username' => 'dan-njenga',
                'linkedin_url' => 'https://linkedin.com/in/daniel-njenga',
                'discord_username' => 'dan_automation#3344',
                'score' => 680,
                'rank' => 'silver',
                'total_sessions_attended' => 12,
                'current_streak' => 2,
                'longest_streak' => 6,
            ],
            [
                'name' => 'Ruth Wambui',
                'email' => 'ruth.wambui@students.slau-csic.org',
                'student_id' => 'STU113',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Blockchain security researcher. Smart contract auditor for DeFi protocols.',
                'headline' => 'Blockchain Security Researcher',
                'github_username' => 'ruth-wambui',
                'linkedin_url' => 'https://linkedin.com/in/ruth-wambui',
                'discord_username' => 'ruth_blockchain#5566',
                'score' => 1650,
                'rank' => 'silver',
                'total_sessions_attended' => 16,
                'current_streak' => 5,
                'longest_streak' => 10,
            ],
            [
                'name' => 'Kevin Mutua',
                'email' => 'kevin.mutua@students.slau-csic.org',
                'student_id' => 'STU114',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Network security engineer with CCNA certification. Setting up the clubs lab network.',
                'headline' => 'Network Security Engineer',
                'github_username' => 'kevin-mutua',
                'linkedin_url' => 'https://linkedin.com/in/kevin-mutua',
                'discord_username' => 'kevin_network#7788',
                'score' => 1320,
                'rank' => 'silver',
                'total_sessions_attended' => 17,
                'current_streak' => 4,
                'longest_streak' => 8,
            ],
            [
                'name' => 'Nancy Kemunto',
                'email' => 'nancy.kemunto@students.slau-csic.org',
                'student_id' => 'STU115',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'Women in Tech advocate. Organizing the annual Girls in Cybersecurity workshop.',
                'headline' => 'Cybersecurity Advocate | Women in Tech',
                'github_username' => 'nancy-kemunto',
                'linkedin_url' => 'https://linkedin.com/in/nancy-kemunto',
                'discord_username' => 'nancy_wit#9900',
                'score' => 820,
                'rank' => 'silver',
                'total_sessions_attended' => 13,
                'current_streak' => 3,
                'longest_streak' => 7,
            ],
            [
                'name' => 'Erick Munene',
                'email' => 'erick.munene@students.slau-csic.org',
                'student_id' => 'STU116',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Vulnerability researcher and CVE contributor. Discovered 3 CVEs in 2025.',
                'headline' => 'Vulnerability Researcher',
                'github_username' => 'erick-munene',
                'linkedin_url' => 'https://linkedin.com/in/erick-munene',
                'discord_username' => 'erick_cve#1011',
                'score' => 3500,
                'rank' => 'platinum',
                'total_sessions_attended' => 32,
                'current_streak' => 14,
                'longest_streak' => 25,
            ],
            [
                'name' => 'Janet Chelangat',
                'email' => 'janet.chelangat@students.slau-csic.org',
                'student_id' => 'STU117',
                'program' => 'Computer Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Embedded systems enthusiast specializing in secure IoT firmware development.',
                'headline' => 'Firmware Security Engineer',
                'github_username' => 'janet-chelangat',
                'linkedin_url' => 'https://linkedin.com/in/janet-chelangat',
                'discord_username' => 'janet_firmware#1213',
                'score' => 1100,
                'rank' => 'silver',
                'total_sessions_attended' => 14,
                'current_streak' => 3,
                'longest_streak' => 7,
            ],
            [
                'name' => 'Tom Oluoch',
                'email' => 'tom.oluoch@students.slau-csic.org',
                'student_id' => 'STU118',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 1,
                'bio' => 'First-year student passionate about ethical hacking. Completed TryHackMe top 5%.',
                'headline' => 'CTF Newbie | TryHackMe Top 5%',
                'github_username' => 'tom-oluoch',
                'linkedin_url' => 'https://linkedin.com/in/tom-oluoch',
                'discord_username' => 'tom_thm#1415',
                'score' => 450,
                'rank' => 'bronze',
                'total_sessions_attended' => 8,
                'current_streak' => 2,
                'longest_streak' => 4,
            ],
            [
                'name' => 'Susan Waithera',
                'email' => 'susan.waithera@students.slau-csic.org',
                'student_id' => 'STU119',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'SOC analyst intern at Safaricom. Specializes in SIEM and incident detection.',
                'headline' => 'SOC Analyst | Incident Responder',
                'github_username' => 'susan-waithera',
                'linkedin_url' => 'https://linkedin.com/in/susan-waithera',
                'discord_username' => 'susan_soc#1617',
                'score' => 2300,
                'rank' => 'gold',
                'total_sessions_attended' => 27,
                'current_streak' => 9,
                'longest_streak' => 16,
            ],
            [
                'name' => 'Patrick Njoroge',
                'email' => 'patrick.njoroge@students.slau-csic.org',
                'student_id' => 'STU120',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'Purple team enthusiast. Bridging the gap between red and blue teams.',
                'headline' => 'Purple Team Enthusiast',
                'github_username' => 'patrick-njoroge',
                'linkedin_url' => 'https://linkedin.com/in/patrick-njoroge',
                'discord_username' => 'patrick_purple#1819',
                'score' => 710,
                'rank' => 'silver',
                'total_sessions_attended' => 11,
                'current_streak' => 2,
                'longest_streak' => 5,
            ],
            [
                'name' => 'Catherine Nyongesa',
                'email' => 'catherine.nyongesa@students.slau-csic.org',
                'student_id' => 'STU121',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'AI/ML security researcher. Working on adversarial machine learning for her thesis.',
                'headline' => 'AI Security Researcher',
                'github_username' => 'catherine-nyongesa',
                'linkedin_url' => 'https://linkedin.com/in/catherine-nyongesa',
                'discord_username' => 'catherine_ai#2021',
                'score' => 1580,
                'rank' => 'silver',
                'total_sessions_attended' => 21,
                'current_streak' => 6,
                'longest_streak' => 12,
            ],
            [
                'name' => 'George Otieno',
                'email' => 'george.otieno@students.slau-csic.org',
                'student_id' => 'STU122',
                'program' => 'Software Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'React developer learning secure coding practices. Contributed to several open-source security tools.',
                'headline' => 'Secure Software Developer',
                'github_username' => 'george-otieno',
                'linkedin_url' => 'https://linkedin.com/in/george-otieno',
                'discord_username' => 'george_devsec#2223',
                'score' => 620,
                'rank' => 'bronze',
                'total_sessions_attended' => 9,
                'current_streak' => 1,
                'longest_streak' => 4,
            ],
            [
                'name' => 'Priscilla Jerono',
                'email' => 'priscilla.jerono@students.slau-csic.org',
                'student_id' => 'STU123',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Cyber law and policy enthusiast. Represents the club in national cybersecurity forums.',
                'headline' => 'Cyber Policy Advocate',
                'github_username' => 'priscilla-jerono',
                'linkedin_url' => 'https://linkedin.com/in/priscilla-jerono',
                'discord_username' => 'priscilla_policy#2425',
                'score' => 1340,
                'rank' => 'silver',
                'total_sessions_attended' => 20,
                'current_streak' => 5,
                'longest_streak' => 10,
            ],
            [
                'name' => 'Andrew Kipkorir',
                'email' => 'andrew.kipkorir@students.slau-csic.org',
                'student_id' => 'STU124',
                'program' => 'Computer Engineering',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Radio frequency and wireless security engineer. Built a portable SDR-based intrusion detector.',
                'headline' => 'Wireless Security Engineer',
                'github_username' => 'andrew-kipkorir',
                'linkedin_url' => 'https://linkedin.com/in/andrew-kipkorir',
                'discord_username' => 'andrew_rf#2627',
                'score' => 2050,
                'rank' => 'gold',
                'total_sessions_attended' => 24,
                'current_streak' => 8,
                'longest_streak' => 15,
            ],
            [
                'name' => 'Diana Chepkoech',
                'email' => 'diana.chepkoech@students.slau-csic.org',
                'student_id' => 'STU125',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 1,
                'bio' => 'New to cybersecurity but eager to learn. Completed the Google IT Support certificate.',
                'headline' => 'IT Support | Cybersecurity Student',
                'github_username' => 'diana-chepkoech',
                'linkedin_url' => 'https://linkedin.com/in/diana-chepkoech',
                'discord_username' => 'diana_new#2829',
                'score' => 180,
                'rank' => 'bronze',
                'total_sessions_attended' => 4,
                'current_streak' => 1,
                'longest_streak' => 2,
            ],
            [
                'name' => 'Michael Kiplangat',
                'email' => 'michael.kiplangat@students.slau-csic.org',
                'student_id' => 'STU126',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Competitive programmer turned CTF player. Solved 200+ challenges on various platforms.',
                'headline' => 'Competitive Programmer | CTF Player',
                'github_username' => 'michael-kiplangat',
                'linkedin_url' => 'https://linkedin.com/in/michael-kiplangat',
                'discord_username' => 'michael_cp#3031',
                'score' => 1720,
                'rank' => 'silver',
                'total_sessions_attended' => 23,
                'current_streak' => 7,
                'longest_streak' => 11,
            ],
            [
                'name' => 'Beatrice Adhiambo',
                'email' => 'beatrice.adhiambo@students.slau-csic.org',
                'student_id' => 'STU127',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Security operations manager at the university SOC. Leading the blue team in upcoming competitions.',
                'headline' => 'Blue Team Lead | SOC Manager',
                'github_username' => 'beatrice-adhiambo',
                'linkedin_url' => 'https://linkedin.com/in/beatrice-adhiambo',
                'discord_username' => 'beatrice_blue#3233',
                'score' => 2800,
                'rank' => 'gold',
                'total_sessions_attended' => 35,
                'current_streak' => 11,
                'longest_streak' => 22,
            ],
            [
                'name' => 'Felix Ndegwa',
                'email' => 'felix.ndegwa@students.slau-csic.org',
                'student_id' => 'STU128',
                'program' => 'Computer Science',
                'faculty' => 'Engineering',
                'year_of_study' => 2,
                'bio' => 'OSINT investigator and digital privacy advocate. Runs the clubs OSINT workshop series.',
                'headline' => 'OSINT Investigator | Privacy Advocate',
                'github_username' => 'felix-ndegwa',
                'linkedin_url' => 'https://linkedin.com/in/felix-ndegwa',
                'discord_username' => 'felix_osint#3435',
                'score' => 890,
                'rank' => 'silver',
                'total_sessions_attended' => 16,
                'current_streak' => 4,
                'longest_streak' => 8,
            ],
            [
                'name' => 'Margaret Wairimu',
                'email' => 'margaret.wairimu@students.slau-csic.org',
                'student_id' => 'STU129',
                'program' => 'Information Technology',
                'faculty' => 'Engineering',
                'year_of_study' => 3,
                'bio' => 'Database security specialist. Works on SQL injection prevention and secure database configurations.',
                'headline' => 'Database Security Specialist',
                'github_username' => 'margaret-wairimu',
                'linkedin_url' => 'https://linkedin.com/in/margaret-wairimu',
                'discord_username' => 'margaret_dbsec#3637',
                'score' => 1250,
                'rank' => 'silver',
                'total_sessions_attended' => 18,
                'current_streak' => 5,
                'longest_streak' => 9,
            ],
            [
                'name' => 'Joseph Barasa',
                'email' => 'joseph.barasa@students.slau-csic.org',
                'student_id' => 'STU130',
                'program' => 'Cybersecurity',
                'faculty' => 'Engineering',
                'year_of_study' => 4,
                'bio' => 'Malware analyst and reverse engineer. Contributed to several open-source malware detection tools.',
                'headline' => 'Malware Analyst | Reverse Engineer',
                'github_username' => 'joseph-barasa',
                'linkedin_url' => 'https://linkedin.com/in/joseph-barasa',
                'discord_username' => 'joseph_malware#3839',
                'score' => 3100,
                'rank' => 'platinum',
                'total_sessions_attended' => 33,
                'current_streak' => 13,
                'longest_streak' => 24,
            ],
        ];

        foreach ($students as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'student_id' => $data['student_id'],
                    'phone' => '+2547'.substr(str_shuffle('0123456789'), 0, 8),
                    'program' => $data['program'],
                    'faculty' => $data['faculty'],
                    'year_of_study' => $data['year_of_study'],
                    'date_of_birth' => now()->subYears(20 + array_rand([0, 1, 2, 3, 4, 5]))->subDays(array_rand([0, 365])),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'residence' => fake()->randomElement(['Hall A', 'Hall B', 'Hall C', 'Hall D', 'Off-Campus']),
                    'membership_type' => 'active',
                    'membership_status' => 'active',
                    'is_discord_member' => true,
                    'discord_username' => $data['discord_username'],
                    'joined_at' => now()->subMonths(fake()->numberBetween(1, 24)),
                    'bio' => $data['bio'],
                    'headline' => $data['headline'],
                    'github_username' => $data['github_username'],
                    'linkedin_url' => $data['linkedin_url'],
                    'emergency_contact_name' => fake()->name(),
                    'emergency_contact_phone' => '+2547'.substr(str_shuffle('0123456789'), 0, 8),
                    'total_sessions_attended' => $data['total_sessions_attended'],
                    'score' => $data['score'],
                    'rank' => $data['rank'],
                    'attendance_count' => $data['total_sessions_attended'],
                    'current_streak' => $data['current_streak'],
                    'longest_streak' => $data['longest_streak'],
                ]
            );

            $user->assignRole('member');

            Membership::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'type' => 'active',
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(fake()->numberBetween(1, 12)),
                    'joined_at' => now()->subMonths(fake()->numberBetween(1, 24)),
                ]
            );

            MemberProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => $data['student_id'],
                    'phone' => '+2547'.substr(str_shuffle('0123456789'), 0, 8),
                    'program' => $data['program'],
                    'faculty' => $data['faculty'],
                    'year_of_study' => $data['year_of_study'],
                    'bio' => $data['bio'],
                    'headline' => $data['headline'],
                ]
            );

            SocialLink::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'github_username' => $data['github_username'],
                    'linkedin_url' => $data['linkedin_url'],
                    'discord_username' => $data['discord_username'],
                    'is_discord_member' => true,
                ]
            );

            UserPrivacy::firstOrCreate(['user_id' => $user->id], []);

            GamificationStat::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'attendance_count' => $data['total_sessions_attended'],
                    'total_sessions_attended' => $data['total_sessions_attended'],
                    'current_streak' => $data['current_streak'],
                    'longest_streak' => $data['longest_streak'],
                    'score' => $data['score'],
                    'rank' => $data['rank'],
                ]
            );
        }

        $this->command->info('Created 30 sample students with realistic profiles.');

        // ============================================
        // 2. CREATE 12 REALISTIC EVENTS
        // ============================================

        $categories = EventCategory::pluck('id', 'slug');
        $allUsers = User::where('email', '!=', 'admin@slau-csic.org')->get();

        $now = Carbon::parse('2026-07-15 09:00:00');

        $events = [
            [
                'title' => 'Advanced Web Exploitation Workshop',
                'slug' => 'advanced-web-exploitation-workshop',
                'description' => '<p>Dive deep into advanced web application exploitation techniques. This hands-on workshop covers:</p><ul><li>Server-Side Template Injection (SSTI)</li><li>Server-Side Request Forgery (SSRF)</li><li>Prototype Pollution</li><li>GraphQL Injection</li><li>JWT Attacks</li></ul><p>Bring your own laptop with Burp Suite Pro (Community Edition works too) and a VM with Kali Linux. Prior web security knowledge is recommended.</p>',
                'type' => 'workshop',
                'start_date' => $now->copy()->addDays(5)->setTime(14, 0),
                'end_date' => $now->copy()->addDays(5)->setTime(17, 0),
                'location' => 'Cyber Lab 101, Engineering Building',
                'max_participants' => 25,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(3)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Prerequisites:</strong></p><ul><li>Laptop with Kali Linux or Parrot OS</li><li>Burp Suite (any edition)</li><li>Basic understanding of HTTP, SQL, and JavaScript</li><li>Completed our "Web Security Fundamentals" workshop or equivalent experience</li></ul>',
                'learning_objectives' => '<ul><li>Identify and exploit SSTI vulnerabilities in popular frameworks</li><li>Bypass SSRF protections using advanced techniques</li><li>Exploit GraphQL introspection and injection flaws</li><li>Forge and manipulate JWT tokens to bypass authentication</li></ul>',
                'skill_level' => 'advanced',
                'external_link' => 'https://github.com/slau-csic/web-exploitation-lab',
                'registration_fee' => 0,
                'category_slugs' => ['workshop'],
            ],
            [
                'title' => 'Certified Ethical Hacker (CEH) Exam Prep Bootcamp',
                'slug' => 'ceh-exam-prep-bootcamp-2026',
                'description' => '<p>Intensive 3-day bootcamp preparing students for the CEH v12 certification exam. Covers all 20 modules of the EC-Council CEH curriculum.</p><p><strong>Topics Covered:</strong></p><ul><li>Footprinting and Reconnaissance</li><li>Scanning Networks</li><li>Enumeration</li><li>Vulnerability Analysis</li><li>System Hacking</li><li>Malware Threats</li><li>Social Engineering</li><li>Session Hijacking</li><li>Web Application Hacking</li><li>Wireless Network Hacking</li></ul><p>Includes practice exams and lab exercises. Lunch and certificate included.</p>',
                'type' => 'bootcamp',
                'start_date' => $now->copy()->addDays(10)->setTime(8, 0),
                'end_date' => $now->copy()->addDays(12)->setTime(17, 0),
                'location' => 'Main Auditorium, Engineering Building',
                'max_participants' => 40,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(8)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Requirements:</strong></p><ul><li>Basic understanding of networking (OSI model, TCP/IP)</li><li>Familiarity with Linux command line</li><li>Laptop with minimum 8GB RAM</li><li>VMware or VirtualBox installed</li></ul>',
                'learning_objectives' => '<ul><li>Master all 20 CEH v12 modules</li><li>Hands-on experience with 100+ hacking tools</li><li>Pass practice exams with 85%+ score</li><li>Develop a structured methodology for penetration testing</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => 'https://www.eccouncil.org/programs/certified-ethical-hacker-ceh/',
                'registration_fee' => 1500,
                'category_slugs' => ['bootcamp', 'training'],
            ],
            [
                'title' => 'SLAU Internal CTF Competition 2026',
                'slug' => 'slau-internal-ctf-2026',
                'description' => '<p>The annual SLAU CSIC Capture The Flag competition! Compete against fellow students in a Jeopardy-style CTF with challenges across multiple categories.</p><p><strong>Categories:</strong></p><ul><li>Web Exploitation</li><li>Cryptography</li><li>Reverse Engineering</li><li>Binary Exploitation (PWN)</li><li>Forensics</li><li>OSINT</li><li>Miscellaneous</li></ul><p>Top 3 teams win prizes sponsored by our partners. All participants receive certificates.</p>',
                'type' => 'ctf',
                'start_date' => $now->copy()->addDays(14)->setTime(10, 0),
                'end_date' => $now->copy()->addDays(16)->setTime(18, 0),
                'location' => 'CS Building, 2nd Floor Labs',
                'max_participants' => 60,
                'registration_required' => true,
                'waitlist_enabled' => false,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(12)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Team Requirements:</strong></p><ul><li>Teams of 2-4 members</li><li>Each member must have a laptop</li><li>VPN access to competition network</li><li>Discord account for team communication</li></ul>',
                'learning_objectives' => '<ul><li>Apply practical hacking skills in a competitive environment</li><li>Collaborate effectively as a team under time pressure</li><li>Develop creative problem-solving approaches</li><li>Learn from other teams writeups after the competition</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => null,
                'registration_fee' => 200,
                'category_slugs' => ['ctf', 'competition'],
            ],
            [
                'title' => 'Introduction to Malware Analysis',
                'slug' => 'intro-to-malware-analysis',
                'description' => '<p>A beginner-friendly workshop on malware analysis fundamentals. Learn how to safely analyze malicious software using static and dynamic analysis techniques.</p><p><strong>What You\'ll Learn:</strong></p><ul><li>Setting up a malware analysis lab</li><li>Static analysis with PEStudio, Detect It Easy</li><li>Dynamic analysis with Process Monitor, Procmon</li><li>Network analysis with Wireshark</li><li>Basic reverse engineering with Ghidra</li><li>Writing YARA rules</li></ul><p>All samples provided are safe and contained in an isolated lab environment.</p>',
                'type' => 'workshop',
                'start_date' => $now->copy()->addDays(7)->setTime(10, 0),
                'end_date' => $now->copy()->addDays(7)->setTime(16, 0),
                'location' => 'Room 305, IT Building',
                'max_participants' => 20,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => false,
                'visibility' => 'members_only',
                'registration_deadline' => $now->copy()->addDays(5)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Requirements:</strong></p><ul><li>Laptop with minimum 8GB RAM and 50GB free disk space</li><li>VirtualBox or VMware installed</li><li>Basic understanding of Windows internals (processes, registry, etc.)</li></ul>',
                'learning_objectives' => '<ul><li>Set up a safe malware analysis lab</li><li>Perform static analysis to extract IOCs</li><li>Execute malware safely in a sandboxed environment</li><li>Identify common malware persistence mechanisms</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['workshop', 'training'],
                'allowed_emails' => [
                    'peter.kamau@students.slau-csic.org',
                    'mary.akinyi@students.slau-csic.org',
                    'erick.munene@students.slau-csic.org',
                    'joseph.barasa@students.slau-csic.org',
                    'samuel.kiprop@students.slau-csic.org',
                ],
            ],
            [
                'title' => 'Cybersecurity Career Fair 2026',
                'slug' => 'cybersecurity-career-fair-2026',
                'description' => '<p>Connect with top cybersecurity employers from across Kenya. This career fair features:</p><ul><li>15+ companies hiring for security roles</li><li>Live resume reviews</li><li>Mock interview sessions</li><li>Panel discussion: "Breaking into Cybersecurity"</li><li>Networking lunch</li></ul><p><strong>Participating Companies:</strong> Safaricom, KCB Bank, Cellulant, E&M Computing, Deloitte, PwC, and more.</p>',
                'type' => 'talk',
                'start_date' => $now->copy()->addDays(21)->setTime(9, 0),
                'end_date' => $now->copy()->addDays(21)->setTime(16, 0),
                'location' => 'Main Hall, Student Center',
                'max_participants' => 200,
                'registration_required' => true,
                'waitlist_enabled' => false,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(19)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Recommendations:</strong></p><ul><li>Bring multiple copies of your resume</li><li>Dress professionally</li><li>Prepare a 30-second elevator pitch</li></ul>',
                'learning_objectives' => '<ul><li>Network with industry professionals</li><li>Learn about internship and job opportunities</li><li>Get feedback on your resume and interview skills</li></ul>',
                'skill_level' => null,
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['talk'],
            ],
            [
                'title' => 'Women in Cybersecurity Leadership Panel',
                'slug' => 'women-in-cyber-panel-2026',
                'description' => '<p>Join us for an inspiring panel discussion featuring women leaders in cybersecurity. Our panelists will share their career journeys, challenges, and advice for aspiring female cybersecurity professionals.</p><p><strong>Panelists:</strong></p><ul><li>Dr. Jane Mwangi - CISO, Safaricom</li><li>Faith Nyambura - Cloud Security Lead, AWS Kenya</li><li>Dr. Susan Ochieng - Professor of Cybersecurity, University of Nairobi</li><li>Mary Akinyi - Threat Intelligence Analyst, Cellulant</li></ul><p>Light refreshments will be provided.</p>',
                'type' => 'talk',
                'start_date' => $now->copy()->addDays(9)->setTime(14, 0),
                'end_date' => $now->copy()->addDays(9)->setTime(17, 0),
                'location' => 'Conference Room A, Engineering Building',
                'max_participants' => 80,
                'registration_required' => true,
                'waitlist_enabled' => false,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(7)->setTime(23, 59),
                'status' => 'published',
                'requirements' => null,
                'learning_objectives' => '<ul><li>Learn from experienced women leaders in cybersecurity</li><li>Understand career paths and advancement strategies</li><li>Build professional network with industry mentors</li></ul>',
                'skill_level' => 'beginner',
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['talk', 'awareness_campaign'],
            ],
            [
                'title' => 'Network Security: From Theory to Practice',
                'slug' => 'network-security-practical',
                'description' => '<p>A comprehensive workshop on network security covering both theoretical concepts and practical implementation.</p><p><strong>Topics:</strong></p><ul><li>Network segmentation and DMZ design</li><li>Firewall configuration (iptables, pfSense)</li><li>IDS/IPS deployment (Snort, Suricata)</li><li>VPN setup (WireGuard, OpenVPN)</li><li>Network traffic analysis</li><li>Securing wireless networks</li><li>Network monitoring with ELK stack</li></ul>',
                'type' => 'workshop',
                'start_date' => $now->copy()->addDays(17)->setTime(9, 0),
                'end_date' => $now->copy()->addDays(18)->setTime(17, 0),
                'location' => 'Network Lab, IT Building',
                'max_participants' => 16,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(14)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Requirements:</strong></p><ul><li>CCNA-level networking knowledge</li><li>Laptop with at least 8GB RAM</li><li>VMware Workstation or VirtualBox</li></ul>',
                'learning_objectives' => '<ul><li>Design and implement secure network architectures</li><li>Configure firewalls and intrusion detection systems</li><li>Set up site-to-site and remote-access VPNs</li><li>Analyze network traffic for anomalies and threats</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['workshop', 'training'],
            ],
            [
                'title' => 'Crypto & Privacy: Signal Protocol Workshop',
                'slug' => 'signal-protocol-workshop',
                'description' => '<p>Dive deep into the Signal Protocol used by WhatsApp, Signal, and other secure messaging apps. Implement the protocol from scratch in Python.</p><p><strong>Topics Covered:</strong></p><ul><li>Double Ratchet Algorithm</li><li>X3DH Key Agreement</li><li>Perfect Forward Secrecy</li><li>Deniable Authentication</li><li>Implementing the protocol step by step</li></ul><p>Prerequisite: Basic understanding of cryptography (symmetric/asymmetric encryption, hash functions, Diffie-Hellman key exchange).</p>',
                'type' => 'workshop',
                'start_date' => $now->copy()->addDays(24)->setTime(10, 0),
                'end_date' => $now->copy()->addDays(24)->setTime(17, 0),
                'location' => 'CS Lab 201, Engineering Building',
                'max_participants' => 15,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(21)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Requirements:</strong></p><ul><li>Python 3.10+ installed</li><li>Basic understanding of cryptography concepts</li><li>Familiarity with Git and GitHub</li></ul>',
                'learning_objectives' => '<ul><li>Understand the Signal Protocol inner workings</li><li>Implement the Double Ratchet algorithm</li><li>Deploy X3DH for secure key exchange</li><li>Build a minimal end-to-end encrypted chat application</li></ul>',
                'skill_level' => 'advanced',
                'external_link' => 'https://signal.org/docs/',
                'registration_fee' => 0,
                'category_slugs' => ['workshop', 'hackathon'],
                'allowed_emails' => [
                    'amina.hassan@students.slau-csic.org',
                    'erick.munene@students.slau-csic.org',
                    'margaret.wairimu@students.slau-csic.org',
                    'catherine.nyongesa@students.slau-csic.org',
                    'andrew.kipkorir@students.slau-csic.org',
                ],
            ],
            [
                'title' => 'SLAU CSIC Hackathon: Build a Security Tool',
                'slug' => 'security-tool-hackathon-2026',
                'description' => '<p>48-hour hackathon to build an open-source security tool! Work in teams to create something innovative that solves a real cybersecurity problem.</p><p><strong>Theme:</strong> "Security Automation for Africa"</p><p><strong>Suggested Project Ideas:</strong></p><ul><li>Automated vulnerability scanner for common web apps</li><li>Phishing detection browser extension</li><li>Network monitoring dashboard</li><li>Social media OSINT collector</li><li>Password strength analyzer and generator</li></ul><p>Prizes for top 3 teams. Winning projects will be featured on the club portal.</p>',
                'type' => 'hackathon',
                'start_date' => $now->copy()->addDays(28)->setTime(9, 0),
                'end_date' => $now->copy()->addDays(30)->setTime(17, 0),
                'location' => 'Innovation Hub, Student Center',
                'max_participants' => 50,
                'registration_required' => true,
                'waitlist_enabled' => false,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(25)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Team Requirements:</strong></p><ul><li>Teams of 2-5 members</li><li>Bring your own laptops and chargers</li><li>All code must be original and open-source (MIT license)</li><li>Present a working prototype at the end</li></ul>',
                'learning_objectives' => '<ul><li>Build a functional security tool from scratch</li><li>Collaborate effectively in a time-constrained environment</li><li>Practice agile development methodologies</li><li>Present your project to judges and peers</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => null,
                'registration_fee' => 100,
                'category_slugs' => ['hackathon', 'competition'],
            ],
            [
                'title' => 'Monthly General Meeting - August',
                'slug' => 'monthly-general-meeting-august',
                'description' => '<p>Monthly club meeting to discuss ongoing projects, upcoming events, and general announcements. All active members are required to attend.</p><p><strong>Agenda:</strong></p><ol><li>Reading of previous minutes</li><li>Presidents report</li><li>Treasurers report</li><li>Committee reports (CTF, Projects, Training, Media)</li><li>Upcoming events overview</li><li>General discussion</li><li>Adjournment</li></ol>',
                'type' => 'talk',
                'start_date' => $now->copy()->addDays(2)->setTime(15, 0),
                'end_date' => $now->copy()->addDays(2)->setTime(17, 0),
                'location' => 'Room 301, Engineering Building',
                'max_participants' => 100,
                'registration_required' => true,
                'waitlist_enabled' => false,
                'is_public' => false,
                'visibility' => 'members_only',
                'registration_deadline' => $now->copy()->addDays(1)->setTime(23, 59),
                'status' => 'published',
                'requirements' => null,
                'learning_objectives' => null,
                'skill_level' => null,
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['talk', 'meeting'],
            ],
            [
                'title' => 'Cloud Security Essentials: AWS Edition',
                'slug' => 'cloud-security-essentials-aws',
                'description' => '<p>Learn cloud security best practices for AWS. This workshop covers the AWS Shared Responsibility Model, IAM policies, security groups, encryption, and compliance.</p><p><strong>Hands-on Labs:</strong></p><ul><li>Setting up IAM roles and policies with least privilege</li><li>Configuring VPC security groups and NACLs</li><li>Enabling S3 bucket security and encryption</li><li>Using AWS GuardDuty for threat detection</li><li>CloudTrail and CloudWatch for monitoring</li><li>AWS Security Hub for centralized security management</li></ul><p>AWS credits will be provided for lab exercises.</p>',
                'type' => 'workshop',
                'start_date' => $now->copy()->addDays(12)->setTime(9, 0),
                'end_date' => $now->copy()->addDays(12)->setTime(16, 0),
                'location' => 'Cloud Lab, New Engineering Building',
                'max_participants' => 30,
                'registration_required' => true,
                'waitlist_enabled' => true,
                'is_public' => true,
                'registration_deadline' => $now->copy()->addDays(9)->setTime(23, 59),
                'status' => 'published',
                'requirements' => '<p><strong>Requirements:</strong></p><ul><li>Laptop with internet connection</li><li>Basic understanding of cloud computing concepts</li><li>AWS account (free tier is fine)</li></ul>',
                'learning_objectives' => '<ul><li>Understand the AWS Shared Responsibility Model</li><li>Implement IAM policies following least privilege</li><li>Secure S3 buckets and configure encryption</li><li>Set up monitoring and alerting with CloudWatch</li><li>Respond to security findings in AWS Security Hub</li></ul>',
                'skill_level' => 'intermediate',
                'external_link' => 'https://aws.amazon.com/training/',
                'registration_fee' => 0,
                'category_slugs' => ['workshop', 'training'],
            ],
            [
                'title' => 'End of Year Cybersecurity Awareness Campaign',
                'slug' => 'end-of-year-awareness-campaign',
                'description' => '<p>Join our campus-wide cybersecurity awareness campaign! We\'ll be setting up booths across campus to educate students on:</p><p><strong>Campaign Stations:</strong></p><ul><li><strong>Phishing Station:</strong> Learn to spot phishing emails and SMS scams</li><li><strong>Password Station:</strong> Test your password strength and learn about password managers</li><li><strong>Social Media Privacy:</strong> Audit your social media privacy settings</li><li><strong>Safe Browsing:</strong> Learn about browser security and ad-blockers</li><li><strong>Device Security:</strong> Tips for securing your phone and laptop</li></ul><p>Free stickers, brochures, and merch for participants!</p>',
                'type' => 'awareness_campaign',
                'start_date' => $now->copy()->addDays(35)->setTime(10, 0),
                'end_date' => $now->copy()->addDays(35)->setTime(16, 0),
                'location' => 'Student Center Quadrangle',
                'max_participants' => null,
                'registration_required' => false,
                'waitlist_enabled' => false,
                'is_public' => true,
                'registration_deadline' => null,
                'status' => 'published',
                'requirements' => null,
                'learning_objectives' => '<ul><li>Raise awareness about common cyber threats among the student body</li><li>Provide practical tips for staying safe online</li><li>Promote the SLAU CSIC club and its activities</li></ul>',
                'skill_level' => 'beginner',
                'external_link' => null,
                'registration_fee' => 0,
                'category_slugs' => ['awareness_campaign', 'social'],
            ],
        ];

        foreach ($events as $eventData) {
            $categorySlugs = $eventData['category_slugs'];
            unset($eventData['category_slugs']);

            $allowedEmails = $eventData['allowed_emails'] ?? [];
            unset($eventData['allowed_emails']);

            $event = Event::firstOrCreate(
                ['slug' => $eventData['slug']],
                $eventData + ['organizer_id' => $admin->id]
            );

            $categoryIds = collect($categorySlugs)
                ->map(fn ($slug) => $categories[$slug] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (! empty($categoryIds)) {
                $event->categories()->syncWithoutDetaching($categoryIds);
            }

            if (! empty($allowedEmails)) {
                $allowedUserIds = User::whereIn('email', $allowedEmails)->pluck('id');
                $event->allowedMembers()->sync($allowedUserIds);
            }
        }

        $this->command->info('Created 12 realistic events with categories and allowed members.');

        // ============================================
        // 3. CREATE EVENT REGISTRATIONS
        // ============================================

        $allEvents = Event::where('status', 'published')->get();
        $registrationCount = 0;

        foreach ($allEvents as $event) {
            $maxRegistrations = min($event->max_participants ?? 25, $allUsers->count());
            $registrants = $allUsers->random(min($maxRegistrations, $allUsers->count()));

            $isInviteOnly = $event->allowedMembers()->exists();

            foreach ($registrants as $user) {
                if ($isInviteOnly && ! $event->allowedMembers()->where('user_id', $user->id)->exists()) {
                    continue;
                }

                try {
                    EventRegistration::firstOrCreate(
                        ['event_id' => $event->id, 'user_id' => $user->id],
                        [
                            'status' => fake()->randomElement(['registered', 'registered', 'registered', 'attended', 'cancelled']),
                            'rsvp_status' => fake()->randomElement(['attending', 'attending', 'attending', 'not_attending']),
                            'registered_at' => $event->registration_deadline
                                ? fake()->dateTimeBetween($event->created_at, $event->registration_deadline)
                                : fake()->dateTimeBetween($event->created_at, $event->start_date),
                            'payment_completed' => $event->registration_fee > 0 ? fake()->boolean(70) : true,
                        ]
                    );
                    $registrationCount++;
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }

        $this->command->info("Created {$registrationCount} event registrations.");

        // ============================================
        // 4. CREATE EVENT ATTENDANCE (for past events)
        // ============================================

        $attendanceCount = 0;

        $registrations = EventRegistration::whereIn('event_id', function ($q) use ($now) {
            $q->select('id')->from('events')->where('start_date', '<', $now);
        })->where('status', 'registered')->get();

        foreach ($registrations as $registration) {
            if (fake()->boolean(60)) {
                try {
                    EventAttendance::firstOrCreate(
                        ['event_id' => $registration->event_id, 'member_id' => $registration->user_id],
                        [
                            'status' => fake()->randomElement(['present', 'present', 'present', 'absent', 'excused']),
                            'checked_in_at' => fake()->dateTimeBetween(
                                $registration->event->start_date,
                                $registration->event->end_date ?? $registration->event->start_date->addHours(3)
                            ),
                            'recorded_at' => fake()->dateTimeBetween(
                                $registration->event->start_date,
                                $registration->event->start_date->addDay()
                            ),
                        ]
                    );

                    if (fake()->boolean(70)) {
                        $registration->update(['status' => 'attended', 'attended_at' => now()]);
                    }

                    $attendanceCount++;
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }

        $this->command->info("Created {$attendanceCount} attendance records.");

        // ============================================
        // 5. CREATE SAMPLE MEETINGS
        // ============================================

        $meetingCount = 0;
        $meetingAttendanceCount = 0;

        $meetings = [
            [
                'title' => 'Introduction to Python for Security',
                'type' => 'training',
                'scheduled_at' => $now->copy()->subDays(35)->setTime(14, 0),
                'started_at' => $now->copy()->subDays(35)->setTime(14, 5),
                'ended_at' => $now->copy()->subDays(35)->setTime(16, 0),
                'location' => 'CS Lab 101',
                'duration_minutes' => 120,
                'expected_attendees' => 20,
                'late_threshold_minutes' => 15,
                'attendance_open' => false,
                'description' => '<p>Learn Python fundamentals with a focus on security applications. Topics include socket programming, hash cracking, and basic exploit development.</p>',
                'agenda' => '<ol><li>Python basics review</li><li>Socket programming for security tools</li><li>Writing a port scanner</li><li>Hash cracking with Python</li><li>Basic buffer overflow in Python</li></ol>',
            ],
            [
                'title' => 'Web Security Fundamentals',
                'type' => 'workshop',
                'scheduled_at' => $now->copy()->subDays(28)->setTime(10, 0),
                'started_at' => $now->copy()->subDays(28)->setTime(10, 10),
                'ended_at' => $now->copy()->subDays(28)->setTime(13, 0),
                'location' => 'Cyber Lab 101, Engineering Building',
                'duration_minutes' => 180,
                'expected_attendees' => 25,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Hands-on workshop covering OWASP Top 10 vulnerabilities. Learn to identify and exploit common web security flaws in a safe lab environment.</p>',
                'agenda' => '<ol><li>OWASP Top 10 overview</li><li>SQL injection deep dive</li><li>XSS attacks and defenses</li><li>CSRF and SSRF</li><li>CTF challenge</li></ol>',
            ],
            [
                'title' => 'July General Meeting',
                'type' => 'general',
                'scheduled_at' => $now->copy()->subDays(21)->setTime(15, 0),
                'started_at' => $now->copy()->subDays(21)->setTime(15, 5),
                'ended_at' => $now->copy()->subDays(21)->setTime(16, 30),
                'location' => 'Room 301, Engineering Building',
                'duration_minutes' => 90,
                'expected_attendees' => 50,
                'late_threshold_minutes' => 15,
                'attendance_open' => false,
                'description' => '<p>Monthly general meeting to discuss club activities, upcoming events, and elect committee members.</p>',
                'agenda' => '<ol><li>Reading of previous minutes</li><li>Presidents report</li><li>Treasurers report</li><li>Committee elections</li><li>Upcoming events</li><li>AOB</li></ol>',
            ],
            [
                'title' => 'CTF Strategy Session: Preparing for Regionals',
                'type' => 'workshop',
                'scheduled_at' => $now->copy()->subDays(14)->setTime(14, 0),
                'started_at' => $now->copy()->subDays(14)->setTime(14, 0),
                'ended_at' => $now->copy()->subDays(14)->setTime(17, 0),
                'location' => 'CS Building, 2nd Floor Labs',
                'duration_minutes' => 180,
                'expected_attendees' => 15,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Intensive strategy session for the upcoming regional CTF competition. We will cover team roles, tool setup, and practice with past challenges.</p>',
                'agenda' => '<ol><li>Team role assignment</li><li>Toolchain setup</li><li>Practice round: Web challenges</li><li>Practice round: Crypto challenges</li><li>Practice round: Binary exploitation</li><li>Debrief and strategy</li></ol>',
            ],
            [
                'title' => 'Board Meeting - Q3 Planning',
                'type' => 'special',
                'scheduled_at' => $now->copy()->subDays(7)->setTime(10, 0),
                'started_at' => $now->copy()->subDays(7)->setTime(10, 5),
                'ended_at' => $now->copy()->subDays(7)->setTime(12, 0),
                'location' => 'Conference Room A',
                'duration_minutes' => 120,
                'expected_attendees' => 8,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Board meeting to plan Q3 activities, budget allocation, and set goals for the upcoming semester.</p>',
                'agenda' => '<ol><li>Q2 review</li><li>Budget planning for Q3</li><li>Event calendar for Q3</li><li>Membership drive strategy</li><li>Sponsorship outreach</li></ol>',
            ],
            [
                'title' => 'Network Scanning with Nmap & Masscan',
                'type' => 'training',
                'scheduled_at' => $now->copy()->subDays(4)->setTime(14, 0),
                'started_at' => $now->copy()->subDays(4)->setTime(14, 0),
                'ended_at' => null,
                'location' => 'Network Lab, IT Building',
                'duration_minutes' => 120,
                'expected_attendees' => 20,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Learn advanced network scanning techniques using Nmap and Masscan. Covers stealth scans, service detection, NSE scripts, and firewall evasion.</p>',
                'agenda' => '<ol><li>Nmap basics and scan types</li><li>NSE scripting</li><li>Masscan for large networks</li><li>Firewall evasion techniques</li><li>Lab exercise: Scan the club network</li></ol>',
                'meeting_link' => 'https://meet.google.com/xyz-uvwx-yz',
            ],
            [
                'title' => 'August General Meeting',
                'type' => 'general',
                'scheduled_at' => $now->copy()->addDays(3)->setTime(15, 0),
                'started_at' => null,
                'ended_at' => null,
                'location' => 'Room 301, Engineering Building',
                'duration_minutes' => 90,
                'expected_attendees' => 60,
                'late_threshold_minutes' => 15,
                'attendance_open' => false,
                'description' => '<p>Monthly general meeting. All active members are required to attend.</p>',
                'agenda' => '<ol><li>Reading of previous minutes</li><li>Presidents report</li><li>Committee reports</li><li>Upcoming events</li><li>AOB</li></ol>',
                'meeting_link' => 'https://meet.google.com/aaa-bbbb-ccc',
            ],
            [
                'title' => 'Advanced SQL Injection Techniques',
                'type' => 'training',
                'scheduled_at' => $now->copy()->addDays(10)->setTime(14, 0),
                'started_at' => null,
                'ended_at' => null,
                'location' => 'CS Lab 102, Engineering Building',
                'duration_minutes' => 150,
                'expected_attendees' => 20,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Advanced SQL injection workshop covering time-based blind injection, out-of-band exfiltration, and bypassing WAFs.</p>',
                'agenda' => '<ol><li>SQL injection review</li><li>Blind SQL injection techniques</li><li>Time-based inference</li><li>Out-of-band exfiltration</li><li>WAF bypass techniques</li><li>Automated exploitation with SQLMap</li></ol>',
            ],
            [
                'title' => 'Committee Meeting: Event Planning',
                'type' => 'special',
                'scheduled_at' => $now->copy()->addDays(14)->setTime(11, 0),
                'started_at' => null,
                'ended_at' => null,
                'location' => 'Conference Room B',
                'duration_minutes' => 60,
                'expected_attendees' => 10,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Committee meeting to plan upcoming events for the remainder of the semester.</p>',
                'agenda' => '<ol><li>Event schedule review</li><li>Speaker invitations</li><li>Budget allocation per event</li><li>Marketing and promotion</li><li>Action items</li></ol>',
            ],
            [
                'title' => 'Malware Analysis Lab - Hands On',
                'type' => 'training',
                'scheduled_at' => $now->copy()->addDays(18)->setTime(14, 0),
                'started_at' => null,
                'ended_at' => null,
                'location' => 'Cyber Lab 101',
                'duration_minutes' => 180,
                'expected_attendees' => 15,
                'late_threshold_minutes' => 10,
                'attendance_open' => false,
                'description' => '<p>Advanced malware analysis lab. Open only to students with prior malware analysis experience. We will analyze real-world malware samples in a sandboxed environment.</p>',
                'agenda' => '<ol><li>Lab setup verification</li><li>Static analysis of recent samples</li><li>Dynamic analysis with Procmon</li><li>Network behavior analysis</li><li>Writing YARA rules</li><li>Indicators of Compromise (IOC) extraction</li></ol>',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
                'allowed_emails' => [
                    'erick.munene@students.slau-csic.org',
                    'joseph.barasa@students.slau-csic.org',
                    'mary.akinyi@students.slau-csic.org',
                    'peter.kamau@students.slau-csic.org',
                    'beatrice.adhiambo@students.slau-csic.org',
                ],
            ],
        ];

        foreach ($meetings as $meetingData) {
            $allowedEmails = $meetingData['allowed_emails'] ?? [];
            $meetingLink = $meetingData['meeting_link'] ?? null;
            unset($meetingData['allowed_emails'], $meetingData['meeting_link']);

            $meeting = Meeting::firstOrCreate(
                ['title' => $meetingData['title'], 'scheduled_at' => $meetingData['scheduled_at']],
                $meetingData + ['created_by' => $admin->id, 'meeting_code' => strtoupper(substr(md5(uniqid()), 0, 8))]
            );

            if ($meetingLink && ! $meeting->meeting_link) {
                $meeting->update(['meeting_link' => $meetingLink]);
            }
            $meetingCount++;

            if (! empty($allowedEmails)) {
                $allowedUserIds = User::whereIn('email', $allowedEmails)->pluck('id');
                $meeting->allowedAttendees()->sync($allowedUserIds);
            }

            // Create attendance for past meetings
            if ($meeting->isPast()) {
                $registrants = $allUsers->random(min($meeting->expected_attendees, $allUsers->count()));

                foreach ($registrants as $user) {
                    if ($meeting->allowedAttendees()->exists() && ! $meeting->allowedAttendees()->where('user_id', $user->id)->exists()) {
                        continue;
                    }

                    try {
                        Attendance::firstOrCreate(
                            ['meeting_id' => $meeting->id, 'user_id' => $user->id],
                            [
                                'checked_in_at' => $meeting->started_at
                                    ? fake()->dateTimeBetween($meeting->started_at->subMinutes(5), $meeting->started_at->addMinutes(30))
                                    : null,
                                'check_in_method' => fake()->randomElement(['qr_code', 'qr_code', 'admin_override']),
                                'status' => fake()->randomElement(['present', 'present', 'present', 'late', 'absent']),
                            ]
                        );
                        $meetingAttendanceCount++;
                    } catch (\Exception $e) {
                        // Skip duplicates
                    }
                }
            }
        }

        $this->command->info("Created {$meetingCount} meetings with {$meetingAttendanceCount} attendance records.");

        $this->command->info('');
        $this->command->info('============================================');
        $this->command->info('Sample Data Seeding Complete!');
        $this->command->info('============================================');
        $this->command->info('Admin:     admin@slau-csic.org / password');
        $this->command->info('Students:  30 sample students (password)');
        $this->command->info('Events:    12 realistic events');
        $this->command->info('Meetings:  '.$meetingCount.' meetings with attendance');
        $this->command->info('============================================');
    }
}
