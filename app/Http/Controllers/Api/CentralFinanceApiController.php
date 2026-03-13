<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\SystemRegistry;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
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
            'income_category_id' => 'nullable|exists:income_categories,id',
            'income_category_name' => 'nullable|string|max:255', // Alternative: create category by name
            'account_id' => 'nullable|exists:accounts,id',
            'metadata' => 'nullable|array',
        ]);

        // Get or generate idempotency key
        $idempotencyKey = $request->header('X-Idempotency-Key') 
            ?? $validated['idempotency_key'] 
            ?? $this->generateIdempotencyKey($validated['system_id'], $validated['external_transaction_id']);

        // Check for duplicate using idempotency key
        $existing = Income::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Income already recorded (idempotent)',
                'data' => $existing,
            ], 200);
        }

        // Get system registry
        $system = SystemRegistry::where('system_id', $validated['system_id'])->first();
        if (!$system || !$system->active_status) {
            return response()->json([
                'success' => false,
                'message' => 'System not found or inactive',
            ], 404);
        }

        // Get or create income category
        $incomeCategoryId = $validated['income_category_id'] ?? null;
        if (!$incomeCategoryId && isset($validated['income_category_name'])) {
            $incomeCategory = IncomeCategory::firstOrCreate(
                ['name' => $validated['income_category_name'], 'user_id' => null],
                ['name' => $validated['income_category_name'], 'user_id' => null]
            );
            $incomeCategoryId = $incomeCategory->id;
        }

        // Attribute to the system's user account (each source has its own user in the bank)
        $userId = $system->user_id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'System has no linked user account. Link a user to this source in API Keys / Sources.',
            ], 400);
        }

        // Use system's account, or first account for that user, or create a default one
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
            $accountId = $account->id;
        } else {
            $account = Account::where('id', $accountId)->where('user_id', $userId)->first();
            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not belong to this system.',
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $income = Income::create([
                'user_id' => $userId,
                'external_system_id' => $system->id,
                'external_transaction_id' => $validated['external_transaction_id'],
                'idempotency_key' => $idempotencyKey,
                'income_category_id' => $incomeCategoryId,
                'account_id' => $accountId,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'channel' => $validated['channel'],
                'notes' => $validated['notes'] ?? null,
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            // Also create a Transaction so it appears in the bank's Transactions list
            $categoryName = $incomeCategoryId ? (IncomeCategory::find($incomeCategoryId)?->name) : null;
            $categoryName = $categoryName ?? ($validated['income_category_name'] ?? 'Income');
            Transaction::create([
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

            DB::commit();

            Log::info('Income recorded from external system', [
                'system_id' => $validated['system_id'],
                'income_id' => $income->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Income recorded successfully',
                'data' => $income->load(['category', 'account', 'externalSystem']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
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
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'expense_category_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
            'metadata' => 'nullable|array',
        ]);

        // Get or generate idempotency key
        $idempotencyKey = $request->header('X-Idempotency-Key') 
            ?? $validated['idempotency_key'] 
            ?? $this->generateIdempotencyKey($validated['system_id'], $validated['external_transaction_id']);

        // Check for duplicate
        $existing = Expense::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Expense already recorded (idempotent)',
                'data' => $existing,
            ], 200);
        }

        // Get system registry
        $system = SystemRegistry::where('system_id', $validated['system_id'])->first();
        if (!$system || !$system->active_status) {
            return response()->json([
                'success' => false,
                'message' => 'System not found or inactive',
            ], 404);
        }

        // Get or create expense category
        $expenseCategoryId = $validated['expense_category_id'] ?? null;
        if (!$expenseCategoryId && isset($validated['expense_category_name'])) {
            $expenseCategory = ExpenseCategory::firstOrCreate(
                ['name' => $validated['expense_category_name'], 'user_id' => null],
                ['name' => $validated['expense_category_name'], 'user_id' => null]
            );
            $expenseCategoryId = $expenseCategory->id;
        }

        // Attribute to the system's user account (each source has its own user in the bank)
        $userId = $system->user_id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'System has no linked user account. Link a user to this source in API Keys / Sources.',
            ], 400);
        }

        // Use system's account, or first account for that user, or create a default one
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
            $accountId = $account->id;
        } else {
            $account = Account::where('id', $accountId)->where('user_id', $userId)->first();
            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not belong to this system.',
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $expense = Expense::create([
                'user_id' => $userId,
                'external_system_id' => $system->id,
                'external_transaction_id' => $validated['external_transaction_id'],
                'idempotency_key' => $idempotencyKey,
                'expense_category_id' => $expenseCategoryId,
                'account_id' => $accountId,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'channel' => $validated['channel'],
                'notes' => $validated['notes'] ?? null,
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            // Also create a Transaction so it appears in the bank's Transactions list
            $expCategoryName = $expenseCategoryId ? (ExpenseCategory::find($expenseCategoryId)?->name) : null;
            $expCategoryName = $expCategoryName ?? ($validated['expense_category_name'] ?? 'Expense');
            Transaction::create([
                'user_id' => $userId,
                'type' => 'expense',
                'category' => $expCategoryName,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'description' => $validated['notes'] ?? $expCategoryName,
                'notes' => null,
                'external_system_id' => $system->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            DB::commit();

            Log::info('Expense recorded from external system', [
                'system_id' => $validated['system_id'],
                'expense_id' => $expense->id,
                'external_transaction_id' => $validated['external_transaction_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense recorded successfully',
                'data' => $expense->load(['category', 'account', 'externalSystem']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
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

