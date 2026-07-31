<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_category_id',
        'amount',
        'semester',
        'academic_year',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->amount, 0);
    }

    public function getSpentAmountAttribute(): float
    {
        return $this->transactions()->approved()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->spent_amount;
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->spent_amount / $this->amount) * 100;
    }
}
