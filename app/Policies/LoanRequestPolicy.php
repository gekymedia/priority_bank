<?php

namespace App\Policies;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoanRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view loan request lists
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LoanRequest $loanRequest): bool
    {
        return $user->isAdmin() || $user->id === $loanRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return !$user->isAdmin(); // Only regular users can create loan requests
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LoanRequest $loanRequest): bool
    {
        return $user->isAdmin() || ($user->id === $loanRequest->user_id && $loanRequest->status === 'pending');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoanRequest $loanRequest): bool
    {
        return $user->isAdmin() || ($user->id === $loanRequest->user_id && $loanRequest->status === 'pending');
    }

    /**
     * Determine whether the user can approve/reject loan requests.
     */
    public function approve(User $user, LoanRequest $loanRequest): bool
    {
        return $user->isAdmin() && $loanRequest->status === 'pending';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LoanRequest $loanRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LoanRequest $loanRequest): bool
    {
        return false;
    }
}
