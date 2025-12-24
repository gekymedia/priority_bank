<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'loan_id',
        'amount',
        'interest_amount',
        'principal_amount',
        'payment_method',
        'transaction_reference',
        'status',
        'payment_date',
        'payment_gateway_response',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'payment_gateway_response' => 'array',
    ];

    /**
     * Get the user that made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the loan this payment is for.
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get total payments made for a loan.
     */
    public function scopeTotalPaidForLoan($query, $loanId)
    {
        return $query->where('loan_id', $loanId)
                    ->where('status', 'completed')
                    ->sum('amount');
    }
}
