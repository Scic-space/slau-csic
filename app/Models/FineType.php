<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FineType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'default_amount',
        'description',
        'auto_apply_trigger',
        'auto_apply_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'auto_apply_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'default_amount', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->default_amount, 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public static function getAutoApplyTriggers(): array
    {
        return [
            'missed_meetings' => 'Missed Meetings',
            'event_no_show' => 'Event No-Show',
            'late_submission' => 'Late Project Submission',
            'lab_violation' => 'Lab Violation',
        ];
    }
}
