<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Budget
 *
 * Represents a monthly budget for a specific expense category. Each user can
 * specify a budget amount per category and month. This model also computes
 * actual spending and remaining amount via accessors.
 */
class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expense_category_id',
        'month',
        'amount',
    ];

    /**
     * Category this budget applies to.
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * User owning this budget.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate actual spent amount for this budget and month.
     */
    public function getSpentAttribute(): float
    {
        return (float) $this->user->expenses()
            ->where('expense_category_id', $this->expense_category_id)
            ->whereMonth('date', substr($this->month, 5, 2))
            ->whereYear('date', substr($this->month, 0, 4))
            ->sum('amount');
    }

    /**
     * Remaining budget amount.
     */
    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->spent);
    }

    /**
     * Percentage of budget used.
     */
    public function getUsedPercentageAttribute(): float
    {
        return $this->amount > 0 ? round(($this->spent / $this->amount) * 100, 2) : 0;
    }
}
