<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view products
        return true;
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $product->merchant_id) {
            return false;
        }

        // Owners can view all products
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can only view products from their assigned stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        // Owners and Managers can create products
        return $user->isOwner() || $user->isManager();
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $product->merchant_id) {
            return false;
        }

        // Owners can update all products
        if ($user->isOwner()) {
            return true;
        }

        // Managers can update products in their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $product->store_id)->exists();
        }

        // Staff cannot update products
        return false;
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $product->merchant_id) {
            return false;
        }

        // Owners can delete all products
        if ($user->isOwner()) {
            return true;
        }

        // Managers can delete products in their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $product->store_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->delete($user, $product);
    }

    /**
     * Determine whether the user can permanently delete the product.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only owners can force delete
        return $user->isOwner() && $user->merchant_id === $product->merchant_id;
    }
}
