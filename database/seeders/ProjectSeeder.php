<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $lead = User::first();

        if (! $lead) {
            return;
        }

        $projects = [
            [
                'name' => 'SLAU CTF Platform',
                'description' => 'Our custom-built CTF challenge hosting platform. Supports multiple challenge categories, team management, real-time scoring, and automated flag verification. Used by the club for all seasonal competitions.',
                'objectives' => 'Build a reliable, scalable platform that can handle 100+ concurrent players. Support challenge types: web, crypto, forensics, reverse, networking. Integrate with Discord for notifications.',
                'type' => 'development',
                'status' => 'active',
                'progress_percentage' => 75,
                'tags' => ['laravel', 'livewire', 'ctf', 'security'],
            ],
            [
                'name' => 'PhishGuard',
                'description' => 'Campus-wide phishing awareness platform. Simulates real phishing attacks to train students and staff to recognize threats. Generates reports for university administration.',
                'objectives' => 'Create realistic phishing templates. Track open rates, click rates, and report rates. Generate monthly security awareness reports. Integrate with university email systems.',
                'type' => 'security_audit',
                'status' => 'active',
                'progress_percentage' => 40,
                'tags' => ['php', 'phishing', 'awareness', 'campus'],
            ],
            [
                'name' => 'NetSentinel',
                'description' => 'Network intrusion detection and traffic analysis tool. Monitors campus network traffic for suspicious activity and potential security breaches. Provides real-time alerts.',
                'objectives' => 'Deploy passive network monitoring. Detect common attack patterns. Alert system admins via email/Slack. Log all events for forensic analysis.',
                'type' => 'research',
                'status' => 'proposed',
                'progress_percentage' => 10,
                'tags' => ['python', 'networking', 'ids', 'monitoring'],
            ],
            [
                'name' => 'SecurityWriteups',
                'description' => 'Technical blog and knowledge base for the club. Publishes CTF writeups, vulnerability disclosures, security tutorials, and research papers. Open source and community-contributed.',
                'objectives' => 'Publish 2+ articles per month. Cover CTF solutions, tool tutorials, and research findings. Accept community contributions via pull requests. Build SEO for club visibility.',
                'type' => 'community',
                'status' => 'active',
                'progress_percentage' => 60,
                'tags' => ['blog', 'writeups', 'documentation', 'open-source'],
            ],
            [
                'name' => 'Malware Sandbox',
                'description' => 'Isolated environment for analyzing malware samples. Provides safe execution, behavioral analysis, and reporting. Used for research and educational workshops.',
                'objectives' => 'Set up isolated VMs for malware execution. Automate sample analysis and reporting. Integrate with VirusTotal API. Support PE, PDF, and document analysis.',
                'type' => 'research',
                'status' => 'on_hold',
                'progress_percentage' => 25,
                'tags' => ['malware', 'analysis', 'sandbox', 'forensics'],
            ],
            [
                'name' => 'Campus Audit Toolkit',
                'description' => 'Collection of scripts and tools for auditing campus infrastructure. Covers web applications, network services, and configuration reviews. Used by the club to help university IT security.',
                'objectives' => 'Automate common security checks. Generate professional audit reports. Cover OWASP Top 10, network misconfigs, and access control issues. Keep tools updated with latest CVEs.',
                'type' => 'security_audit',
                'status' => 'active',
                'progress_percentage' => 55,
                'tags' => ['audit', 'security', 'tools', 'owasp'],
            ],
        ];

        foreach ($projects as $project) {
            Project::create(array_merge($project, [
                'lead_id' => $lead->id,
                'start_date' => now()->subMonths(rand(1, 6)),
            ]));
        }
    }
}
