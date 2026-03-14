<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\SystemRegistry;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Central Finance API Controller
 *
 * Each external system (CUG, GekyChat, etc.) has a user account in the bank (Systems Registry
 * links system_id to a User). Income and expense from that system are recorded against that
 * system's user and account: deposit = credit their account (bank owes them), payout = debit
 * their account. Admin Geky and other humans do their own transactions under their own user
 * accounts. So the bank tracks per-entity balances (system or person) via user + account.
 */
class CentralFinanceApiController extends Controller
{
    /**
     * Store income from external system
     * 
     * POST /api/central-finance/income
     * 
     * Required headers:
     * - Authorization: Bearer {token}
     * - X-Idempotency-Key: {unique_key}
     * 
     * Body:
     * {
     *   "system_id": "gekymedia",
     *   "external_transaction_id": "gekymedia_income_123",
     *   "amount": 1000.00,
     *   "date": "2025-01-15",
     *   "channel": "bank",
     *   "notes": "Payment from client",
     *   "income_category_id": 1, // Optional, will create if not exists
     *   "account_id": 1, // Optional, defaults to first account
     *   "metadata": {} // Optional system-specific data
     * }
     */
    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'system_id' => 'required|string|exists:systems_registry,system_id',
            'external_transaction_id' => 'required|string',
            'idempotency_key' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
            'income_category_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
            'metadata' => 'nullable|array',
        ]);

        $system = SystemRegistry::where('system_id', $validated['system_id'])->first();
        if (!$system || !$system->active_status) {
            return response()->json([
                'success' => false,
                'message' => 'System not found or inactive',
            ], 404);
        }

        $existing = Transaction::where('external_system_id', $system->id)
            ->where('external_transaction_id', $validated['external_transaction_id'])
            ->where('type', 'income')
            ->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Income already recorded (idempotent)',
                'data' => $existing->load(['user', 'externalSystem']),
            ], 200);
        }

        $userId = $system->user_id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'System has no linked user account. Link a user to this source in API Keys / Sources.',
            ], 400);
        }

        $accountId = $validated['account_id'] ?? null;
        if (!$accountId) {
            $account = Account::where('user_id', $userId)->first();
            if (!$account) {
                Account::create([
                    'user_id' => $userId,
                    'name' => 'Default',
                    'type' => 'bank',
                    'opening_balance' => 0,
                ]);
            }
        }

        $categoryName = $validated['income_category_name'] ?? 'Income';

        try {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'income',
                'category' => $categoryName,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'description' => $validated['notes'] ?? $categoryName,
                'notes' => null,
                'external_system_id' => $system->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            Log::info('Income recorded from external system', [
                'system_id' => $validated['system_id'],
                'transaction_id' => $transaction->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Income recorded successfully',
                'data' => $transaction->load(['user', 'externalSystem']),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to record income from external system', [
                'system_id' => $validated['system_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record income',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Store expense from external system
     * 
     * POST /api/central-finance/expense
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'system_id' => 'required|string|exists:systems_registry,system_id',
            'external_transaction_id' => 'required|string',
            'idempotency_key' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
            'expense_category_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
            'metadata' => 'nullable|array',
        ]);

        $system = SystemRegistry::where('system_id', $validated['system_id'])->first();
        if (!$system || !$system->active_status) {
            return response()->json([
                'success' => false,
                'message' => 'System not found or inactive',
            ], 404);
        }

        $existing = Transaction::where('external_system_id', $system->id)
            ->where('external_transaction_id', $validated['external_transaction_id'])
            ->where('type', 'expense')
            ->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Expense already recorded (idempotent)',
                'data' => $existing->load(['user', 'externalSystem']),
            ], 200);
        }

        $userId = $system->user_id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'System has no linked user account. Link a user to this source in API Keys / Sources.',
            ], 400);
        }

        $accountId = $validated['account_id'] ?? null;
        if (!$accountId) {
            $account = Account::where('user_id', $userId)->first();
            if (!$account) {
                $account = Account::create([
                    'user_id' => $userId,
                    'name' => 'Default',
                    'type' => 'bank',
                    'opening_balance' => 0,
                ]);
            }
        }

        $categoryName = $validated['expense_category_name'] ?? 'Expense';

        try {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'expense',
                'category' => $categoryName,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'description' => $validated['notes'] ?? $categoryName,
                'notes' => null,
                'external_system_id' => $system->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            Log::info('Expense recorded from external system', [
                'system_id' => $validated['system_id'],
                'transaction_id' => $transaction->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense recorded successfully',
                'data' => $transaction->load(['user', 'externalSystem']),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to record expense from external system', [
                'system_id' => $validated['system_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record expense',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Return net balance for the authenticated caller (same as statement "Net balance").
     * GET /api/central-finance/balance
     *
     * The Bearer token identifies one user in the bank (e.g. Priority Agriculture, GekyChat,
     * SchoolsGH). Net is computed as Total credits (income transactions) minus Total debits
     * (expense transactions) for that user — matching the bank statement "Net balance".
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Net = Total credits - Total debits (same as statement "Net balance")
        $credits = (float) Transaction::where('user_id', $user->id)->where('type', 'income')->sum('amount');
        $debits = (float) Transaction::where('user_id', $user->id)->where('type', 'expense')->sum('amount');
        $netBalance = $credits - $debits;

        return response()->json([
            'success' => true,
            'balance' => round($netBalance, 2),
            'currency' => 'GHS',
        ]);
    }

    /**
     * Generate idempotency key
     */
    private function generateIdempotencyKey(string $systemId, string $externalTransactionId): string
    {
        return hash('sha256', "{$systemId}:{$externalTransactionId}");
    }
}

