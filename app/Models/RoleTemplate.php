<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'required_skills',
        'min_experience',
        'availability_requirement',
        'approval_route',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function assignmentRoles(): HasMany
    {
        return $this->hasMany(AssignmentRole::class);
    }
}
