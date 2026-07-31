<?php

namespace App\Models;

use App\Notifications\WriteupReviewedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CtfWriteup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ctf_challenge_id',
        'user_id',
        'content',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reviewed_by'])
            ->logOnlyDirty();
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function approve($reviewer)
    {
        $this->status = 'approved';
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = now();
        $this->save();

        try {
            $this->user->notify(new WriteupReviewedNotification($this));
        } catch (\Exception $e) {
            Log::error('Failed to send writeup review notification: '.$e->getMessage());
        }
    }

    public function reject($reviewer)
    {
        $this->status = 'rejected';
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = now();
        $this->save();

        try {
            $this->user->notify(new WriteupReviewedNotification($this));
        } catch (\Exception $e) {
            Log::error('Failed to send writeup review notification: '.$e->getMessage());
        }
    }
}
