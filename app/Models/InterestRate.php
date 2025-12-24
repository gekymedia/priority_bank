<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'rate_percentage',
        'type',
        'is_active',
        'effective_from',
        'effective_to',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'rate_percentage' => 'decimal:2',
    ];

    /**
     * Get loans using this interest rate.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Check if the rate is currently active.
     */
    public function isCurrentlyActive()
    {
        return $this->is_active &&
               $this->effective_from <= now() &&
               ($this->effective_to === null || $this->effective_to >= now());
    }

    /**
     * Scope for active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', now());
                    });
    }

    /**
     * Scope for loan interest rates.
     */
    public function scopeForLoans($query)
    {
        return $query->where('type', 'loan_interest');
    }

    /**
     * Scope for savings interest rates.
     */
    public function scopeForSavings($query)
    {
        return $query->where('type', 'savings_interest');
    }

    /**
     * Calculate interest amount for a given principal and period.
     */
    public function calculateInterest($principal, $days = 30)
    {
        // Simple interest calculation: (Principal * Rate * Time) / 100
        // Assuming monthly rate for simplicity
        $monthlyRate = $this->rate_percentage / 100;
        return ($principal * $monthlyRate * ($days / 30));
    }
}
