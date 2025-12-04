<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use OpenAI;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Financial Summary
        $totalIncome = Income::where('user_id', $user->id)
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        $totalExpenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $thirtyDaysAgo)
            ->sum('amount');

        $activeLoans = Loan::where('user_id', $user->id)
            ->where('status', 'given')
            ->sum('amount');

        $loansCount = Loan::where('user_id', $user->id)
            ->where('status', 'given')
            ->count();

        $netBalance = $totalIncome - $totalExpenses - $activeLoans;

        // Charts & Recent
        $incomeExpenseChart = $this->generateIncomeExpenseChart($user, $thirtyDaysAgo);
        $expenseCategoryChart = $this->generateExpenseCategoryChart($user, $thirtyDaysAgo);
        $recentTransactions = $this->getRecentTransactions($user);

        // AI Insights with Cache
        $aiInsights = Cache::remember("ai-insights-{$user->id}", now()->addHours(6), function () use (
            $totalIncome, $totalExpenses, $activeLoans, $netBalance, $expenseCategoryChart
        ) {
            return $this->generateAiInsights([
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'activeLoans' => $activeLoans,
                'netBalance' => $netBalance,
                'expenseBreakdown' => $expenseCategoryChart
            ]);
        });

        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'activeLoans', 'loansCount',
            'netBalance', 'incomeExpenseChart', 'expenseCategoryChart',
            'recentTransactions', 'aiInsights'
        ));
    }

    protected function generateIncomeExpenseChart($user, $startDate)
    {
        $incomeData = Income::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')->orderBy('day')->get();

        $expenseData = Expense::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
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
        $expenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->orderByDesc('total')->get();

        return [
            'labels' => $expenses->pluck('category'),
            'data' => $expenses->pluck('total'),
        ];
    }

    protected function getRecentTransactions($user)
    {
        $incomes = Income::where('user_id', $user->id)->latest()->take(5)->get()->map(function ($item) {
            return [
                'type' => 'income',
                'amount' => $item->amount,
                'description' => $item->source,
                'category' => 'Income',
                'date' => $item->date
            ];
        });

        $expenses = Expense::where('user_id', $user->id)->latest()->take(5)->get()->map(function ($item) {
            return [
                'type' => 'expense',
                'amount' => $item->amount,
                'description' => $item->category,
                'category' => 'Expense',
                'date' => $item->date
            ];
        });

        return $incomes->merge($expenses)
            ->sortByDesc('date')->take(5)->values()->all();
    }

    protected function generateAiInsights($financialData)
    {
        $promptData = [
            'total_income' => number_format($financialData['totalIncome'], 2),
            'total_expenses' => number_format($financialData['totalExpenses'], 2),
            'net_balance' => number_format($financialData['netBalance'], 2),
            'expense_breakdown' => array_combine(
                $financialData['expenseBreakdown']['labels']->toArray(),
                array_map(fn($amount) => number_format($amount, 2), $financialData['expenseBreakdown']['data']->toArray())
            ),
            'active_loans' => number_format($financialData['activeLoans'], 2),
            'currency' => 'GHS'
        ];

        $prompt = "Analyze this financial data and provide 3–4 concise insights with actionable recommendations:\n\n"
            . json_encode($promptData, JSON_PRETTY_PRINT) . "\n\n"
            . "Focus on: spending patterns, savings opportunities, unusual expenses, and loan impact. "
            . "Use simple language and format response in <p> HTML blocks.";

        try {
  $client = \OpenAI::client(config('services.openai.api_key'));



            $response = $client->chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 600,
                'temperature' => 0.7,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('AI Insight error: ' . $e->getMessage());
            return $this->generateBasicInsights($financialData);
        }
    }

    protected function generateBasicInsights($financialData)
    {
        $insights = [];

       $expenseBreakdown = array_combine(
    $financialData['expenseBreakdown']['labels']->toArray(),
    $financialData['expenseBreakdown']['data']->toArray()
);

        arsort($expenseBreakdown);
        $topCategory = key($expenseBreakdown);
        $topAmount = current($expenseBreakdown);
        $topPercentage = $financialData['totalExpenses'] > 0
            ? round(($topAmount / $financialData['totalExpenses']) * 100)
            : 0;

        $insights[] = "Your largest expense category is <strong>{$topCategory}</strong>, accounting for {$topPercentage}% of your total expenses.";

        $savingsRate = $financialData['totalIncome'] > 0
            ? round(($financialData['netBalance'] / $financialData['totalIncome']) * 100, 2)
            : 0;

        if ($savingsRate > 20) {
            $insights[] = "Great job! Your savings rate is <strong>{$savingsRate}%</strong>, above the 20% benchmark.";
        } else {
            $insights[] = "Your savings rate is <strong>{$savingsRate}%</strong>. Consider reducing expenses to increase savings.";
        }

        if ($financialData['activeLoans'] > 0) {
            $loanPercentage = $financialData['totalIncome'] > 0
                ? round(($financialData['activeLoans'] / $financialData['totalIncome']) * 100)
                : 0;

            $insights[] = "You have <strong>GHS " . number_format($financialData['activeLoans'], 2) . "</strong> in active loans, representing {$loanPercentage}% of your income.";
        }

        return implode('<br><br>', $insights);
    }
}
