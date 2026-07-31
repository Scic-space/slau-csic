<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'start_date',
        'end_date',
        'location',
        'website_url',
        'is_team_based',
        'max_team_size',
        'participation_status',
        'club_ranking',
        'achievements',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_team_based' => 'boolean',
        ];
    }

    public function challenges()
    {
        return $this->hasMany(Challenge::class)->orderBy('sort_order');
    }

    public function participants()
    {
        return $this->hasMany(CompetitionParticipants::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'competition_participants')
            ->withPivot(['team_name', 'role'])
            ->withTimestamps();
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now())
            ->orderBy('start_date');
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('start_date', '<=', now())
            ->where(function (Builder $q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeRanked(Builder $query): Builder
    {
        return $query->whereNotNull('club_ranking')
            ->orderBy('club_ranking');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function isUserParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function getParticipantForUser(User $user): ?CompetitionParticipants
    {
        return $this->participants()->where('user_id', $user->id)->first();
    }

    public function isUpcoming(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    public function isOngoing(): bool
    {
        return $this->start_date && $this->start_date->isPast()
            && (! $this->end_date || $this->end_date->isFuture());
    }

    public function isPast(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function statusLabel(): string
    {
        if ($this->isPast()) {
            return 'Past';
        }

        if ($this->isOngoing()) {
            return 'Ongoing';
        }

        return 'Upcoming';
    }

    public function statusColor(): string
    {
        if ($this->isPast()) {
            return 'gray';
        }

        if ($this->isOngoing()) {
            return 'success';
        }

        return 'indigo';
    }
}
