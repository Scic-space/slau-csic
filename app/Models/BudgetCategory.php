<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'allocated_amount',
        'semester',
        'academic_year',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, BudgetAllocation::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'UGX '.number_format($this->allocated_amount, 0);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getIncomeCategories(): array
    {
        return [
            'Membership Dues',
            'Donations',
            'Sponsorships',
            'Fundraising',
            'Other Income',
        ];
    }

    public static function getExpenseCategories(): array
    {
        return [
            'Events',
            'Equipment',
            'Prizes',
            'Refreshments',
            'Printing',
            'Travel',
            'Other Expense',
        ];
    }
}
