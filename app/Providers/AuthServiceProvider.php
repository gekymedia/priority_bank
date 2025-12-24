<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\Saving;
use App\Models\LoanRequest;
use App\Policies\TransactionPolicy;
use App\Policies\SavingPolicy;
use App\Policies\LoanRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
        \App\Models\Income::class => \App\Policies\IncomePolicy::class,
        \App\Models\Expense::class => \App\Policies\ExpensePolicy::class,
        \App\Models\Loan::class => \App\Policies\LoanPolicy::class,
        \App\Models\Account::class => \App\Policies\AccountPolicy::class,
        \App\Models\Budget::class => \App\Policies\BudgetPolicy::class,
        Saving::class => SavingPolicy::class,
        LoanRequest::class => LoanRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}