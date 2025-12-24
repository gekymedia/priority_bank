<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Account
 *
 * Represents a financial account or wallet where funds are kept. Each account belongs to a user
 * and can be of type bank, momo, cash, or other. The balance is not stored explicitly; instead
 * it can be derived from related incomes, expenses and loans.
 */
class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'opening_balance',
    ];

    /**
     * The user that owns the account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Incomes posted to this account.
     */
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Expenses posted to this account.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Loans given from this account.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Calculate the current balance by summing incomes and subtracting expenses and loans.
     */
    public function getBalanceAttribute(): float
    {
        $income = $this->incomes()->sum('amount');
        $expense = $this->expenses()->sum('amount');
        // Loans given reduce balance but when returned the returned_amount should be added back.
        $loansGiven = $this->loans()->where('status', 'borrowed')->sum('amount');
        $loansReturned = $this->loans()->where('status', 'returned')->sum('returned_amount');
        return (float) ($this->opening_balance + $income + $loansReturned - $expense - $loansGiven);
    }
}
