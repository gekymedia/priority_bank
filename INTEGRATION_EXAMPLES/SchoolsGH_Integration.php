<?php

/**
 * SchoolsGH System Integration Example
 * 
 * This file shows how to integrate SchoolsGH system with Priority Bank.
 * 
 * Place this in: schoolsgh/app/Services/PriorityBankIntegrationService.php
 */

namespace App\Services;

use App\Models\Tenant\Income;
use App\Models\Tenant\Expense;
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
     * Push subscription payment income to Priority Bank
     * 
     * Call this when a school subscription payment is received
     */
    public function pushSubscriptionPayment($subscription, $school): bool
    {
        try {
            $result = $this->client->pushIncome(
                systemId: 'schoolsgh',
                externalTransactionId: 'schoolsgh_sub_' . $subscription->id,
                amount: (float) $subscription->amount,
                date: $subscription->paid_at->format('Y-m-d'),
                channel: $this->determineChannel($subscription->payment_method),
                options: [
                    'notes' => "Subscription payment from {$school->name} - Term: {$subscription->term}",
                    'income_category_name' => 'Subscription Payments',
                    'metadata' => [
                        'school_id' => $school->id,
                        'school_name' => $school->name,
                        'subscription_id' => $subscription->id,
                        'term' => $subscription->term,
                    ],
                ]
            );

            if ($result && $result['success']) {
                Log::info('Subscription payment pushed to Priority Bank', [
                    'subscription_id' => $subscription->id,
                    'school_id' => $school->id,
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Exception pushing subscription to Priority Bank', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Push income to Priority Bank
     */
    public function pushIncomeToPriorityBank(Income $income): bool
    {
        try {
            $result = $this->client->pushIncome(
                systemId: 'schoolsgh',
                externalTransactionId: 'schoolsgh_income_' . $income->id,
                amount: (float) $income->amount,
                date: $income->date->format('Y-m-d'),
                channel: 'bank', // Adjust based on your data
                options: [
                    'notes' => $income->description,
                    'income_category_name' => $income->category?->name ?? 'Other Income',
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing income to Priority Bank', [
                'income_id' => $income->id,
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
                systemId: 'schoolsgh',
                externalTransactionId: 'schoolsgh_expense_' . $expense->id,
                amount: (float) $expense->amount,
                date: $expense->date->format('Y-m-d'),
                channel: 'bank',
                options: [
                    'notes' => $expense->description,
                    'expense_category_name' => $this->mapExpenseCategory($expense->category?->name),
                ]
            );

            return $result && $result['success'];
        } catch (\Exception $e) {
            Log::error('Exception pushing expense to Priority Bank', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Map expense category
     */
    protected function mapExpenseCategory(?string $category): string
    {
        $mapping = [
            'Domain' => 'Domain',
            'Hosting' => 'Hosting',
            'SMS Packages' => 'SMS',
        ];

        return $mapping[$category] ?? 'Other Expenses';
    }

    /**
     * Determine payment channel
     */
    protected function determineChannel(?string $paymentMethod): string
    {
        $mapping = [
            'bank' => 'bank',
            'mobile_money' => 'momo',
            'momo' => 'momo',
            'cash' => 'cash',
        ];

        return $mapping[strtolower($paymentMethod ?? '')] ?? 'bank';
    }
}

/**
 * Example: Update subscription payment handler
 * 
 * In your subscription payment webhook/controller:
 * 
 * use App\Services\PriorityBankIntegrationService;
 * 
 * public function handlePayment($subscription)
 * {
 *     // Process payment locally
 *     $subscription->markAsPaid();
 *     
 *     // Push to Priority Bank
 *     $integrationService = new PriorityBankIntegrationService();
 *     $integrationService->pushSubscriptionPayment($subscription, $subscription->school);
 * }
 */

