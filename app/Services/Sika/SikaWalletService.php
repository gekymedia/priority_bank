<?php

namespace App\Services\Sika;

use App\Exceptions\Sika\InsufficientFundsException;
use App\Exceptions\Sika\WalletException;
use App\Models\Sika\SikaWallet;
use App\Models\Sika\SikaWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SikaWalletService
{
    /**
     * Get or create wallet for external user
     */
    public function getOrCreateWallet(int $externalUserId, string $source = SikaWallet::SOURCE_GEKYCHAT): SikaWallet
    {
        return SikaWallet::getOrCreateForExternalUser($externalUserId, $source);
    }

    /**
     * Get wallet balance
     */
    public function getBalance(int $externalUserId, string $source = SikaWallet::SOURCE_GEKYCHAT): array
    {
        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        return [
            'balance' => (float) $wallet->balance,
            'available_balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'status' => $wallet->status,
        ];
    }

    /**
     * Debit wallet (for Sika coin purchases from GekyChat)
     */
    public function debitWallet(
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type = SikaWalletTransaction::TYPE_SIKA_COIN_PURCHASE,
        ?string $description = null,
        array $metadata = [],
        string $source = SikaWallet::SOURCE_GEKYCHAT
    ): array {
        $existingTxn = SikaWalletTransaction::findByIdempotencyKey($idempotencyKey);
        if ($existingTxn) {
            if ($existingTxn->isCompleted()) {
                return $this->buildTransactionResponse($existingTxn);
            }
            throw new WalletException('Transaction is still processing', 409);
        }

        if ($amount <= 0) {
            throw new WalletException('Amount must be greater than zero', 400);
        }

        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        if (!$wallet->canTransact()) {
            throw new WalletException('Wallet is not active', 403);
        }

        return DB::transaction(function () use ($wallet, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source) {
            $wallet->lockForUpdate();
            $wallet->refresh();

            if (!$wallet->hasSufficientBalance($amount)) {
                throw new InsufficientFundsException(
                    'Insufficient funds in wallet',
                    402,
                    (float) $wallet->balance,
                    $amount
                );
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $transaction = SikaWalletTransaction::create([
                'wallet_id' => $wallet->id,
                'external_user_id' => $externalUserId,
                'source' => $source,
                'type' => $type,
                'direction' => SikaWalletTransaction::DIRECTION_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => SikaWalletTransaction::STATUS_COMPLETED,
                'idempotency_key' => $idempotencyKey,
                'description' => $description ?? 'Sika Coin Purchase',
                'metadata' => $metadata,
            ]);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            Log::info('Sika wallet debited', [
                'external_user_id' => $externalUserId,
                'source' => $source,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'new_balance' => $balanceAfter,
            ]);

            return $this->buildTransactionResponse($transaction);
        });
    }

    /**
     * Credit wallet (for Sika coin cashouts or deposits)
     */
    public function creditWallet(
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type = SikaWalletTransaction::TYPE_SIKA_COIN_CASHOUT,
        ?string $description = null,
        array $metadata = [],
        string $source = SikaWallet::SOURCE_GEKYCHAT
    ): array {
        $existingTxn = SikaWalletTransaction::findByIdempotencyKey($idempotencyKey);
        if ($existingTxn) {
            if ($existingTxn->isCompleted()) {
                return $this->buildTransactionResponse($existingTxn);
            }
            throw new WalletException('Transaction is still processing', 409);
        }

        if ($amount <= 0) {
            throw new WalletException('Amount must be greater than zero', 400);
        }

        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        if (!$wallet->canTransact()) {
            throw new WalletException('Wallet is not active', 403);
        }

        return DB::transaction(function () use ($wallet, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source) {
            $wallet->lockForUpdate();
            $wallet->refresh();

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $transaction = SikaWalletTransaction::create([
                'wallet_id' => $wallet->id,
                'external_user_id' => $externalUserId,
                'source' => $source,
                'type' => $type,
                'direction' => SikaWalletTransaction::DIRECTION_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => SikaWalletTransaction::STATUS_COMPLETED,
                'idempotency_key' => $idempotencyKey,
                'description' => $description ?? 'Sika Coin Cashout',
                'metadata' => $metadata,
            ]);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            Log::info('Sika wallet credited', [
                'external_user_id' => $externalUserId,
                'source' => $source,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'new_balance' => $balanceAfter,
            ]);

            return $this->buildTransactionResponse($transaction);
        });
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction(string $transactionId): ?array
    {
        $transaction = SikaWalletTransaction::find($transactionId);
        
        if (!$transaction) {
            return null;
        }

        return [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference,
            'status' => $transaction->status,
            'amount' => (float) $transaction->amount,
            'type' => $transaction->type,
            'direction' => $transaction->direction,
            'created_at' => $transaction->created_at->toIso8601String(),
        ];
    }

    /**
     * Get transaction history
     */
    public function getTransactions(int $externalUserId, string $source = SikaWallet::SOURCE_GEKYCHAT, int $perPage = 20): array
    {
        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ];
    }

    /**
     * Deposit funds to wallet (user adding money)
     */
    public function deposit(
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        ?string $description = null,
        array $metadata = [],
        string $source = SikaWallet::SOURCE_GEKYCHAT
    ): array {
        return $this->creditWallet(
            $externalUserId,
            $amount,
            $idempotencyKey,
            SikaWalletTransaction::TYPE_DEPOSIT,
            $description ?? 'Wallet Deposit',
            $metadata,
            $source
        );
    }

    private function buildTransactionResponse(SikaWalletTransaction $transaction): array
    {
        return [
            'success' => true,
            'transaction_id' => (string) $transaction->id,
            'reference' => $transaction->reference,
            'amount' => (float) $transaction->amount,
            'new_balance' => (float) $transaction->balance_after,
            'status' => $transaction->status,
            'timestamp' => $transaction->created_at->toIso8601String(),
        ];
    }
}
