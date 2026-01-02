<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Expense;
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
 * This controller handles bidirectional finance data synchronization:
 * - Accepts income/expense from external systems
 * - Pushes finance data back to external systems when created in Priority Bank
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

        // Get default account if not provided
        $accountId = $validated['account_id'] ?? Account::first()?->id;
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'No account available. Please create an account first.',
            ], 400);
        }

        // Get default user (CEO/admin) - in production, this should be configurable
        $userId = auth()->id() ?? User::where('role', 'admin')->first()?->id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'No user available for recording income.',
            ], 400);
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

        // Get default account if not provided
        $accountId = $validated['account_id'] ?? Account::first()?->id;
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'No account available. Please create an account first.',
            ], 400);
        }

        // Get default user
        $userId = auth()->id() ?? User::where('role', 'admin')->first()?->id;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'No user available for recording expense.',
            ], 400);
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
     * Generate idempotency key
     */
    private function generateIdempotencyKey(string $systemId, string $externalTransactionId): string
    {
        return hash('sha256', "{$systemId}:{$externalTransactionId}");
    }
}

