<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendance extends Model
{
    use HasFactory;

    protected $table = 'event_attendance';

    protected $fillable = [
        'event_id',
        'member_id',
        'status',
        'checked_in_at',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'recorded_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
