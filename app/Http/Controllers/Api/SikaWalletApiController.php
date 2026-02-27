<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Sika\InsufficientFundsException;
use App\Exceptions\Sika\WalletException;
use App\Http\Controllers\Controller;
use App\Services\Sika\SikaWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SikaWalletApiController extends Controller
{
    public function __construct(
        private SikaWalletService $walletService
    ) {}

    /**
     * Health check endpoint
     * GET /api/health
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'Priority Bank Ghana - Sika Wallet API',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get wallet balance for a user
     * GET /api/wallets/user/{userId}/balance
     */
    public function getBalance(int $userId): JsonResponse
    {
        try {
            $balance = $this->walletService->getBalance($userId);

            return response()->json($balance);

        } catch (\Exception $e) {
            Log::error('Failed to get wallet balance', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve balance',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Debit user's wallet (for Sika coin purchases)
     * POST /api/wallets/debit
     */
    public function debit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|in:GHS',
            'type' => 'sometimes|string',
            'idempotency_key' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        try {
            $result = $this->walletService->debitWallet(
                $validated['user_id'],
                (float) $validated['amount'],
                $validated['idempotency_key'],
                $validated['type'] ?? 'SIKA_COIN_PURCHASE',
                $validated['description'] ?? null,
                $validated['metadata'] ?? []
            );

            return response()->json($result);

        } catch (InsufficientFundsException $e) {
            return response()->json([
                'error' => 'insufficient_funds',
                'message' => $e->getMessage(),
                'available_balance' => $e->getAvailableBalance(),
                'requested_amount' => $e->getRequestedAmount(),
            ], 402);

        } catch (WalletException $e) {
            return response()->json([
                'error' => 'wallet_error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);

        } catch (\Exception $e) {
            Log::error('Wallet debit failed', [
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'debit_failed',
                'message' => 'Failed to process debit',
            ], 500);
        }
    }

    /**
     * Credit user's wallet (for Sika coin cashouts)
     * POST /api/wallets/credit
     */
    public function credit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|in:GHS',
            'type' => 'sometimes|string',
            'idempotency_key' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        try {
            $result = $this->walletService->creditWallet(
                $validated['user_id'],
                (float) $validated['amount'],
                $validated['idempotency_key'],
                $validated['type'] ?? 'SIKA_COIN_CASHOUT',
                $validated['description'] ?? null,
                $validated['metadata'] ?? []
            );

            return response()->json($result);

        } catch (WalletException $e) {
            return response()->json([
                'error' => 'wallet_error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);

        } catch (\Exception $e) {
            Log::error('Wallet credit failed', [
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'credit_failed',
                'message' => 'Failed to process credit',
            ], 500);
        }
    }

    /**
     * Verify a transaction
     * GET /api/transactions/{transactionId}
     */
    public function verifyTransaction(string $transactionId): JsonResponse
    {
        $transaction = $this->walletService->verifyTransaction($transactionId);

        if (!$transaction) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Reverse a transaction
     * POST /api/transactions/reverse
     */
    public function reverseTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'original_transaction_id' => 'required|string',
            'idempotency_key' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        return response()->json([
            'error' => 'not_implemented',
            'message' => 'Transaction reversal is not yet implemented',
        ], 501);
    }

    /**
     * Get transaction history for a user
     * GET /api/wallets/user/{userId}/transactions
     */
    public function getTransactions(Request $request, int $userId): JsonResponse
    {
        try {
            $perPage = min($request->input('per_page', 20), 100);
            $result = $this->walletService->getTransactions($userId, $perPage);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to get transactions', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve transactions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deposit funds to wallet
     * POST /api/wallets/deposit
     */
    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'idempotency_key' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        try {
            $result = $this->walletService->deposit(
                $validated['user_id'],
                (float) $validated['amount'],
                $validated['idempotency_key'],
                $validated['description'] ?? null,
                $validated['metadata'] ?? []
            );

            return response()->json($result);

        } catch (WalletException $e) {
            return response()->json([
                'error' => 'wallet_error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);

        } catch (\Exception $e) {
            Log::error('Wallet deposit failed', [
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'deposit_failed',
                'message' => 'Failed to process deposit',
            ], 500);
        }
    }
}
