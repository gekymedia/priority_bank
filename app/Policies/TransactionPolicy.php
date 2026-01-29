<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determine if the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Admins can view all transactions
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can only view their own transactions
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine if the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Admins can update all transactions
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can only update their own transactions
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine if the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Admins can delete all transactions
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can only delete their own transactions
        return $user->id === $transaction->user_id;
    }
}
