<?php

namespace App\Policies;

use App\Models\ProductAddon;
use App\Models\User;

class ProductAddonPolicy
{
    /**
     * Determine whether the user can view any addons.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the addon.
     */
    public function view(User $user, ProductAddon $addon): bool
    {
        return $user->merchant_id === $addon->merchant_id;
    }

    /**
     * Determine whether the user can create addons.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the addon.
     */
    public function update(User $user, ProductAddon $addon): bool
    {
        return $user->merchant_id === $addon->merchant_id;
    }

    /**
     * Determine whether the user can delete the addon.
     */
    public function delete(User $user, ProductAddon $addon): bool
    {
        return $user->merchant_id === $addon->merchant_id;
    }
}
