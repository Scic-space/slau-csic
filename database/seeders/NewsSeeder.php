<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::truncate();

        $admin = User::where('email', 'kevinssali23@gmail.com')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first()
            ?? User::first();

        $articles = [
            [
                'title' => 'Critical Zero-Day in Palo Alto PAN-OS Exploited in the Wild',
                'excerpt' => 'A critical zero-day vulnerability (CVE-2026-1337) in Palo Alto Networks PAN-OS has been actively exploited by a Chinese threat actor group targeting government networks across Southeast Asia.',
                'content' => '<p>A critical zero-day vulnerability in Palo Alto Networks PAN-OS firewalls has been actively exploited in the wild, prompting an emergency patch from the vendor.</p>
<h2>What Happened</h2>
<p>Security researchers at Volexity discovered that a sophisticated threat actor — tracked as <strong>UNC5221</strong> — was exploiting a previously unknown vulnerability in the management interface of PAN-OS firewalls. The flaw allows unauthenticated remote code execution on affected devices.</p>
<h2>Impact</h2>
<p>The vulnerability affects PAN-OS versions 11.2.x through 11.4.x. Organizations running affected versions are urged to apply the hotfix immediately. Palo Alto has confirmed that the vulnerability is being exploited by a state-sponsored group with ties to China.</p>
<h2>Recommended Actions</h2>
<ul><li>Apply the PAN-OS hotfix immediately</li><li>Restrict access to management interfaces</li><li>Review firewall logs for suspicious activity</li><li>Check for indicators of compromise published by Volexity</li></ul>
<p>This is a developing story. We will update this article as more information becomes available.</p>',
                'category' => 'threat_intel',
                'content_type' => 'article',
                'source_name' => 'The Hacker News',
                'source_url' => 'https://thehackernews.com/example',
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subHours(2),
            ],
            [
                'title' => 'CrowdStrike 2026 Global Threat Report: Key Takeaways',
                'excerpt' => 'CrowdStrike annual report reveals a 75% increase in cloud-targeted attacks and the rise of AI-powered social engineering campaigns across all sectors.',
                'content' => '<p>CrowdStrike has released its annual Global Threat Report, and the findings paint a concerning picture of the evolving cyber threat landscape.</p>
<h2>Key Findings</h2>
<p>The report highlights several major trends:</p>
<ul><li><strong>Cloud attacks surged 75%</strong> — adversaries are increasingly targeting cloud environments with identity-based attacks</li><li><strong>AI-powered phishing</strong> — threat actors are using large language models to craft highly convincing social engineering campaigns</li><li><strong>Identity-based attacks doubled</strong> — credential theft and lateral movement remain the primary attack vectors</li></ul>
<h2>Top Threat Actors</h2>
<p>The report identifies nation-state groups from China, Russia, Iran, and North Korea as the most active threat actors, with China-nexus groups focusing on intellectual property theft in the technology sector.</p>
<h2>Recommendations</h2>
<p>Organizations should prioritize identity security, implement zero-trust architectures, and deploy AI-driven threat detection capabilities to stay ahead of evolving threats.</p>',
                'category' => 'industry',
                'content_type' => 'article',
                'source_name' => 'CrowdStrike',
                'source_url' => 'https://crowdstrike.com/example',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'MITRE Releases ATT&CK v15 with Cloud and Mobile Expansions',
                'excerpt' => 'The latest MITRE ATT&CK framework update introduces expanded cloud coverage, new mobile techniques, and improved detection guidance for defenders.',
                'content' => '<p>MITRE has released ATT&CK Framework version 15, bringing significant expansions to cloud and mobile technique coverage.</p>
<h2>What\'s New</h2>
<p>The update includes 47 new techniques across cloud, mobile, and enterprise matrices. Notable additions include:</p>
<ul><li>New techniques for container escape and Kubernetes-specific attacks</li><li>Expanded coverage of OAuth and token-based authentication abuse</li><li>New mobile techniques targeting Android and iOS spyware</li></ul>
<h2>Why It Matters</h2>
<p>The ATT&CK framework is the de facto standard for classifying adversary behavior. Updates directly influence how security vendors, SOCs, and threat intelligence teams categorize and respond to threats.</p>',
                'category' => 'tools_research',
                'content_type' => 'article',
                'source_name' => 'MITRE',
                'source_url' => 'https://attack.mitre.org/example',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'New CVE-2026-4091: Linux Kernel Privilege Escalation via eBPF',
                'excerpt' => 'A high-severity privilege escalation vulnerability in the Linux kernel eBPF subsystem allows local users to gain root on affected systems running kernel 6.8+.',
                'content' => '<p>A newly disclosed vulnerability in the Linux kernel\'s eBPF (extended Berkeley Packet Filter) subsystem allows local privilege escalation on systems running kernel versions 6.8 and above.</p>
<h2>Technical Details</h2>
<p>The vulnerability exists in the eBPF verifier, which fails to properly validate certain types of memory access patterns. An attacker with local access can craft a malicious eBPF program to bypass the verifier and achieve arbitrary kernel memory read/write.</p>
<h2>Affected Systems</h2>
<ul><li>Linux kernel 6.8 through 6.14.3</li><li>All major distributions (Ubuntu, Debian, RHEL, Fedora, Arch)</li><li>Containers sharing the host kernel are also vulnerable</li></ul>
<h2>Patches Available</h2>
<p>Patches have been released for all supported kernel versions. Distributors are rolling out updates. Users should apply patches immediately or disable unprivileged eBPF as a temporary workaround.</p>',
                'category' => 'vulnerabilities',
                'content_type' => 'article',
                'source_name' => 'NVD',
                'source_url' => 'https://nvd.nist.gov/example',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'NIST Updates Cybersecurity Framework to Version 2.1',
                'excerpt' => 'NIST CSF 2.1 adds new guidance on AI security, post-quantum cryptography readiness, and supply chain risk management for critical infrastructure.',
                'content' => '<p>The National Institute of Standards and Technology (NIST) has released version 2.1 of its Cybersecurity Framework (CSF), introducing significant updates to address emerging technology risks.</p>
<h2>Key Updates</h2>
<ul><li><strong>AI Security Controls</strong> — New guidance on securing AI/ML systems throughout their lifecycle</li><li><strong>Post-Quantum Cryptography</strong> — Updated recommendations for crypto-agility and quantum-safe transitions</li><li><strong>Supply Chain Risk</strong> — Enhanced guidance on third-party risk management and software bill of materials (SBOM)</li></ul>
<h2>Impact on Organizations</h2>
<p>While CSF adoption is voluntary, many regulatory frameworks reference NIST CSF. Organizations should review the updates and assess gaps in their current security programs.</p>',
                'category' => 'policy_compliance',
                'content_type' => 'article',
                'source_name' => 'NIST',
                'source_url' => 'https://nist.gov/example',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'DEF CON CTF 2024 Finals — Day 1 Commentary',
                'excerpt' => 'Watch the full Day 1 commentary from DEF CON 32\'s Capture The Flag finals — featuring innovative challenges in web exploitation, binary analysis, and AI security.',
                'content' => '<p>DEF CON 32 delivered one of the most competitive CTF finals in the event\'s history, with 12 teams from around the world battling it out across web, binary, crypto, and a new AI security category.</p>
<h2>Highlights</h2>
<p>The competition featured several notable moments:</p>
<ul><li><strong>AI Security Challenge</strong> — For the first time, teams had to exploit vulnerabilities in large language model deployments</li><li><strong>Binary Revolution</strong> — A novel ARM64 kernel exploitation challenge that stumped most teams until the final hours</li><li><strong>Web Speed</strong> — A multi-stage web challenge involving real-time protocol manipulation</li></ul>
<h2>Top Teams</h2>
<ol><li><strong>Maple Mallard Magistrates</strong> (CMU / UBC) — 5,943 points</li><li><strong>Blue Water</strong> (International) — 5,090 points</li><li><strong>SuperDiceCode</strong> (International) — 3,720 points</li></ol>
<p>Full writeups and challenge binaries were released on the Nautilus Institute GitHub in the weeks following the event.</p>',
                'category' => 'industry',
                'content_type' => 'video',
                'source_name' => 'DEF CON',
                'source_url' => 'https://www.youtube.com/watch?v=4Ndw2mALAC8',
                'video_url' => 'https://www.youtube.com/watch?v=4Ndw2mALAC8',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&h=400&fit=crop',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
        ];

        foreach ($articles as $data) {
            News::create(array_merge($data, [
                'created_by' => $admin?->id,
                'slug' => \Illuminate\Support\Str::slug($data['title']),
            ]));
        }
    }
}
