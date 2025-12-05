<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any categories.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view categories
        return true;
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): bool
    {
        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $category->merchant_id) {
            return false;
        }

        // Owners can view all categories
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can only view categories from their assigned stores
        return $user->stores()->where('stores.id', $category->store_id)->exists();
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        // Owners and Managers can create categories
        return $user->isOwner() || $user->isManager();
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $category->merchant_id) {
            return false;
        }

        // Owners can update all categories
        if ($user->isOwner()) {
            return true;
        }

        // Managers can update categories in their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $category->store_id)->exists();
        }

        // Staff cannot update categories
        return false;
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $category->merchant_id) {
            return false;
        }

        // Owners can delete all categories
        if ($user->isOwner()) {
            return true;
        }

        // Managers can delete categories in their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $category->store_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the category.
     */
    public function restore(User $user, Category $category): bool
    {
        return $this->delete($user, $category);
    }

    /**
     * Determine whether the user can permanently delete the category.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        // Only owners can force delete
        return $user->isOwner() && $user->merchant_id === $category->merchant_id;
    }
}
