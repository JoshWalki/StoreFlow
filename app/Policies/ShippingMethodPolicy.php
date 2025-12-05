<?php

namespace App\Policies;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;

class ShippingMethodPolicy
{
    /**
     * Determine whether the user can view any shipping methods.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view shipping methods
        return true;
    }

    /**
     * Determine whether the user can view the shipping method.
     */
    public function view(User $user, ShippingMethod $shippingMethod): bool
    {
        // Get the zone to check merchant and store
        $zone = $shippingMethod->zone;

        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $zone->merchant_id) {
            return false;
        }

        // Owners can view all shipping methods
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can view methods for their assigned stores
        return $user->stores()->where('stores.id', $zone->store_id)->exists();
    }

    /**
     * Determine whether the user can create shipping methods.
     */
    public function create(User $user, ?ShippingZone $zone = null): bool
    {
        // Only owners and managers can create shipping methods
        if (!($user->isOwner() || $user->isManager())) {
            return false;
        }

        // If zone is provided, verify access
        if ($zone) {
            if ($user->merchant_id !== $zone->merchant_id) {
                return false;
            }

            // Owners can create for any zone in their merchant
            if ($user->isOwner()) {
                return true;
            }

            // Managers can only create for their assigned stores
            return $user->stores()->where('stores.id', $zone->store_id)->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can update the shipping method.
     */
    public function update(User $user, ShippingMethod $shippingMethod): bool
    {
        $zone = $shippingMethod->zone;

        // Check merchant match
        if ($user->merchant_id !== $zone->merchant_id) {
            return false;
        }

        // Owners can update all shipping methods
        if ($user->isOwner()) {
            return true;
        }

        // Managers can update methods for their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $zone->store_id)->exists();
        }

        // Staff cannot update shipping methods
        return false;
    }

    /**
     * Determine whether the user can delete the shipping method.
     */
    public function delete(User $user, ShippingMethod $shippingMethod): bool
    {
        $zone = $shippingMethod->zone;

        // Check merchant match
        if ($user->merchant_id !== $zone->merchant_id) {
            return false;
        }

        // Owners can delete all shipping methods
        if ($user->isOwner()) {
            return true;
        }

        // Managers can delete methods for their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $zone->store_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the shipping method.
     */
    public function restore(User $user, ShippingMethod $shippingMethod): bool
    {
        return $this->delete($user, $shippingMethod);
    }

    /**
     * Determine whether the user can permanently delete the shipping method.
     */
    public function forceDelete(User $user, ShippingMethod $shippingMethod): bool
    {
        $zone = $shippingMethod->zone;

        // Only owners can force delete
        return $user->isOwner() && $user->merchant_id === $zone->merchant_id;
    }
}
