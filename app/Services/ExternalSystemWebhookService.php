<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use App\Models\SystemRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Service for pushing finance data back to external systems via webhooks
 */
class ExternalSystemWebhookService
{
    /**
     * Push income to external system
     */
    public function pushIncome(Income $income, ?SystemRegistry $system = null): bool
    {
        $system = $system ?? $income->externalSystem;
        
        if (!$system || !$system->callback_url) {
            Log::warning('Cannot push income: system has no callback URL', [
                'income_id' => $income->id,
                'system_id' => $system?->system_id,
            ]);
            return false;
        }

        if (!$system->active_status) {
            Log::warning('Cannot push income: system is inactive', [
                'income_id' => $income->id,
                'system_id' => $system->system_id,
            ]);
            return false;
        }

        try {
            $payload = [
                'transaction_type' => 'income',
                'priority_bank_transaction_id' => $income->id,
                'external_transaction_id' => $income->external_transaction_id,
                'amount' => (float) $income->amount,
                'date' => $income->date->format('Y-m-d'),
                'channel' => $income->channel,
                'notes' => $income->notes,
                'category' => $income->category?->name,
                'account' => $income->account?->name,
                'synced_at' => $income->synced_at?->toIso8601String(),
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Priority-Bank-System' => 'priority_bank',
                    'X-Transaction-Id' => (string) $income->id,
                ])
                ->post($system->callback_url . '/api/webhook/finance/income', $payload);

            if ($response->successful()) {
                $income->update([
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);

                Log::info('Income pushed to external system successfully', [
                    'income_id' => $income->id,
                    'system_id' => $system->system_id,
                ]);

                return true;
            } else {
                $income->update([
                    'sync_status' => 'failed',
                    'sync_error' => $response->body(),
                ]);

                Log::error('Failed to push income to external system', [
                    'income_id' => $income->id,
                    'system_id' => $system->system_id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            $income->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error('Exception while pushing income to external system', [
                'income_id' => $income->id,
                'system_id' => $system->system_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Push expense to external system
     */
    public function pushExpense(Expense $expense, ?SystemRegistry $system = null): bool
    {
        $system = $system ?? $expense->externalSystem;
        
        if (!$system || !$system->callback_url) {
            Log::warning('Cannot push expense: system has no callback URL', [
                'expense_id' => $expense->id,
                'system_id' => $system?->system_id,
            ]);
            return false;
        }

        if (!$system->active_status) {
            Log::warning('Cannot push expense: system is inactive', [
                'expense_id' => $expense->id,
                'system_id' => $system->system_id,
            ]);
            return false;
        }

        try {
            $payload = [
                'transaction_type' => 'expense',
                'priority_bank_transaction_id' => $expense->id,
                'external_transaction_id' => $expense->external_transaction_id,
                'amount' => (float) $expense->amount,
                'date' => $expense->date->format('Y-m-d'),
                'channel' => $expense->channel,
                'notes' => $expense->notes,
                'category' => $expense->category?->name,
                'account' => $expense->account?->name,
                'synced_at' => $expense->synced_at?->toIso8601String(),
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Priority-Bank-System' => 'priority_bank',
                    'X-Transaction-Id' => (string) $expense->id,
                ])
                ->post($system->callback_url . '/api/webhook/finance/expense', $payload);

            if ($response->successful()) {
                $expense->update([
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);

                Log::info('Expense pushed to external system successfully', [
                    'expense_id' => $expense->id,
                    'system_id' => $system->system_id,
                ]);

                return true;
            } else {
                $expense->update([
                    'sync_status' => 'failed',
                    'sync_error' => $response->body(),
                ]);

                Log::error('Failed to push expense to external system', [
                    'expense_id' => $expense->id,
                    'system_id' => $system->system_id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            $expense->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error('Exception while pushing expense to external system', [
                'expense_id' => $expense->id,
                'system_id' => $system->system_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

