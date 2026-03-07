<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupFund;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Saving;
use App\Services\AiInsightsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AiInsightsController extends Controller
{
    public function index(Request $request, AiInsightsService $aiInsights)
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $totalIncome = \App\Models\Transaction::where('type', 'income')
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        $totalExpenses = \App\Models\Transaction::where('type', 'expense')
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        $activeLoans = Loan::where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->sum('remaining_balance');

        $loansCount = Loan::where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->count();

        $netBalance = $totalIncome - $totalExpenses - $activeLoans;

        $groupFund = GroupFund::getInstance();
        $pendingLoanRequests = LoanRequest::pending()->count();
        $totalCreditUnionLoans = Loan::creditUnionLoans()->active()->sum('amount');
        $pendingSavingsCount = Saving::pendingApproval()->count();

        $expenseCategoryChart = $this->generateExpenseCategoryChart($thirtyDaysAgo);

        $financialData = [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'activeLoans' => $activeLoans,
            'netBalance' => $netBalance,
            'expenseBreakdown' => $expenseCategoryChart,
        ];

        $cacheKey = "ai-insights-{$user->id}";
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $aiInsightsHtml = Cache::remember($cacheKey, now()->addHours(6), fn () => $aiInsights->generateInsights($financialData));

        return view('admin.ai_insights.index', compact(
            'totalIncome',
            'totalExpenses',
            'activeLoans',
            'loansCount',
            'netBalance',
            'groupFund',
            'pendingLoanRequests',
            'totalCreditUnionLoans',
            'pendingSavingsCount',
            'aiInsightsHtml'
        ));
    }

    protected function generateExpenseCategoryChart($startDate)
    {
        $expenses = \App\Models\Transaction::where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $expenses->pluck('category'),
            'data' => $expenses->pluck('total'),
        ];
    }
}
