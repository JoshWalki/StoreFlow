<?php

namespace App\Services\Orders;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Events\ShippingStatusUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    /**
     * Status transition rules based on A1.4 spec.
     * Maps current status to allowed next statuses.
     */
    protected const STATUS_TRANSITIONS = [
        Order::STATUS_PENDING => [
            Order::STATUS_ACCEPTED,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_READY,  // Allow skip
            Order::STATUS_PACKING,  // Allow skip
            Order::STATUS_SHIPPED,  // Allow skip to shipped
            Order::STATUS_READY_FOR_PICKUP,  // Allow skip for pickup orders
            Order::STATUS_CANCELLED
        ],
        Order::STATUS_ACCEPTED => [
            Order::STATUS_PENDING,  // Allow backward
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_READY,  // Allow skip
            Order::STATUS_PACKING,  // Allow skip
            Order::STATUS_SHIPPED,  // Allow skip to shipped
            Order::STATUS_READY_FOR_PICKUP,  // Allow skip
            Order::STATUS_CANCELLED
        ],
        Order::STATUS_IN_PROGRESS => [
            Order::STATUS_PENDING,  // Allow backward
            Order::STATUS_ACCEPTED,  // Allow backward
            Order::STATUS_READY,
            Order::STATUS_PACKING,  // Allow skip
            Order::STATUS_SHIPPED,  // Allow skip to shipped
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_CANCELLED
        ],
        Order::STATUS_READY => [
            Order::STATUS_ACCEPTED,  // Allow backward
            Order::STATUS_IN_PROGRESS,  // Allow backward
            Order::STATUS_PACKING,
            Order::STATUS_SHIPPED,  // Allow skip
            Order::STATUS_CANCELLED
        ],
        Order::STATUS_PACKING => [
            Order::STATUS_PENDING,  // Allow backward to fix mistakes
            Order::STATUS_ACCEPTED,  // Allow backward to fix mistakes
            Order::STATUS_IN_PROGRESS,  // Allow backward to fix mistakes
            Order::STATUS_READY,  // Allow backward
            Order::STATUS_SHIPPED
        ],
        Order::STATUS_SHIPPED => [
            Order::STATUS_PENDING,  // Allow backward to fix mistakes
            Order::STATUS_ACCEPTED,  // Allow backward to fix mistakes
            Order::STATUS_IN_PROGRESS,  // Allow backward to fix mistakes
            Order::STATUS_READY,  // Allow backward to fix mistakes
            Order::STATUS_PACKING,  // Allow backward to fix mistakes
            Order::STATUS_DELIVERED
        ],
        Order::STATUS_READY_FOR_PICKUP => [
            Order::STATUS_PENDING,  // Allow backward
            Order::STATUS_ACCEPTED,  // Allow backward
            Order::STATUS_IN_PROGRESS,  // Allow backward
            Order::STATUS_PICKED_UP
        ],
        Order::STATUS_DELIVERED => [
            Order::STATUS_PENDING,  // Allow backward to fix mistakes
            Order::STATUS_ACCEPTED,  // Allow backward to fix mistakes
            Order::STATUS_IN_PROGRESS,  // Allow backward to fix mistakes
            Order::STATUS_READY,  // Allow backward to fix mistakes
            Order::STATUS_PACKING,  // Allow backward to fix mistakes
            Order::STATUS_SHIPPED,  // Allow backward to fix mistakes
        ],
        Order::STATUS_PICKED_UP => [
            Order::STATUS_PENDING,  // Allow backward to fix mistakes
            Order::STATUS_ACCEPTED,  // Allow backward to fix mistakes
            Order::STATUS_IN_PROGRESS,  // Allow backward to fix mistakes
            Order::STATUS_READY_FOR_PICKUP,  // Allow backward to fix mistakes
        ],
        Order::STATUS_CANCELLED => [
            Order::STATUS_PENDING,  // Allow reactivation
        ],
    ];

    /**
     * Create a new order from cart data.
     *
     * @param array $data Order data including items, customer info, shipping details
     * @return Order
     * @throws InvalidArgumentException
     */
    public function createOrder(array $data): Order
    {
        // Validate required fields
        $this->validateOrderData($data);

        return DB::transaction(function () use ($data) {
            // Calculate totals from items
            $totals = $this->calculateOrderTotals($data['items'] ?? []);

            // Create the order
            $order = Order::create([
                'public_id' => $this->generatePublicId(),
                'merchant_id' => $data['merchant_id'],
                'store_id' => $data['store_id'],
                'customer_id' => $data['customer_id'],
                'fulfilment_type' => $data['fulfilment_type'],
                'status' => Order::STATUS_PENDING,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'items_total_cents' => $totals['items_total_cents'],
                'shipping_cost_cents' => $data['shipping_cost_cents'] ?? 0,
                'total_cents' => $totals['items_total_cents'] + ($data['shipping_cost_cents'] ?? 0),
                'pickup_time' => $data['pickup_time'] ?? null,
                'shipping_method' => $data['shipping_method'] ?? null,
                'shipping_status' => null,
                'tracking_code' => null,
                'tracking_url' => null,
                'shipping_name' => $data['shipping_name'] ?? null,
                'line1' => $data['line1'] ?? null,
                'line2' => $data['line2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'country' => $data['country'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
            ]);

            // Create order items
            foreach ($data['items'] as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price_cents' => $item['unit_price_cents'],
                    'addons' => $item['addons'] ?? null, // Keep JSON for backward compatibility
                ]);

                // Create order item customizations (options)
                if (isset($item['customizations']) && is_array($item['customizations'])) {
                    foreach ($item['customizations'] as $customization) {
                        \App\Models\OrderItemOption::create([
                            'order_item_id' => $orderItem->id,
                            'option_id' => $customization['option_id'] ?? null,
                            'qty' => 1,
                            'price_delta_cents' => $customization['price_delta_cents'] ?? 0,
                        ]);
                    }
                }

                // Create order item addons
                if (isset($item['addons']) && is_array($item['addons'])) {
                    foreach ($item['addons'] as $addon) {
                        $priceAdjustmentCents = isset($addon['price_adjustment'])
                            ? (int) round($addon['price_adjustment'] * 100)
                            : 0;

                        \App\Models\OrderItemAddon::create([
                            'order_item_id' => $orderItem->id,
                            'product_addon_id' => null, // We don't have this in the cart data
                            'name' => ($addon['addon_name'] ?? 'Addon') . ': ' . ($addon['option_name'] ?? ''),
                            'description' => null,
                            'quantity' => 1,
                            'unit_price_cents' => $priceAdjustmentCents,
                            'total_price_cents' => $priceAdjustmentCents,
                        ]);
                    }
                }
            }

            // Fire OrderCreated event
            event(new OrderCreated($order));

            return $order->fresh(['items', 'customer', 'store']);
        });
    }

    /**
     * Update order status with validation.
     *
     * @param Order $order
     * @param string $newStatus
     * @param array $additionalData Additional data to update (e.g., tracking info)
     * @return Order
     * @throws InvalidArgumentException
     */
    public function updateOrderStatus(Order $order, string $newStatus, array $additionalData = []): Order
    {
        $oldStatus = $order->status;

        // Validate the status transition
        if (!$this->validateStatusTransition($oldStatus, $newStatus)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$oldStatus}' to '{$newStatus}'"
            );
        }

        // Special handling for fulfilment-type-specific transitions
        if ($newStatus === Order::STATUS_PACKING && $order->fulfilment_type !== 'shipping') {
            throw new InvalidArgumentException(
                "Status 'packing' is only valid for shipping orders"
            );
        }

        if ($newStatus === Order::STATUS_READY_FOR_PICKUP && $order->fulfilment_type !== 'pickup') {
            throw new InvalidArgumentException(
                "Status 'ready_for_pickup' is only valid for pickup orders"
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $oldStatus, $additionalData) {
            // Prepare update data with lifecycle timestamps
            $updateData = array_merge($additionalData, [
                'status' => $newStatus,
            ]);

            // Set lifecycle timestamp based on new status (A1.2 Part 4 immutability requirement)
            switch ($newStatus) {
                case Order::STATUS_ACCEPTED:
                    $updateData['accepted_at'] = now();
                    break;

                case Order::STATUS_READY:
                case Order::STATUS_READY_FOR_PICKUP:
                    $updateData['ready_at'] = now();
                    break;

                case Order::STATUS_DELIVERED:
                case Order::STATUS_PICKED_UP:
                    $updateData['completed_at'] = now();
                    break;

                case Order::STATUS_CANCELLED:
                    $updateData['cancelled_at'] = now();
                    break;
            }

            // Update order with status and timestamp
            $order->update($updateData);

            // Reload order with relationships for broadcasting
            $order->load(['items.product', 'items.addons', 'customer']);

            // Fire OrderStatusUpdated event
            event(new OrderStatusUpdated($order, $oldStatus, $newStatus));

            // Handle loyalty points deduction if order is cancelled
            if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
                $loyaltyService = app(LoyaltyService::class);
                $deductionResult = $loyaltyService->deductPointsForCancelledOrder($order);

                if ($deductionResult['success']) {
                    \Log::info('Loyalty points deducted for cancelled order', [
                        'order_id' => $order->id,
                        'points_deducted' => $deductionResult['points_deducted'] ?? 0,
                    ]);
                }
            }

            return $order->fresh();
        });
    }

    /**
     * Update shipping information.
     *
     * @param Order $order
     * @param string|null $shippingStatus
     * @param string|null $trackingCode
     * @param string|null $trackingUrl
     * @return Order
     */
    public function updateShippingInfo(Order $order, ?string $shippingStatus, ?string $trackingCode, ?string $trackingUrl): Order
    {
        if ($order->fulfilment_type !== 'shipping') {
            throw new InvalidArgumentException(
                "Cannot update shipping info for non-shipping orders"
            );
        }

        $oldShippingStatus = $order->shipping_status;

        return DB::transaction(function () use ($order, $shippingStatus, $trackingCode, $trackingUrl, $oldShippingStatus) {
            $updateData = [];

            if ($shippingStatus !== null) {
                $updateData['shipping_status'] = $shippingStatus;
            }
            if ($trackingCode !== null) {
                $updateData['tracking_code'] = $trackingCode;
            }
            if ($trackingUrl !== null) {
                $updateData['tracking_url'] = $trackingUrl;
            }

            $order->update($updateData);

            // Fire ShippingStatusUpdated event if status changed
            if ($shippingStatus !== null && $shippingStatus !== $oldShippingStatus) {
                event(new ShippingStatusUpdated($order, $oldShippingStatus, $shippingStatus));
            }

            return $order->fresh();
        });
    }

    /**
     * Update shipping status.
     *
     * @param Order $order
     * @param string $shippingStatus
     * @param array $trackingData
     * @return Order
     */
    public function updateShippingStatus(Order $order, string $shippingStatus, array $trackingData = []): Order
    {
        if ($order->fulfilment_type !== 'shipping') {
            throw new InvalidArgumentException(
                "Cannot update shipping status for non-shipping orders"
            );
        }

        $oldShippingStatus = $order->shipping_status;

        return DB::transaction(function () use ($order, $shippingStatus, $trackingData, $oldShippingStatus) {
            $order->update(array_merge($trackingData, [
                'shipping_status' => $shippingStatus,
            ]));

            // Fire ShippingStatusUpdated event
            event(new ShippingStatusUpdated($order, $oldShippingStatus, $shippingStatus));

            return $order->fresh();
        });
    }

    /**
     * Validate status transition against allowed transitions.
     *
     * @param string $from Current status
     * @param string $to Target status
     * @return bool
     */
    public function validateStatusTransition(string $from, string $to): bool
    {
        // If transitioning to the same status, that's always valid (idempotent)
        if ($from === $to) {
            return true;
        }

        // Check if the transition is allowed
        if (!isset(self::STATUS_TRANSITIONS[$from])) {
            return false;
        }

        return in_array($to, self::STATUS_TRANSITIONS[$from]);
    }

    /**
     * Calculate order totals from items.
     *
     * @param array $items Array of items with qty and unit_price_cents
     * @return array ['items_total_cents' => int, 'total_cents' => int]
     */
    public function calculateOrderTotals(array $items): array
    {
        $itemsTotal = 0;

        foreach ($items as $item) {
            $qty = $item['qty'] ?? 0;
            $unitPrice = $item['unit_price_cents'] ?? 0;
            $itemsTotal += ($qty * $unitPrice);
        }

        return [
            'items_total_cents' => $itemsTotal,
            'total_cents' => $itemsTotal, // Will be adjusted with shipping in createOrder
        ];
    }

    /**
     * Generate unique public tracking ID.
     * Format: SF-XXXXX (e.g., SF-12345)
     *
     * @return string
     */
    public function generatePublicId(): string
    {
        do {
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $publicId = 'SF-' . $randomNumber;

            // Check if this ID already exists
            $exists = Order::where('public_id', $publicId)->exists();
        } while ($exists);

        return $publicId;
    }

    /**
     * Validate order data before creation.
     *
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function validateOrderData(array $data): void
    {
        $requiredFields = ['merchant_id', 'store_id', 'customer_id', 'fulfilment_type', 'items'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (!in_array($data['fulfilment_type'], ['pickup', 'shipping'])) {
            throw new InvalidArgumentException(
                "Invalid fulfilment_type. Must be 'pickup' or 'shipping'"
            );
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException("Order must have at least one item");
        }

        // Validate each item
        foreach ($data['items'] as $index => $item) {
            if (!isset($item['product_id']) || !isset($item['qty']) || !isset($item['unit_price_cents'])) {
                throw new InvalidArgumentException(
                    "Item at index {$index} is missing required fields (product_id, qty, unit_price_cents)"
                );
            }

            if ($item['qty'] <= 0) {
                throw new InvalidArgumentException(
                    "Item at index {$index} must have quantity greater than 0"
                );
            }

            if ($item['unit_price_cents'] < 0) {
                throw new InvalidArgumentException(
                    "Item at index {$index} cannot have negative unit price"
                );
            }
        }

        // Validate shipping fields for shipping orders
        if ($data['fulfilment_type'] === 'shipping') {
            $requiredShippingFields = ['shipping_name', 'line1', 'city', 'state', 'postcode', 'country'];
            foreach ($requiredShippingFields as $field) {
                if (empty($data[$field])) {
                    throw new InvalidArgumentException(
                        "Missing required shipping field: {$field}"
                    );
                }
            }
        }

        // Validate pickup time for pickup orders
        if ($data['fulfilment_type'] === 'pickup' && empty($data['pickup_time'])) {
            throw new InvalidArgumentException(
                "Pickup orders must have a pickup_time"
            );
        }
    }

    /**
     * Get allowed next statuses for a given status.
     *
     * @param string $currentStatus
     * @return array
     */
    public function getAllowedNextStatuses(string $currentStatus): array
    {
        return self::STATUS_TRANSITIONS[$currentStatus] ?? [];
    }

    /**
     * Check if an order can be cancelled.
     *
     * @param Order $order
     * @return bool
     */
    public function canBeCancelled(Order $order): bool
    {
        // Orders can be cancelled before they reach terminal states
        $nonCancellableStatuses = [
            Order::STATUS_DELIVERED,
            Order::STATUS_PICKED_UP,
            Order::STATUS_CANCELLED,
        ];

        return !in_array($order->status, $nonCancellableStatuses);
    }

    /**
     * Cancel an order.
     *
     * @param Order $order
     * @param string|null $reason
     * @return Order
     * @throws InvalidArgumentException
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if (!$this->canBeCancelled($order)) {
            throw new InvalidArgumentException(
                "Order cannot be cancelled in current status: {$order->status}"
            );
        }

        $additionalData = [];
        if ($reason) {
            $additionalData['cancellation_reason'] = $reason;
        }

        return $this->updateOrderStatus($order, Order::STATUS_CANCELLED, $additionalData);
    }
}
