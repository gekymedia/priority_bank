<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * API controller that returns summary dashboard statistics for the authenticated user.
 */
class DashboardApiController extends Controller
{
    /**
     * Return key financial summary metrics for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Summaries
        $totalIncome = Income::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        $totalExpenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        $outstandingLoans = Loan::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->sum('amount');

        // Accounts balances
        $accounts = Account::where('user_id', $user->id)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                ];
            });

        // Budgets for current month
        $monthKey = Carbon::now()->format('Y-m');
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $monthKey)
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category' => optional($budget->category)->name,
                    'amount' => $budget->amount,
                    'spent' => $budget->spent,
                    'remaining' => $budget->remaining,
                    'used_percentage' => $budget->used_percentage,
                ];
            });

        $netBalance = $totalIncome - $totalExpenses - $outstandingLoans;

        return response()->json([
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_balance' => $netBalance,
            'outstanding_loans' => $outstandingLoans,
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
    }
}