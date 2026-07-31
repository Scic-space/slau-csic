<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FinePayment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fine_id',
        'amount',
        'payment_date',
        'payment_method',
        'receipt_number',
        'receipt_path',
        'recorded_by',
        'submitted_by',
        'notes',
        'status',
        'confirmed_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'payment_method', 'receipt_number'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->amount, 0);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? asset('storage/'.$this->receipt_path) : null;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRecorded(): bool
    {
        return $this->status === 'recorded';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeRecorded($query)
    {
        return $query->where('status', 'recorded');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function confirm(?User $confirmedBy = null): void
    {
        $this->update([
            'status' => 'confirmed',
            'recorded_by' => $confirmedBy?->id ?? auth()->id(),
            'confirmed_at' => now(),
        ]);

        $fine = $this->fine;
        $newAmountPaid = $fine->amount_paid + $this->amount;
        $fine->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newAmountPaid >= $fine->amount ? 'paid' : 'partially_paid',
        ]);
    }

    public function reject(?string $reason = null, ?User $rejectedBy = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'recorded_by' => $rejectedBy?->id ?? auth()->id(),
        ]);
    }

    public static function getPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'check' => 'Check',
            'card' => 'Card',
            'transfer' => 'Bank Transfer',
            'other' => 'Other',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'recorded' => 'Recorded',
            'pending' => 'Pending Review',
            'confirmed' => 'Confirmed',
            'rejected' => 'Rejected',
        ];
    }
}
