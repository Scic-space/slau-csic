<?php

namespace Database\Seeders;

use App\Models\ClubResource;
use Illuminate\Database\Seeder;

class ClubPortalSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'title' => 'Cybersecurity Fundamentals — Study Guide',
                'slug' => 'cybersecurity-fundamentals-study-guide',
                'category' => 'learning',
                'difficulty' => 'Beginner',
                'status' => 'open',
                'points' => 50,
                'summary' => 'Comprehensive revision material for the Cybersecurity Fundamentals exam. Covers core security principles, threat modeling, and risk management.',
                'details' => 'Includes key terminology, CIA triad, security controls overview, and practice questions.',
                'target_total' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => 'Network Security Basics — Lesson Notes',
                'slug' => 'network-security-basics-lesson-notes',
                'category' => 'learning',
                'difficulty' => 'Beginner',
                'status' => 'open',
                'points' => 45,
                'summary' => 'Class companion notes for Network Security lessons. Covers firewall rules, segmentation, VPNs, and traffic analysis.',
                'details' => 'Lesson summaries, diagrams of network topologies, and hands-on exercises using Wireshark and iptables.',
                'target_total' => 1,
                'sort_order' => 2,
            ],
            [
                'title' => 'Ethical Hacking Principles — Practical Guide',
                'slug' => 'ethical-hacking-principles-guide',
                'category' => 'learning',
                'difficulty' => 'Intermediate',
                'status' => 'open',
                'points' => 60,
                'summary' => 'Step-by-step guide aligned to the Ethical Hacking exam. Covers reconnaissance, scanning, exploitation, and reporting.',
                'details' => 'Includes methodology walkthroughs, legal and ethical considerations, and a sample penetration test report template.',
                'target_total' => 1,
                'sort_order' => 3,
            ],
            [
                'title' => 'Cryptography Concepts — Reference Sheet',
                'slug' => 'cryptography-concepts-reference-sheet',
                'category' => 'learning',
                'difficulty' => 'Intermediate',
                'status' => 'active',
                'points' => 40,
                'summary' => 'Quick reference for the Cryptography exam. Covers symmetric/asymmetric encryption, hashing, digital signatures, and PKI.',
                'details' => 'Algorithm comparison table, common use cases, and exam tips for crypto problems.',
                'target_total' => 1,
                'sort_order' => 4,
            ],
            [
                'title' => 'Web Application Security — Exam Prep',
                'slug' => 'web-application-security-exam-prep',
                'category' => 'learning',
                'difficulty' => 'Intermediate',
                'status' => 'open',
                'points' => 55,
                'summary' => 'Focused preparation for the Web Application Security exam. Covers OWASP Top 10, input validation, and secure configuration.',
                'details' => 'Vulnerability deep-dives, exploit demonstration walkthroughs, and mitigation strategy summaries.',
                'target_total' => 1,
                'sort_order' => 5,
            ],
            [
                'title' => 'Incident Response & Forensics — Playbook',
                'slug' => 'incident-response-forensics-playbook',
                'category' => 'learning',
                'difficulty' => 'Intermediate',
                'status' => 'open',
                'points' => 70,
                'summary' => 'Exam-oriented playbook for Incident Response & Forensics. Covers detection, containment, eradication, and evidence handling.',
                'details' => 'IR checklist templates, forensic acquisition procedures, chain of custody forms, and report writing guides.',
                'target_total' => 1,
                'sort_order' => 6,
            ],
            [
                'title' => 'Cloud Security Architecture — Study Notes',
                'slug' => 'cloud-security-architecture-study-notes',
                'category' => 'learning',
                'difficulty' => 'Advanced',
                'status' => 'scheduled',
                'points' => 65,
                'summary' => 'Advanced study material for the Cloud Security exam. Covers shared responsibility, IAM, encryption, and compliance.',
                'details' => 'Architecture diagrams, service comparison tables, and real-world case studies of cloud breaches.',
                'target_total' => 1,
                'sort_order' => 7,
            ],
            [
                'title' => 'SOC Analyst Skills — Workbook',
                'slug' => 'soc-analyst-skills-workbook',
                'category' => 'learning',
                'difficulty' => 'Intermediate',
                'status' => 'scheduled',
                'points' => 75,
                'summary' => 'Hands-on workbook for the SOC Analyst Skills Test. Covers alert triage, log analysis, escalation procedures, and reporting.',
                'details' => 'SIEM query examples, sample alert scenarios, shift handoff templates, and metric tracking sheets.',
                'target_total' => 1,
                'sort_order' => 8,
            ],
        ];

        foreach ($resources as $resource) {
            ClubResource::query()->updateOrCreate(
                ['slug' => $resource['slug']],
                $resource,
            );
        }
    }
}
