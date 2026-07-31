<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'date',
        'description',
        'receipt_path',
        'paid_to_from',
        'payment_method',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'created_by',
        'is_recurring',
        'recurring_frequency',
        'recurring_interval',
        'recurring_end_date',
        'recurring_last_generated',
        'recurring_next_date',
        'recurring_parent_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'requires_approval' => 'boolean',
            'approved_at' => 'datetime',
            'is_recurring' => 'boolean',
            'recurring_interval' => 'integer',
            'recurring_end_date' => 'date',
            'recurring_last_generated' => 'date',
            'recurring_next_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'category', 'amount', 'status', 'approved_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->amount, 0);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
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

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeDueRecurring($query)
    {
        return $query->where('is_recurring', true)
            ->whereNotNull('recurring_next_date')
            ->where('recurring_next_date', '<=', now());
    }

    public function recurringParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurring_parent_id');
    }

    public function recurringChildren(): HasMany
    {
        return $this->hasMany(self::class, 'recurring_parent_id');
    }

    public function calculateNextDate(): ?Carbon
    {
        if (! $this->is_recurring || ! $this->recurring_frequency) {
            return null;
        }

        $base = $this->recurring_last_generated ?? $this->date ?? now();
        $interval = $this->recurring_interval ?? 1;

        return match ($this->recurring_frequency) {
            'daily' => Carbon::parse($base)->addDays($interval),
            'weekly' => Carbon::parse($base)->addWeeks($interval),
            'monthly' => Carbon::parse($base)->addMonths($interval),
            'yearly' => Carbon::parse($base)->addYears($interval),
            default => Carbon::parse($base)->addMonth(),
        };
    }

    public function approve(User $approver, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'description' => $notes ? $notes.' | '.$this->description : $this->description,
        ]);
    }

    public function reject(User $rejecter, ?string $reason = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $rejecter->id,
            'approved_at' => now(),
            'description' => $reason ? $reason.' | '.$this->description : $this->description,
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            $transaction->requires_approval = $transaction->amount > 100;
        });
    }
}
