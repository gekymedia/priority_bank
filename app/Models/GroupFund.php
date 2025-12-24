<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupFund extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'total_available',
        'total_loaned',
        'total_savings',
        'last_updated',
        'fund_breakdown',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_updated' => 'date',
        'total_available' => 'decimal:2',
        'total_loaned' => 'decimal:2',
        'total_savings' => 'decimal:2',
        'fund_breakdown' => 'array',
    ];

    /**
     * Get the available funds for loans.
     */
    public function getAvailableForLoansAttribute()
    {
        return $this->total_available - $this->total_loaned;
    }

    /**
     * Update the fund totals.
     */
    public function updateTotals()
    {
        $this->total_savings = \App\Models\Saving::where('status', 'available')->sum('amount');
        $this->total_loaned = \App\Models\Loan::where('is_credit_union_loan', true)
                                              ->where('status', 'borrowed')
                                              ->sum('amount');
        $this->total_available = $this->total_savings;
        $this->last_updated = now();
        $this->save();
    }

    /**
     * Get the singleton instance of group funds.
     */
    public static function getInstance()
    {
        return static::firstOrCreate([]);
    }
}
