<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@slau-csic.org')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first()
            ?? User::first();

        $announcements = [
            [
                'title' => 'Cybersecurity Workshop: Ethical Hacking Basics',
                'content' => '<p>We are excited to announce our upcoming hands-on workshop on <strong>Ethical Hacking Basics</strong>!</p>
<p>This session will cover:</p>
<ul><li>Introduction to penetration testing methodologies</li><li>Reconnaissance and information gathering techniques</li><li>Common vulnerability scanning tools</li><li>Basic exploitation and reporting</li></ul>
<p><strong>Date:</strong> Saturday, July 25, 2026<br><strong>Time:</strong> 2:00 PM – 5:00 PM<br><strong>Venue:</strong> CSIC Lab, Block C</p>
<p>Laptops are required. Please register through the events page.</p>',
                'type' => 'event',
            ],
            [
                'title' => 'Q2 CTF Competition Results',
                'content' => '<p>Congratulations to all participants in this quarter\'s Capture The Flag competition!</p>
<p><strong>Top 3 Teams:</strong></p>
<ol><li><strong>CyberWolves</strong> — 2,450 points</li><li><strong>ZeroDay Squad</strong> — 2,180 points</li><li><strong>Binary Bandits</strong> — 1,950 points</li></ol>
<p>Special recognition goes to <strong>Amara Osei</strong> for solving the hardest reverse engineering challenge in record time.</p>
<p>The next CTF is scheduled for August. Start forming your teams!</p>',
                'type' => 'achievement',
            ],
            [
                'title' => 'Weekly Meeting — July 22',
                'content' => '<p>Dear members,</p>
<p>This is a reminder that our weekly meeting will hold as scheduled:</p>
<p><strong>Day:</strong> Tuesday, July 22, 2026<br><strong>Time:</strong> 5:30 PM<br><strong>Agenda:</strong></p>
<ul><li>Cabinet election updates</li><li>Upcoming workshop planning</li><li>Member onboarding review</li><li>Open floor</li></ul>
<p>Attendance is encouraged for all active members.</p>',
                'type' => 'meeting',
            ],
            [
                'title' => 'Important: Membership Renewal Deadline Extended',
                'content' => '<p>The deadline for membership renewal has been extended to <strong>August 1, 2026</strong>.</p>
<p>All members who have not yet renewed are reminded to complete their renewal before the deadline. Failure to renew will result in loss of access to club resources including:</p>
<ul><li>Lab access and equipment</li><li>Competition eligibility</li><li>Certification exam discounts</li><li>Workshop registration priority</li></ul>
<p>Contact the finance team or visit the portal to complete your renewal.</p>',
                'type' => 'urgent',
            ],
            [
                'title' => 'Welcome New Members — July 2026 Cohort',
                'content' => '<p>We are delighted to welcome <strong>30 new members</strong> who have joined SLAU CSIC this month!</p>
<p>To all new members: we encourage you to:</p>
<ol><li>Complete your profile on the member portal</li><li>Attend the next weekly meeting for orientation</li><li>Join our communication channels (Discord, WhatsApp)</li><li>Explore the available courses and workshops</li></ol>
<p>Buddy pairing will be organized at the next meeting. Welcome to the family!</p>',
                'type' => 'general',
            ],
            [
                'title' => 'SLAU CSIC Members Win National Cyber Challenge',
                'content' => '<p>We are proud to announce that a team of four SLAU CSIC members placed <strong>1st place</strong> at the 2026 National University Cybersecurity Challenge held in Nairobi.</p>
<p>The team comprised:</p>
<ul><li>Fatima Abdullahi — Team Lead</li><li>Kevin Odhiambo — Network Security</li><li>Grace Muthoni — Forensics</li><li>Ibrahim Hassan — Cryptography</li></ul>
<p>This is a historic achievement for our club. The team will be recognized at the next general meeting.</p>',
                'type' => 'achievement',
            ],
        ];

        foreach ($announcements as $i => $data) {
            Announcement::create(array_merge($data, [
                'audience' => 'all',
                'is_published' => true,
                'published_at' => now()->subDays(count($announcements) - $i)->setTime(9, 0),
                'created_by' => $admin->id,
            ]));
        }
    }
}
