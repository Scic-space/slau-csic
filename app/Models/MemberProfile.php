<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'phone',
        'program',
        'faculty',
        'year_of_study',
        'date_of_birth',
        'gender',
        'residence',
        'headline',
        'bio',
        'emergency_contact_name',
        'emergency_contact_phone',
        'profile_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'year_of_study' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
