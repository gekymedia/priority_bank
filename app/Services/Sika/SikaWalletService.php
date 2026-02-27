<?php

namespace App\Services\Sika;

use App\Exceptions\Sika\InsufficientFundsException;
use App\Exceptions\Sika\WalletException;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Sika\SikaWallet;
use App\Models\Sika\SikaWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SikaWalletService
{
    /**
     * Get the GekyChat merchant user (receives Sika coin purchase funds)
     * 
     * First tries to get from systems_registry (preferred), 
     * then falls back to config value.
     */
    protected function getMerchantUser(): User
    {
        // Try to get from systems_registry first (preferred method)
        $gekyChat = \App\Models\SystemRegistry::where('system_id', 'gekychat')
            ->where('active_status', true)
            ->first();
        
        if ($gekyChat && $gekyChat->user_id) {
            $merchant = User::find($gekyChat->user_id);
            if ($merchant) {
                return $merchant;
            }
        }
        
        // Fallback to config value
        $merchantUserId = config('services.gekychat.merchant_user_id', 1);
        $merchant = User::find($merchantUserId);
        
        if (!$merchant) {
            throw new WalletException('GekyChat merchant account not configured. Run: php artisan db:seed --class=SystemAccountsSeeder', 500);
        }
        
        return $merchant;
    }

    /**
     * Find Priority Bank user by phone number
     */
    protected function findUserByPhone(string $phone): ?User
    {
        // Normalize phone number (remove spaces, dashes, etc.)
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Try exact match first
        $user = User::where('phone', $normalizedPhone)->first();
        
        if (!$user) {
            // Try without country code prefix
            $phoneWithoutPrefix = preg_replace('/^\+?233/', '0', $normalizedPhone);
            $user = User::where('phone', $phoneWithoutPrefix)->first();
        }
        
        if (!$user) {
            // Try with country code
            $phoneWithPrefix = preg_replace('/^0/', '+233', $normalizedPhone);
            $user = User::where('phone', $phoneWithPrefix)->first();
        }
        
        return $user;
    }

    /**
     * Get wallet balance for external user
     * This checks the user's Priority Bank savings balance
     */
    public function getBalance(int $externalUserId, string $source = SikaWallet::SOURCE_GEKYCHAT, ?string $phone = null): array
    {
        // If phone is provided, look up the Priority Bank user
        if ($phone) {
            $user = $this->findUserByPhone($phone);
            
            if ($user) {
                return [
                    'balance' => (float) $user->savings_balance,
                    'available_balance' => (float) $user->net_balance,
                    'currency' => 'GHS',
                    'status' => $user->isApproved() ? 'active' : 'pending',
                    'priority_bank_user_id' => $user->id,
                    'has_priority_bank_account' => true,
                ];
            }
        }
        
        // Fallback to Sika wallet (for users without Priority Bank account)
        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        return [
            'balance' => (float) $wallet->balance,
            'available_balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'status' => $wallet->status,
            'has_priority_bank_account' => false,
        ];
    }

    /**
     * Get or create Sika wallet for external user (fallback for users without PB account)
     */
    public function getOrCreateWallet(int $externalUserId, string $source = SikaWallet::SOURCE_GEKYCHAT): SikaWallet
    {
        return SikaWallet::getOrCreateForExternalUser($externalUserId, $source);
    }

    /**
     * Debit wallet (for Sika coin purchases from GekyChat)
     * 
     * Flow:
     * 1. Find user's Priority Bank account by phone
     * 2. Check savings balance
     * 3. Debit from user's savings
     * 4. Credit to GekyChat merchant account
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
        // Check for duplicate transaction
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

        // Get phone from metadata (GekyChat should send user's phone)
        $phone = $metadata['phone'] ?? $metadata['user_phone'] ?? null;
        
        // Try to find Priority Bank user by phone
        $pbUser = $phone ? $this->findUserByPhone($phone) : null;
        
        if ($pbUser) {
            // User has Priority Bank account - use proper transfer flow
            return $this->debitFromSavings($pbUser, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source);
        }
        
        // Fallback to Sika wallet (for users without Priority Bank account)
        return $this->debitFromSikaWallet($externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source);
    }

    /**
     * Debit from user's Priority Bank savings and credit to GekyChat merchant
     */
    protected function debitFromSavings(
        User $user,
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type,
        ?string $description,
        array $metadata,
        string $source
    ): array {
        // Check user's savings balance
        $availableBalance = (float) $user->savings_balance;
        
        if ($availableBalance < $amount) {
            throw new InsufficientFundsException(
                'Insufficient funds in your Priority Bank savings account',
                402,
                $availableBalance,
                $amount
            );
        }

        // Get merchant account
        $merchant = $this->getMerchantUser();

        return DB::transaction(function () use ($user, $merchant, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source, $availableBalance) {
            // 1. Create expense transaction for user (debit from savings)
            $userTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'expense',
                'amount' => $amount,
                'description' => $description ?? 'Sika Coin Purchase via GekyChat',
                'category' => 'Sika Coins',
                'date' => now(),
                'notes' => json_encode([
                    'idempotency_key' => $idempotencyKey,
                    'external_user_id' => $externalUserId,
                    'source' => $source,
                    'type' => $type,
                    'metadata' => $metadata,
                ]),
            ]);

            // 2. Create income transaction for merchant (credit to GekyChat account)
            $merchantTransaction = Transaction::create([
                'user_id' => $merchant->id,
                'type' => 'income',
                'amount' => $amount,
                'description' => "Sika Coin Sale - User #{$user->id} ({$user->phone})",
                'category' => 'Sika Coin Sales',
                'date' => now(),
                'notes' => json_encode([
                    'idempotency_key' => $idempotencyKey,
                    'buyer_user_id' => $user->id,
                    'buyer_phone' => $user->phone,
                    'external_user_id' => $externalUserId,
                    'source' => $source,
                ]),
            ]);

            // 3. Record in Sika wallet transactions for audit trail
            $sikaTransaction = SikaWalletTransaction::create([
                'wallet_id' => null, // No Sika wallet used
                'external_user_id' => $externalUserId,
                'source' => $source,
                'type' => $type,
                'direction' => SikaWalletTransaction::DIRECTION_DEBIT,
                'amount' => $amount,
                'balance_before' => $availableBalance,
                'balance_after' => $availableBalance - $amount,
                'status' => SikaWalletTransaction::STATUS_COMPLETED,
                'idempotency_key' => $idempotencyKey,
                'description' => $description ?? 'Sika Coin Purchase (from Priority Bank savings)',
                'metadata' => array_merge($metadata, [
                    'priority_bank_user_id' => $user->id,
                    'priority_bank_transaction_id' => $userTransaction->id,
                    'merchant_transaction_id' => $merchantTransaction->id,
                    'transfer_type' => 'savings_to_merchant',
                ]),
            ]);

            Log::info('Sika coin purchase completed (savings transfer)', [
                'external_user_id' => $externalUserId,
                'priority_bank_user_id' => $user->id,
                'amount' => $amount,
                'user_transaction_id' => $userTransaction->id,
                'merchant_transaction_id' => $merchantTransaction->id,
                'sika_transaction_id' => $sikaTransaction->id,
                'new_user_balance' => $availableBalance - $amount,
            ]);

            return [
                'success' => true,
                'transaction_id' => (string) $sikaTransaction->id,
                'reference' => $sikaTransaction->reference,
                'amount' => (float) $amount,
                'new_balance' => (float) ($availableBalance - $amount),
                'status' => 'completed',
                'timestamp' => $sikaTransaction->created_at->toIso8601String(),
                'transfer_type' => 'savings_to_merchant',
                'priority_bank_user_id' => $user->id,
            ];
        });
    }

    /**
     * Debit from Sika wallet (fallback for users without Priority Bank account)
     */
    protected function debitFromSikaWallet(
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type,
        ?string $description,
        array $metadata,
        string $source
    ): array {
        $wallet = $this->getOrCreateWallet($externalUserId, $source);

        if (!$wallet->canTransact()) {
            throw new WalletException('Wallet is not active', 403);
        }

        return DB::transaction(function () use ($wallet, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source) {
            $wallet->lockForUpdate();
            $wallet->refresh();

            if (!$wallet->hasSufficientBalance($amount)) {
                throw new InsufficientFundsException(
                    'Insufficient funds in Sika wallet. Please fund your wallet or link your Priority Bank account.',
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
                'metadata' => array_merge($metadata, ['transfer_type' => 'sika_wallet']),
            ]);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            Log::info('Sika wallet debited (Sika wallet)', [
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
     * Credit wallet (for Sika coin cashouts)
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

        // Get phone from metadata
        $phone = $metadata['phone'] ?? $metadata['user_phone'] ?? null;
        $pbUser = $phone ? $this->findUserByPhone($phone) : null;

        if ($pbUser) {
            // Credit to user's Priority Bank savings
            return $this->creditToSavings($pbUser, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source);
        }

        // Fallback to Sika wallet
        return $this->creditToSikaWallet($externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source);
    }

    /**
     * Credit to user's Priority Bank savings (for cashouts)
     */
    protected function creditToSavings(
        User $user,
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type,
        ?string $description,
        array $metadata,
        string $source
    ): array {
        $merchant = $this->getMerchantUser();
        $currentBalance = (float) $user->savings_balance;

        return DB::transaction(function () use ($user, $merchant, $externalUserId, $amount, $idempotencyKey, $type, $description, $metadata, $source, $currentBalance) {
            // 1. Create income transaction for user (credit to savings)
            $userTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'income',
                'amount' => $amount,
                'description' => $description ?? 'Sika Coin Cashout via GekyChat',
                'category' => 'Sika Coins',
                'date' => now(),
                'notes' => json_encode([
                    'idempotency_key' => $idempotencyKey,
                    'external_user_id' => $externalUserId,
                    'source' => $source,
                    'type' => $type,
                ]),
            ]);

            // 2. Create expense transaction for merchant (debit from GekyChat account)
            $merchantTransaction = Transaction::create([
                'user_id' => $merchant->id,
                'type' => 'expense',
                'amount' => $amount,
                'description' => "Sika Coin Cashout - User #{$user->id} ({$user->phone})",
                'category' => 'Sika Coin Cashouts',
                'date' => now(),
                'notes' => json_encode([
                    'idempotency_key' => $idempotencyKey,
                    'recipient_user_id' => $user->id,
                    'recipient_phone' => $user->phone,
                    'external_user_id' => $externalUserId,
                    'source' => $source,
                ]),
            ]);

            // 3. Record in Sika wallet transactions for audit
            $sikaTransaction = SikaWalletTransaction::create([
                'wallet_id' => null,
                'external_user_id' => $externalUserId,
                'source' => $source,
                'type' => $type,
                'direction' => SikaWalletTransaction::DIRECTION_CREDIT,
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance + $amount,
                'status' => SikaWalletTransaction::STATUS_COMPLETED,
                'idempotency_key' => $idempotencyKey,
                'description' => $description ?? 'Sika Coin Cashout (to Priority Bank savings)',
                'metadata' => array_merge($metadata, [
                    'priority_bank_user_id' => $user->id,
                    'priority_bank_transaction_id' => $userTransaction->id,
                    'merchant_transaction_id' => $merchantTransaction->id,
                    'transfer_type' => 'merchant_to_savings',
                ]),
            ]);

            Log::info('Sika coin cashout completed (savings transfer)', [
                'external_user_id' => $externalUserId,
                'priority_bank_user_id' => $user->id,
                'amount' => $amount,
                'user_transaction_id' => $userTransaction->id,
                'merchant_transaction_id' => $merchantTransaction->id,
                'new_user_balance' => $currentBalance + $amount,
            ]);

            return [
                'success' => true,
                'transaction_id' => (string) $sikaTransaction->id,
                'reference' => $sikaTransaction->reference,
                'amount' => (float) $amount,
                'new_balance' => (float) ($currentBalance + $amount),
                'status' => 'completed',
                'timestamp' => $sikaTransaction->created_at->toIso8601String(),
                'transfer_type' => 'merchant_to_savings',
                'priority_bank_user_id' => $user->id,
            ];
        });
    }

    /**
     * Credit to Sika wallet (fallback)
     */
    protected function creditToSikaWallet(
        int $externalUserId,
        float $amount,
        string $idempotencyKey,
        string $type,
        ?string $description,
        array $metadata,
        string $source
    ): array {
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
                'metadata' => array_merge($metadata, ['transfer_type' => 'sika_wallet']),
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
    public function getTransactions(int $externalUserId, int $perPage = 20, string $source = SikaWallet::SOURCE_GEKYCHAT): array
    {
        // Get transactions from SikaWalletTransaction (includes both wallet and savings transfers)
        $transactions = SikaWalletTransaction::where('external_user_id', $externalUserId)
            ->where('source', $source)
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
