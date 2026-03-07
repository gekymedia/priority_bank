<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI;

class AiInsightsService
{
    /**
     * Generate AI-powered financial insights and recommendations from summary data.
     * Falls back to basic rule-based insights if OpenAI is unavailable.
     */
    public function generateInsights(array $financialData): string
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return $this->generateBasicInsights($financialData);
        }

        $promptData = [
            'total_income' => number_format($financialData['totalIncome'], 2),
            'total_expenses' => number_format($financialData['totalExpenses'], 2),
            'net_balance' => number_format($financialData['netBalance'], 2),
            'expense_breakdown' => array_combine(
                $financialData['expenseBreakdown']['labels']->toArray(),
                array_map(fn ($amount) => number_format($amount, 2), $financialData['expenseBreakdown']['data']->toArray())
            ),
            'active_loans' => number_format($financialData['activeLoans'], 2),
            'currency' => 'GHS',
        ];

        $prompt = "Analyze this financial data and provide 3–4 concise insights with actionable recommendations:\n\n"
            . json_encode($promptData, JSON_PRETTY_PRINT) . "\n\n"
            . "Focus on: spending patterns, savings opportunities, unusual expenses, and loan impact. "
            . "Use simple language and format response in <p> HTML blocks.";

        try {
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
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

    protected function generateBasicInsights(array $financialData): string
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
