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
        'month',
        'amount',
    ];

    /**
     * User owning this budget.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate actual spent amount for this budget and month (from transactions ledger).
     */
    public function getSpentAttribute(): float
    {
        $year = substr($this->month, 0, 4);
        $month = substr($this->month, 5, 2);
        return (float) \App\Models\Transaction::where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
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
