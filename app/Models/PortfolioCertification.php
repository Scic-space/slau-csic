<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioCertification extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'issuer',
        'date_earned',
        'expiry_date',
        'credential_url',
        'credential_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'date_earned' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date !== null
            && ! $this->expiry_date->isPast()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public static function commonCertifications(): array
    {
        return [
            'CompTIA Security+',
            'CompTIA Network+',
            'CompTIA CySA+',
            'CEH (Certified Ethical Hacker)',
            'OSCP (Offensive Security Certified Professional)',
            'CISSP',
            'AWS Certified Security - Specialty',
            'Google Cybersecurity Certificate',
            'Microsoft SC-900',
            'Cisco CCNA Security',
        ];
    }
}
