<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingRecurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'pattern',
        'interval',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'ends_at' => 'date',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function isWeekly(): bool
    {
        return $this->pattern === 'weekly';
    }

    public function isBiweekly(): bool
    {
        return $this->pattern === 'biweekly';
    }

    public function isMonthly(): bool
    {
        return $this->pattern === 'monthly';
    }
}
