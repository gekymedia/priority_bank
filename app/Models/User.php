<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Loan;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Saving;
use App\Models\LoanRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\SystemRegistry;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

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
        'account_id',
        'preferred_currency',
        'notification_email',
        'notification_browser',
        'notification_sms',
        'notification_whatsapp',
        'notification_gekychat',
        'theme',
        'role',
        'type',
        'status',
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
            'notification_sms' => 'boolean',
            'notification_whatsapp' => 'boolean',
            'notification_gekychat' => 'boolean',
        ];
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
     * Get all transactions tagged to this user (e.g. admin-created bank transactions).
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Systems/accounts (main or subaccounts) owned by this user.
     * systems_registry.user_id = owner; used for "Linked to" on User Management.
     */
    public function ownedSystems()
    {
        return $this->hasMany(SystemRegistry::class, 'user_id');
    }

    /**
     * Get the user's available savings balance.
     * Includes: successful Saving deposits + income Transactions (e.g. admin-tagged Priority Bank credits).
     */
    public function getSavingsBalanceAttribute()
    {
        $fromSavings = $this->savings()->where('status', 'successful')->sum('amount');
        $fromTransactions = $this->transactions()->where('type', 'income')->sum('amount');
        return $fromSavings + $fromTransactions;
    }

    /**
     * Get the user's outstanding loan balance.
     * Includes: group loans (borrowed) + expense Transactions (e.g. admin-tagged Priority Bank debits/loans).
     */
    public function getLoanBalanceAttribute()
    {
        $fromLoans = $this->loans()->where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->sum('remaining_balance');
        $fromTransactions = $this->transactions()->where('type', 'expense')->sum('amount');
        return $fromLoans + $fromTransactions;
    }

    /**
     * Get the user's net balance (savings - loans).
     */
    public function getNetBalanceAttribute()
    {
        return $this->savings_balance - $this->loan_balance;
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
     * Check if user is a system account (not a real person).
     */
    public function isSystem()
    {
        return $this->type === 'system';
    }

    /**
     * Check if user is a real person (not a system account).
     */
    public function isRealUser()
    {
        return $this->type === 'user' || $this->type === null;
    }

    /**
     * Scope to only include real users (not system accounts).
     */
    public function scopeRealUsers($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'user')->orWhereNull('type');
        });
    }

    /**
     * Scope to only include system accounts.
     */
    public function scopeSystemAccounts($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Check if user is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if user is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
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
     * Total income records count (from transactions ledger).
     */
    public function getTotalIncomesCountAttribute(): int
    {
        return $this->transactions()->where('type', 'income')->count();
    }

    /**
     * Total expense records count (from transactions ledger).
     */
    public function getTotalExpensesCountAttribute(): int
    {
        return $this->transactions()->where('type', 'expense')->count();
    }

    /**
     * Total loan records count (Loan model + expense Transactions tagged as loan by admin).
     */
    public function getTotalLoansCountAttribute(): int
    {
        $groupLoans = $this->loans()->where('is_group_loan', true)->count();
        $transactionLoans = $this->transactions()->where('type', 'expense')->count();
        return $groupLoans + $transactionLoans;
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
