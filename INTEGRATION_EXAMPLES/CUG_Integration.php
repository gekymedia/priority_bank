<?php

/**
 * CUG System Integration Example
 * 
 * This file shows how to integrate CUG (Priority Admissions) system
 * with Priority Bank Central Finance API.
 * 
 * Place this in: cug/app/Services/PriorityBankIntegrationService.php
 * 
 * Usage:
 * 1. Copy PriorityBankApiClient to cug/app/Services/
 * 2. Add to cug/.env:
 *    PRIORITY_BANK_API_URL=https://prioritybank.example.com
 *    PRIORITY_BANK_API_TOKEN=your_token
 * 3. Update IncomeController and ExpenseController to use this service
 */

namespace App\Services;

use App\Models\IncomeRecord;
use App\Models\Expense;
use App\Services\PriorityBankApiClient;
use Illuminate\Support\Facades\Log;

class PriorityBankIntegrationService
{
    protected PriorityBankApiClient $client;

    public function __construct()
    {
        $this->client = new PriorityBankApiClient(
            config('services.priority_bank.api_url'),
            config('services.priority_bank.api_token')
        );
    }

    /**
     * Push income to Priority Bank when form sale is recorded
     * 
     * Call this after creating an IncomeRecord in CUG system
     */
    public function pushIncomeToPriorityBank(IncomeRecord $incomeRecord): bool
    {
        try {
            $result = $this->client->pushIncome(
                systemId: 'priority_admissions',
                externalTransactionId: 'cug_income_' . $incomeRecord->id,
                amount: (float) $incomeRecord->amount,
                date: $incomeRecord->received_at->format('Y-m-d'),
                channel: $this->determineChannel($incomeRecord->source),
                options: [
                    'notes' => $incomeRecord->label ?? "Income from {$incomeRecord->source}",
                    'income_category_name' => $this->mapIncomeCategory($incomeRecord->source),
                    'metadata' => [
                        'reference' => $incomeRecord->reference,
                        'source' => $incomeRecord->source,
                    ],
                ]
            );

            if ($result && $result['success']) {
                // Store Priority Bank transaction ID for reference
                // You may want to add a priority_bank_transaction_id column to income_records table
                Log::info('Income pushed to Priority Bank', [
                    'income_record_id' => $incomeRecord->id,
                    'priority_bank_id' => $result['data']['id'] ?? null,
                ]);
                return true;
            }

            Log::error('Failed to push income to Priority Bank', [
                'income_record_id' => $incomeRecord->id,
                'response' => $result,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception pushing income to Priority Bank', [
                'income_record_id' => $incomeRecord->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push expense to Priority Bank
     */
    public function pushExpenseToPriorityBank(Expense $expense): bool
    {
        try {
            $result = $this->client->pushExpense(
                systemId: 'priority_admissions',
                externalTransactionId: 'cug_expense_' . $expense->id,
                amount: (float) $expense->amount,
                date: $expense->spent_at->format('Y-m-d'),
                channel: $this->determineChannel($expense->category),
                options: [
                    'notes' => $expense->description,
                    'expense_category_name' => $this->mapExpenseCategory($expense->category),
                    'metadata' => [
                        'vendor' => $expense->vendor,
                        'reference' => $expense->reference,
                    ],
                ]
            );

            if ($result && $result['success']) {
                Log::info('Expense pushed to Priority Bank', [
                    'expense_id' => $expense->id,
                    'priority_bank_id' => $result['data']['id'] ?? null,
                ]);
                return true;
            }

            Log::error('Failed to push expense to Priority Bank', [
                'expense_id' => $expense->id,
                'response' => $result,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception pushing expense to Priority Bank', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Map CUG income source to Priority Bank category
     */
    protected function mapIncomeCategory(string $source): string
    {
        $mapping = [
            'CUG Form Sale' => 'Form Sales',
            'Other University Form' => 'Form Sales',
            'Document Request' => 'Document Services',
            'Dues' => 'Dues & Services',
        ];

        return $mapping[$source] ?? 'Other Income';
    }

    /**
     * Map CUG expense category to Priority Bank category
     */
    protected function mapExpenseCategory(string $category): string
    {
        $mapping = [
            'domain_hosting' => 'Hosting',
            'sms_api' => 'SMS',
            'printer_toner' => 'Office Supplies',
            'paper_a4' => 'Office Supplies',
            'internet_data' => 'Internet',
            'airtime' => 'Communication',
        ];

        return $mapping[$category] ?? 'Other Expenses';
    }

    /**
     * Determine payment channel based on source/category
     */
    protected function determineChannel(string $source): string
    {
        // Default to bank, but you can implement logic based on payment method
        return 'bank';
    }
}

/**
 * Example: Update IncomeController to use this service
 * 
 * In cug/app/Http/Controllers/IncomeController.php:
 * 
 * use App\Services\PriorityBankIntegrationService;
 * 
 * public function store(Request $request)
 * {
 *     $income = IncomeRecord::create([...]);
 *     
 *     // Push to Priority Bank
 *     $integrationService = new PriorityBankIntegrationService();
 *     $integrationService->pushIncomeToPriorityBank($income);
 *     
 *     return redirect()->back();
 * }
 */

