<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrivacy extends Model
{
    protected $fillable = [
        'user_id',
        'show_email',
        'show_phone',
        'show_discord',
        'show_attendance',
        'show_program',
        'show_year',
        'show_profile',
        'allow_contact',
    ];

    protected function casts(): array
    {
        return [
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'show_discord' => 'boolean',
            'show_attendance' => 'boolean',
            'show_program' => 'boolean',
            'show_year' => 'boolean',
            'show_profile' => 'boolean',
            'allow_contact' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
