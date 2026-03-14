<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Account
 *
 * Represents a financial account or wallet where funds are kept. Each account belongs to a user
 * and can be of type bank, momo, cash, or other. The balance is derived from opening balance and loans.
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
     * Loans given from this account.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Ensure the user has at least one account; create a default "Main account" if none exist.
     * Call this before loading accounts for income/expense forms so dropdowns are never empty.
     */
    public static function ensureDefaultForUser(int $userId): void
    {
        if (static::where('user_id', $userId)->exists()) {
            return;
        }
        static::create([
            'user_id' => $userId,
            'name' => 'Main account',
            'type' => 'other',
            'opening_balance' => 0,
        ]);
    }

    /**
     * Calculate the current balance from opening balance and loans (given/returned).
     */
    public function getBalanceAttribute(): float
    {
        $loansGiven = $this->loans()->where('status', 'borrowed')->sum('amount');
        $loansReturned = $this->loans()->where('status', 'returned')->sum('returned_amount');
        return (float) ($this->opening_balance + $loansReturned - $loansGiven);
    }
}
