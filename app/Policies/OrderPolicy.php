<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view orders for their stores
        return true;
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Check if user belongs to the same merchant
        if ($user->merchant_id !== $order->merchant_id) {
            return false;
        }

        // Owners can view all orders in their merchant
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can only view orders from their assigned stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can create orders.
     * Note: Customers create orders via public API, staff do not create orders directly.
     */
    public function create(User $user): bool
    {
        // Staff users should not create orders directly
        // Orders are created via the public checkout API
        return false;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Check merchant match
        if ($user->merchant_id !== $order->merchant_id) {
            return false;
        }

        // Owners can update all orders
        if ($user->isOwner()) {
            return true;
        }

        // Managers and Staff can update orders from their assigned stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only owners can delete orders (soft delete)
        return $user->isOwner() && $user->merchant_id === $order->merchant_id;
    }

    /**
     * Determine whether the user can update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }

    /**
     * Determine whether the user can update shipping information.
     */
    public function updateShipping(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }

    /**
     * Determine whether the user can view audit logs for the order.
     */
    public function viewAuditLogs(User $user, Order $order): bool
    {
        // Only owners and managers can view audit logs
        if ($user->merchant_id !== $order->merchant_id) {
            return false;
        }

        return $user->isOwner() || $user->isManager();
    }
}
