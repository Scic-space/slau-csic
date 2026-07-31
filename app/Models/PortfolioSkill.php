<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSkill extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'proficiency',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function categories(): array
    {
        return ['offensive', 'defensive', 'forensics', 'networking', 'programming', 'tools', 'general'];
    }

    public static function skillSuggestions(): array
    {
        return [
            'offensive' => ['Penetration Testing', 'Web Application Security', 'SQL Injection', 'XSS Exploitation', 'Privilege Escalation', 'Buffer Overflow', 'Metasploit', 'Burp Suite', 'OWASP Top 10'],
            'defensive' => ['Incident Response', 'Log Analysis', 'SIEM (Splunk/ELK)', 'Firewall Management', 'Vulnerability Assessment', 'Hardening', 'Malware Analysis'],
            'forensics' => ['Digital Forensics', 'Memory Forensics', 'Network Forensics', 'Disk Forensics', 'Chain of Custody', 'Autopsy', 'Volatility', 'FTK Imager'],
            'networking' => ['TCP/IP', 'Wireshark', 'Nmap', 'Packet Analysis', 'DNS', 'VPN Configuration', 'Network Segmentation'],
            'programming' => ['Python', 'Bash Scripting', 'PowerShell', 'C/C++', 'JavaScript', 'SQL', 'Go', 'Rust'],
            'tools' => ['Kali Linux', 'Nmap', 'Wireshark', 'John the Ripper', 'Hashcat', 'Aircrack-ng', 'Ghidra', 'IDA Pro', 'Nessus'],
            'general' => ['OSINT', 'Social Engineering', 'CTF Competitions', 'Security+ Concepts', 'Risk Assessment', 'Compliance (ISO 27001)'],
        ];
    }
}
