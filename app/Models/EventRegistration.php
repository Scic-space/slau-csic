<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'rsvp_status',
        'registered_at',
        'waitlisted_at',
        'attended_at',
        'notes',
        'custom_fields',
        'payment_completed',
        'check_in_code',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'waitlisted_at' => 'datetime',
            'attended_at' => 'datetime',
            'custom_fields' => 'array',
            'payment_completed' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (! $registration->check_in_code) {
                $registration->check_in_code = strtoupper(Str::random(12));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasAttended(): bool
    {
        return ! is_null($this->attended_at);
    }

    public function isWaitlisted(): bool
    {
        return $this->status === 'waitlist';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isAttending(): bool
    {
        return $this->rsvp_status === 'attending';
    }

    public function isNotAttending(): bool
    {
        return $this->rsvp_status === 'not_attending';
    }
}
