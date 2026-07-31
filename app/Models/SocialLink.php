<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLink extends Model
{
    protected $fillable = [
        'user_id',
        'github_username',
        'linkedin_url',
        'discord_username',
        'is_discord_member',
    ];

    protected function casts(): array
    {
        return [
            'is_discord_member' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
