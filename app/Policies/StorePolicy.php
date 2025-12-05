<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StorePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their stores
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Store $store): bool
    {
        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $store->merchant_id) {
            return false;
        }

        // Owners can view all their merchant's stores
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can only view stores they're assigned to
        return $user->stores()->where('stores.id', $store->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only owners can create stores
        return $user->isOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Store $store): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $store->merchant_id) {
            return false;
        }

        // Owners can update all stores
        if ($user->isOwner()) {
            return true;
        }

        // Managers can update stores they're assigned to
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $store->id)->exists();
        }

        // Staff cannot update stores
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Store $store): bool
    {
        // Only owners can delete stores
        return $user->isOwner() && $user->merchant_id === $store->merchant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Store $store): bool
    {
        return $this->delete($user, $store);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Store $store): bool
    {
        return $this->delete($user, $store);
    }
}
