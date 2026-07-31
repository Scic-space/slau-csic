<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubResource>
 */
class ClubResourceFactory extends Factory
{
    /** @var list<string> */
    private static array $titles = [
        'SLAU Internal Red Team Sprint',
        'Campus Blue Team Readiness Drill',
        'Web Exploitation Practice Lab',
        'Cryptography Fundamentals Challenge',
        'Network Scanning and Reconnaissance',
        'Incident Response Simulation',
        'Malware Analysis Workshop',
        'Security Operations Center Drill',
        'Penetration Testing Fundamentals',
        'Digital Forensics Investigation',
        'Social Engineering Defense',
        'Secure Code Review Practice',
        'Cloud Security Architecture Lab',
        'Threat Hunting Exercise',
        'Vulnerability Assessment Lab',
    ];

    /** @var list<string> */
    private static array $summaries = [
        'A timed internal offensive security challenge built around reconnaissance, exploitation, and reporting discipline.',
        'A defensive practice cycle focused on alert review, triage workflow, and incident reporting habits.',
        'Hands-on web application security practice covering SQL injection, XSS, CSRF, and authentication bypasses.',
        'A cryptography challenge series exploring encryption, hashing, and digital signature fundamentals.',
        'Practical network scanning exercises using Nmap, Wireshark, and other industry-standard tools.',
        'A realistic incident response scenario requiring containment, eradication, and recovery steps.',
        'Learn to analyze suspicious binaries and identify malicious indicators using sandbox environments.',
        'Team-based SOC drill simulating real-world threat detection and escalation procedures.',
        'A structured lab environment for practicing penetration testing methodology on vulnerable targets.',
        'Digital forensics case study covering disk imaging, file carving, and timeline analysis.',
        'Learn to identify and defend against social engineering tactics including phishing and pretexting.',
        'Review vulnerable code samples across multiple languages and identify security flaws.',
        'Design and implement secure cloud infrastructure following AWS and Azure best practices.',
        'Proactive threat hunting exercise using log analysis and endpoint detection tools.',
        'Systematic vulnerability assessment lab using industry-standard scanning and reporting workflows.',
    ];

    /** @var list<string> */
    private static array $details = [
        'Members work in teams to complete a series of escalating challenges. Each team submits findings through the platform and is scored on methodology, impact, and communication quality. The top teams receive recognition at the next club meeting.',
        'This drill simulates a real security operations center environment where participants triage alerts, investigate potential threats, and document their findings. Ideal for members transitioning from competition participation into practical security operations.',
        'This lab covers the OWASP Top 10 vulnerabilities with practical exercises. Each module includes a vulnerable web application, guided exploitation steps, and remediation strategies. Complete all modules to earn the Web Security badge.',
        'Topics include symmetric and asymmetric encryption, hash functions, digital signatures, and certificate authorities. Each challenge increases in difficulty, building toward a final capstone exercise that combines multiple techniques.',
        'Exercises cover network discovery, port scanning, service enumeration, and traffic analysis. Participants learn to identify open ports, running services, and potential vulnerabilities while minimizing detection.',
        'The scenario involves a simulated data breach. Participants must contain the incident, preserve evidence, eradicate the threat, and restore normal operations. A comprehensive after-action report is required for completion.',
        'Using a sandboxed analysis environment, participants examine suspicious files, identify indicators of compromise, and classify malware families. Prior programming experience is helpful but not required.',
        'Alerts are generated from a simulated enterprise network. Teams must classify each alert, determine its severity, and follow established escalation procedures. Performance is tracked through response time and accuracy metrics.',
        'Work through a structured penetration testing methodology including reconnaissance, scanning, exploitation, and post-exploitation. Each phase includes guided exercises and independent challenges to test your skills.',
        'The forensic investigation follows a data exfiltration incident. Participants analyze disk images, recover deleted files, reconstruct timelines, and produce a formal forensic report suitable for legal proceedings.',
        'This module covers the human element of security. Topics include phishing simulation, pretexting scenarios, tailgating prevention, and building a security awareness program for your organization.',
        'Review code samples in PHP, Python, JavaScript, and Java for common security vulnerabilities. Each exercise requires identifying the flaw, explaining the impact, and implementing the correct fix.',
        'Design a secure multi-tier cloud architecture following the principle of least privilege. Exercises include configuring IAM policies, network ACLs, encryption at rest and in transit, and monitoring setup.',
        'Using provided log data from SIEM and EDR tools, hunt for signs of compromise. Develop hypotheses, create search queries, and document your findings in a threat hunting report.',
        'Conduct a vulnerability assessment against a target network using Nessus, OpenVAS, or similar tools. Prioritize findings by risk, create remediation recommendations, and present your findings.',
    ];

    public function definition(): array
    {
        $title = fake()->unique()->randomElement(self::$titles);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => 'competition',
            'platform' => 'Internal',
            'difficulty' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'status' => fake()->randomElement(['open', 'scheduled', 'active']),
            'location' => fake()->randomElement(['SLAU Lab', 'Online', 'Hybrid']),
            'cta_label' => fake()->randomElement(['View competition brief', 'Prepare for drill', 'Start lab', 'Open challenge']),
            'external_url' => null,
            'summary' => fake()->unique()->randomElement(self::$summaries),
            'details' => fake()->unique()->randomElement(self::$details),
            'target_total' => fake()->numberBetween(3, 10),
            'points' => fake()->numberBetween(50, 200),
            'starts_at' => now()->addDays(fake()->numberBetween(-10, 20)),
            'ends_at' => now()->addDays(fake()->numberBetween(21, 60)),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
