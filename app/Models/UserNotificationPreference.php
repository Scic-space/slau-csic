<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'event_reminders',
        'event_cancellations',
        'challenge_solved',
        'membership_updates',
        'broadcast_messages',
        'fine_notifications',
        'weekly_digest',
    ];

    protected function casts(): array
    {
        return [
            'event_reminders' => 'boolean',
            'event_cancellations' => 'boolean',
            'challenge_solved' => 'boolean',
            'membership_updates' => 'boolean',
            'broadcast_messages' => 'boolean',
            'fine_notifications' => 'boolean',
            'weekly_digest' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
