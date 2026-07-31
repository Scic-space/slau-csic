<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Fine extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'fine_type_id',
        'amount',
        'reason',
        'issue_date',
        'due_date',
        'status',
        'amount_paid',
        'issued_by',
        'waived_by',
        'waived_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fineType(): BelongsTo
    {
        return $this->belongsTo(FineType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinePayment::class);
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(FineAppeal::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount_paid', 'waived_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->amount, 0);
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return 'UGX '.number_format($this->amount_paid, 0);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'UGX '.number_format($this->balance, 0);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->diffInDays(now()) <= 3;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    public function scopeDueSoon($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<=', now()->addDays(3))
            ->where('due_date', '>=', now());
    }

    public function recordPayment(float $amount, string $paymentMethod, ?string $receiptNumber = null, ?string $notes = null): FinePayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $paymentMethod,
            'receipt_number' => $receiptNumber,
            'recorded_by' => auth()->id(),
            'notes' => $notes,
        ]);

        $newAmountPaid = $this->amount_paid + $amount;
        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newAmountPaid >= $this->amount ? 'paid' : 'partially_paid',
        ]);

        return $payment;
    }

    public function waive(?User $waivedBy = null, ?string $reason = null): bool
    {
        return $this->update([
            'status' => 'waived',
            'waived_by' => $waivedBy?->id ?? auth()->id(),
            'waived_reason' => $reason,
        ]);
    }

    public function canBeAppealed(): bool
    {
        return $this->status === 'pending' &&
               $this->issue_date->diffInDays(now()) <= 7 &&
               ! $this->appeals()->where('status', 'pending')->exists();
    }

    public function hasPendingAppeal(): bool
    {
        return $this->appeals()->where('status', 'pending')->exists();
    }
}
