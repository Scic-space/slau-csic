<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Training extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($training) {
            if (empty($training->slug)) {
                $training->slug = Str::slug($training->title);
            }
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'difficulty',
        'objectives',
        'prerequisites',
        'duration_hours',
        'thumbnail',
        'resources',
        'max_enrollments',
        'is_published',
        'available_from',
        'available_until',
        'instructor_id',
    ];

    protected function casts(): array
    {
        return [
            'available_from' => 'datetime',
            'available_until' => 'datetime',
            'resources' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(TrainingModule::class)->ordered();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function getEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getCompletedCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->max_enrollments && $this->enrollment_count >= $this->max_enrollments;
    }
}
