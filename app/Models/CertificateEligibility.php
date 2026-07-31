<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CertificateEligibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'user_id',
        'exam_id',
        'eligible',
        'notes',
        'verification_code',
    ];

    protected function casts(): array
    {
        return [
            'eligible' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CertificateEligibility $eligibility) {
            if (empty($eligibility->verification_code)) {
                $eligibility->verification_code = Str::uuid()->toString();
            }
        });
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function scopeEligible($query)
    {
        return $query->where('eligible', true);
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', $this->verification_code);
    }

    public function getCertificateIdAttribute(): string
    {
        return 'CERT-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
