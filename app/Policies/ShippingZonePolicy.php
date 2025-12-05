<?php

namespace App\Policies;

use App\Models\ShippingZone;
use App\Models\User;

class ShippingZonePolicy
{
    /**
     * Determine whether the user can view any shipping zones.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view shipping zones
        return true;
    }

    /**
     * Determine whether the user can view the shipping zone.
     */
    public function view(User $user, ShippingZone $shippingZone): bool
    {
        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $shippingZone->merchant_id) {
            return false;
        }

        // Owners can view all shipping zones
        if ($user->isOwner()) {
            return true;
        }

        // Managers can view zones for their assigned stores
        return $user->stores()->where('stores.id', $shippingZone->store_id)->exists();
    }

    /**
     * Determine whether the user can create shipping zones.
     */
    public function create(User $user): bool
    {
        // Only owners and managers can create shipping zones
        return $user->isOwner() || $user->isManager();
    }

    /**
     * Determine whether the user can update the shipping zone.
     */
    public function update(User $user, ShippingZone $shippingZone): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $shippingZone->merchant_id) {
            return false;
        }

        // Owners can update all shipping zones
        if ($user->isOwner()) {
            return true;
        }

        // Managers can update zones for their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $shippingZone->store_id)->exists();
        }

        // Staff cannot update shipping zones
        return false;
    }

    /**
     * Determine whether the user can delete the shipping zone.
     */
    public function delete(User $user, ShippingZone $shippingZone): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $shippingZone->merchant_id) {
            return false;
        }

        // Owners can delete all shipping zones
        if ($user->isOwner()) {
            return true;
        }

        // Managers can delete zones for their assigned stores
        if ($user->isManager()) {
            return $user->stores()->where('stores.id', $shippingZone->store_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the shipping zone.
     */
    public function restore(User $user, ShippingZone $shippingZone): bool
    {
        return $this->delete($user, $shippingZone);
    }

    /**
     * Determine whether the user can permanently delete the shipping zone.
     */
    public function forceDelete(User $user, ShippingZone $shippingZone): bool
    {
        // Only owners can force delete
        return $user->isOwner() && $user->merchant_id === $shippingZone->merchant_id;
    }
}
