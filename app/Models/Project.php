<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project): void {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'objectives',
        'type',
        'status',
        'start_date',
        'end_date',
        'actual_completion_date',
        'repository_url',
        'documentation_url',
        'progress_percentage',
        'lead_id',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_completion_date' => 'date',
            'tags' => 'array',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function memberships()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'joined_at', 'left_at', 'contribution'])
            ->withTimestamps();
    }
}
