<?php

namespace App\Policies;

use App\Models\Saving;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SavingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view savings lists
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Saving $saving): bool
    {
        return $user->isAdmin() || $user->id === $saving->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create savings
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Saving $saving): bool
    {
        return $user->isAdmin() || ($user->id === $saving->user_id && $saving->status === 'available');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Saving $saving): bool
    {
        return $user->isAdmin() || ($user->id === $saving->user_id && $saving->status === 'available');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Saving $saving): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Saving $saving): bool
    {
        return false;
    }
}
