<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Account;
use App\Models\Budget;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Saving;
use App\Models\LoanRequest;
use App\Models\Payment;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'phone',
        'preferred_currency',
        'notification_email',
        'notification_browser',
        'theme',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_email' => 'boolean',
            'notification_browser' => 'boolean',
        ];
    }

    /**
     * Get all incomes for the user.
     */
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Get all expenses for the user.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get all loans for the user.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get all accounts for the user.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get all budgets for the user.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get income categories defined by user.
     */
    public function incomeCategories()
    {
        return $this->hasMany(IncomeCategory::class);
    }

    /**
     * Get expense categories defined by user.
     */
    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get all savings for the user.
     */
    public function savings()
    {
        return $this->hasMany(Saving::class);
    }

    /**
     * Get all loan requests for the user.
     */
    public function loanRequests()
    {
        return $this->hasMany(LoanRequest::class);
    }

    /**
     * Get all payments made by the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user's available savings balance.
     */
    public function getSavingsBalanceAttribute()
    {
        return $this->savings()->where('status', 'available')->sum('amount');
    }

    /**
     * Get the user's outstanding loan balance.
     */
    public function getLoanBalanceAttribute()
    {
        return $this->loans()->where('is_group_loan', true)
                            ->where('status', 'borrowed')
                            ->sum('remaining_balance');
    }


    /**
     * Get detailed balance breakdown.
     */
    public function getBalanceBreakdownAttribute()
    {
        $savings = $this->savings_balance;
        $loans = $this->loan_balance;
        $net = $this->net_balance;

        return [
            'savings' => $savings,
            'outstanding_loans' => $loans,
            'net_balance' => $net,
            'balance_type' => $net >= 0 ? 'credit' : 'debit',
            'formatted_savings' => 'GHS ' . number_format($savings, 2),
            'formatted_loans' => 'GHS ' . number_format($loans, 2),
            'formatted_net' => 'GHS ' . number_format(abs($net), 2),
        ];
    }

    /**
     * Get user's credit union transaction history.
     */
    public function getCreditUnionHistory($limit = 20)
    {
        $savings = $this->savings()->selectRaw("'saving' as type, id, amount, deposit_date as date, status, notes, null as loan_id, null as payment_method")
                               ->get();

        $loans = $this->loans()->where('is_group_loan', true)
                              ->selectRaw("'loan' as type, id, amount, disbursement_date as date, status, notes, null as loan_id, null as payment_method")
                              ->get();

        $payments = $this->payments()->selectRaw("'payment' as type, id, amount, payment_date as date, status, notes, loan_id, payment_method")
                                    ->get();

        return collect()
            ->merge($savings)
            ->merge($loans)
            ->merge($payments)
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can request a loan.
     */
    public function canRequestLoan($amount)
    {
        $groupFund = \App\Models\GroupFund::getInstance();
        return $amount <= $groupFund->available_for_loans;
    }

    /**
     * Get user's loan eligibility.
     */
    public function getLoanEligibilityAttribute()
    {
        $groupFund = \App\Models\GroupFund::getInstance();

        return [
            'can_request_loan' => $groupFund->available_for_loans > 0,
            'available_amount' => $groupFund->available_for_loans,
            'has_pending_request' => $this->loanRequests()->where('status', 'pending')->exists(),
            'current_requests' => $this->loanRequests()->whereIn('status', ['pending', 'approved'])->count(),
        ];
    }
}
