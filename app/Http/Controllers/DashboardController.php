<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Payout;
use App\Models\Saving;
use App\Models\LoanRequest;
use App\Models\Payment;
use App\Models\GroupFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\AiInsightsService;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        return $this->adminDashboardData($user);
    }

    public function userDashboard()
    {
        $user = Auth::user();
        return $this->userDashboardData($user);
    }

    protected function adminDashboardData($user)
    {
        // Get pending users count
        $pendingUsersCount = \App\Models\User::where('status', 'pending')->count();
        
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Financial Summary - Admin sees ALL transactions across all users
        $totalIncome = \App\Models\Transaction::where('type', 'income')
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        $totalExpenses = \App\Models\Transaction::where('type', 'expense')
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        // Active Loans - All group loans across all users
        $activeLoans = Loan::where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->sum('remaining_balance');

        $loansCount = Loan::where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->count();

        $netBalance = $totalIncome - $totalExpenses - $activeLoans;

        // Credit Union Summary
        $groupFund = GroupFund::getInstance();
        $pendingLoanRequests = LoanRequest::pending()->count();
        $totalCreditUnionLoans = Loan::creditUnionLoans()->active()->sum('amount');

        // Get pending loan requests for notifications
        $pendingLoanRequestsList = LoanRequest::pending()->with('user')->latest()->take(5)->get();
        
        // Get pending deposits for notifications
        $pendingDepositsList = \App\Models\Deposit::pending()->with('user')->latest()->take(5)->get();
        $pendingDepositsCount = \App\Models\Deposit::pending()->count();

        // Get pending savings (direct deposits awaiting admin approval)
        $pendingSavingsList = Saving::pendingApproval()->with('user')->latest()->take(5)->get();
        $pendingSavingsCount = Saving::pendingApproval()->count();

        // Charts & Recent - Admin sees all transactions
        $incomeExpenseChart = $this->generateIncomeExpenseChart(null, $thirtyDaysAgo);
        $expenseCategoryChart = $this->generateExpenseCategoryChart(null, $thirtyDaysAgo);
        $recentTransactions = $this->getRecentTransactions(null);

        // AI Insights with Cache
        $aiInsightsService = app(AiInsightsService::class);
        $financialData = [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'activeLoans' => $activeLoans,
            'netBalance' => $netBalance,
            'expenseBreakdown' => $expenseCategoryChart,
        ];
        $aiInsights = Cache::remember("ai-insights-{$user->id}", now()->addHours(6), fn () => $aiInsightsService->generateInsights($financialData));

        return view('admin.dashboard', compact(
            'totalIncome', 'totalExpenses', 'activeLoans', 'loansCount',
            'netBalance', 'incomeExpenseChart', 'expenseCategoryChart',
            'recentTransactions', 'aiInsights', 'groupFund', 'pendingLoanRequests',
            'totalCreditUnionLoans', 'pendingUsersCount', 'pendingLoanRequestsList',
            'pendingDepositsList', 'pendingDepositsCount',
            'pendingSavingsList', 'pendingSavingsCount'
        ));
    }

    protected function userDashboardData($user)
    {
        // Credit Union Balance Summary
        $savingsBalance = $user->savings_balance;
        $loanBalance = $user->loan_balance;
        $netBalance = $user->net_balance;

        // Recent Activity
        $recentSavings = Saving::where('user_id', $user->id)
            ->latest()->take(5)->get();

        $recentLoans = Loan::where('user_id', $user->id)
            ->where('is_group_loan', true)
            ->latest()->take(5)->get();

        $recentPayments = Payment::where('user_id', $user->id)
            ->latest()->take(5)->get();

        $activeLoanRequests = LoanRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        // Bank transactions tagged to this user (e.g. by admin from Priority Bank)
        $recentTransactions = \App\Models\Transaction::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'savingsBalance', 'loanBalance', 'netBalance', 'recentSavings',
            'recentLoans', 'recentPayments', 'activeLoanRequests', 'recentTransactions'
        ));
    }

    protected function generateIncomeExpenseChart($user, $startDate)
    {
        // If user is null, show all transactions (admin view)
        $incomeQuery = \App\Models\Transaction::where('type', 'income')
            ->where('date', '>=', $startDate);
        if ($user) {
            $incomeQuery->where('user_id', $user->id);
        }
        $incomeData = $incomeQuery->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')->orderBy('day')->get();

        $expenseQuery = \App\Models\Transaction::where('type', 'expense')
            ->where('date', '>=', $startDate);
        if ($user) {
            $expenseQuery->where('user_id', $user->id);
        }
        $expenseData = $expenseQuery->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')->orderBy('day')->get();

        $labels = [];
        $income = [];
        $expenses = [];

        $period = new DatePeriod($startDate, new DateInterval('P1D'), Carbon::now());

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M d');

            $income[] = optional($incomeData->firstWhere('day', $dateStr))->total ?? 0;
            $expenses[] = optional($expenseData->firstWhere('day', $dateStr))->total ?? 0;
        }

        return compact('labels', 'income', 'expenses');
    }

    protected function generateExpenseCategoryChart($user, $startDate)
    {
        // If user is null, show all transactions (admin view)
        $expenseQuery = \App\Models\Transaction::where('type', 'expense')
            ->where('date', '>=', $startDate);
        if ($user) {
            $expenseQuery->where('user_id', $user->id);
        }
        $expenses = $expenseQuery->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $expenses->pluck('category'),
            'data' => $expenses->pluck('total'),
        ];
    }

    protected function getRecentTransactions($user)
    {
        // If user is null, show all transactions (admin view)
        $query = \App\Models\Transaction::query();
        if ($user) {
            $query->where('user_id', $user->id);
        }
        
        $transactions = $query->with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'description' => $item->description ?? $item->category ?? ucfirst($item->type),
                    'category' => $item->category ?? ucfirst($item->type),
                    'date' => $item->date,
                    'user' => $item->user ? $item->user->name : 'N/A'
                ];
            });

        return $transactions->all();
    }

}
