<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\LoanRequest;
use App\Models\InterestRate;
use App\Models\Payment;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'borrower_name',
        'borrower_phone',
        'amount',
        'date_given',
        'expected_return_date',
        'status',
        'returned_amount',
        'channel',
        'account_id',
        'notes',
        'loan_request_id',
        'interest_rate_id',
        'interest_rate_applied',
        'total_amount_with_interest',
        'remaining_balance',
        'disbursement_date',
        'loan_type',
        'is_group_loan',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'date_given' => 'date',
        'expected_return_date' => 'date',
        'disbursement_date' => 'date',
        'amount' => 'decimal:2',
        'returned_amount' => 'decimal:2',
        'interest_rate_applied' => 'decimal:2',
        'total_amount_with_interest' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'is_group_loan' => 'boolean',
    ];

    /**
     * The user that owns the loan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The account from which the loan was given.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * The loan request this loan was created from.
     */
    public function loanRequest()
    {
        return $this->belongsTo(LoanRequest::class);
    }

    /**
     * The interest rate applied to this loan.
     */
    public function interestRate()
    {
        return $this->belongsTo(InterestRate::class);
    }

    /**
     * The payments made for this loan.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate the total amount paid for this loan.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Calculate remaining balance.
     */
    public function getCalculatedRemainingBalanceAttribute()
    {
        if ($this->is_group_loan) {
            return max(0, ($this->total_amount_with_interest ?? $this->amount) - $this->total_paid);
        }
        return $this->amount - $this->returned_amount;
    }

    /**
     * Update remaining balance after payment.
     */
    public function updateRemainingBalance()
    {
        if ($this->is_group_loan) {
            $this->remaining_balance = $this->calculated_remaining_balance;
            $this->save();

            // Check if loan is fully paid
            if ($this->remaining_balance <= 0) {
                $this->status = 'returned';
                $this->save();

                // Update group funds
                $groupFund = \App\Models\GroupFund::getInstance();
                $groupFund->updateTotals();
            }
        }
    }

    /**
     * Make a payment towards this loan.
     */
    public function makePayment($amount, $paymentMethod = 'manual', $notes = null)
    {
        $payment = $this->payments()->create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'payment_date' => now(),
            'notes' => $notes,
        ]);

        // Calculate interest and principal portions
        $totalOwed = $this->remaining_balance;
        $interestPortion = min($amount * 0.3, $totalOwed * 0.5); // Assume 30% interest or 50% of remaining, whichever is smaller
        $principalPortion = $amount - $interestPortion;

        $payment->update([
            'interest_amount' => $interestPortion,
            'principal_amount' => $principalPortion,
        ]);

        // Update loan balance
        $this->updateRemainingBalance();

        return $payment;
    }

    /**
     * Check if loan is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Scope for credit union loans.
     */
    public function scopeCreditUnionLoans($query)
    {
        return $query->where('is_group_loan', true);
    }

    /**
     * Scope for active loans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'borrowed');
    }
}
