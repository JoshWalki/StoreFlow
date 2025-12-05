<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Customer;
use App\Models\CustomizationOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Product;
use App\Models\Store;
use App\Services\Shipping\ShippingEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected ShippingEngine $shippingEngine;

    public function __construct(ShippingEngine $shippingEngine)
    {
        $this->shippingEngine = $shippingEngine;
    }

    /**
     * Process checkout and create order.
     *
     * @param CheckoutRequest $request
     * @param Store $store
     * @return JsonResponse
     */
    public function checkout(CheckoutRequest $request, Store $store): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Validate fulfilment type specific requirements
            if ($validated['fulfilment_type'] === 'delivery') {
                if (!$store->shipping_enabled) {
                    return response()->json([
                        'message' => 'Delivery is not available for this store.',
                    ], 422);
                }

                // Validate shipping is available for the destination
                $shippingAvailable = $this->shippingEngine->isShippingAvailable(
                    $store->merchant_id,
                    $store->id,
                    $validated['shipping_address']['country'],
                    $validated['shipping_address']['state'] ?? null,
                    $validated['shipping_address']['postcode'] ?? null
                );

                if (!$shippingAvailable) {
                    return response()->json([
                        'message' => 'Shipping is not available for this destination.',
                    ], 422);
                }
            }

            // Find or create customer
            $customer = $this->findOrCreateCustomer(
                $store->merchant_id,
                $validated['contact']
            );

            // Calculate totals
            $calculationResult = $this->calculateOrderTotals(
                $validated['items'],
                $validated['fulfilment_type'],
                $store,
                $validated['shipping_method_id'] ?? null,
                $validated['shipping_address'] ?? null
            );

            if (isset($calculationResult['error'])) {
                DB::rollBack();
                return response()->json([
                    'message' => $calculationResult['error'],
                ], 422);
            }

            // Create order
            $order = $this->createOrder(
                $store,
                $customer,
                $validated,
                $calculationResult
            );

            // Create order items
            $this->createOrderItems($order, $validated['items'], $calculationResult['items']);

            DB::commit();

            // Broadcast event
            broadcast(new OrderCreated($order));

            return response()->json([
                'order_id' => $order->id,
                'public_id' => $order->public_id,
                'status' => $order->status,
                'total_cents' => $order->total_cents,
                'created_at' => $order->created_at->toIso8601String(),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing your order.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Find existing customer or create a new guest customer.
     *
     * @param int $merchantId
     * @param array $contactInfo
     * @return Customer
     */
    protected function findOrCreateCustomer(int $merchantId, array $contactInfo): Customer
    {
        // Try to find existing customer by email
        $customer = Customer::where('merchant_id', $merchantId)
            ->where('email', $contactInfo['email'])
            ->first();

        if ($customer) {
            // Update customer info if provided
            $customer->update([
                'first_name' => $contactInfo['first_name'],
                'last_name' => $contactInfo['last_name'],
                'mobile' => $contactInfo['mobile'] ?? $customer->mobile,
            ]);

            return $customer;
        }

        // Create new guest customer
        return Customer::create([
            'merchant_id' => $merchantId,
            'first_name' => $contactInfo['first_name'],
            'last_name' => $contactInfo['last_name'],
            'email' => $contactInfo['email'],
            'mobile' => $contactInfo['mobile'] ?? null,
        ]);
    }

    /**
     * Calculate order totals including items and shipping.
     *
     * @param array $items
     * @param string $fulfilmentType
     * @param Store $store
     * @param int|null $shippingMethodId
     * @param array|null $shippingAddress
     * @return array
     */
    protected function calculateOrderTotals(
        array $items,
        string $fulfilmentType,
        Store $store,
        ?int $shippingMethodId = null,
        ?array $shippingAddress = null
    ): array {
        $itemsTotal = 0;
        $shippingCost = 0;
        $processedItems = [];

        // Get all products
        $productIds = collect($items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Calculate items total
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                return ['error' => "Product with ID {$item['product_id']} is not available."];
            }

            $itemTotal = $product->price_cents * $item['qty'];

            // Add customization costs
            $customizationCost = 0;
            if (!empty($item['customizations'])) {
                $optionIds = collect($item['customizations'])->pluck('option_id');
                $options = CustomizationOption::whereIn('id', $optionIds)->get()->keyBy('id');

                foreach ($item['customizations'] as $customization) {
                    $option = $options->get($customization['option_id']);
                    if ($option) {
                        $customizationCost += $option->price_delta_cents * $item['qty'];
                    }
                }
            }

            $itemTotal += $customizationCost;
            $itemsTotal += $itemTotal;

            $processedItems[] = [
                'product' => $product,
                'qty' => $item['qty'],
                'unit_price_cents' => $product->price_cents,
                'customizations' => $item['customizations'] ?? [],
            ];
        }

        // Calculate shipping cost for delivery orders
        if ($fulfilmentType === 'delivery' && $shippingMethodId && $shippingAddress) {
            // Build cart items for ShippingEngine
            $cartItems = collect($processedItems)->map(function ($item) {
                return [
                    'product_id' => $item['product']->id,
                    'quantity' => $item['qty'],
                    'price_cents' => $item['product']->price_cents,
                    'weight_grams' => $item['product']->weight_grams ?? 0,
                ];
            })->all();

            // Get shipping quote for selected method
            $quote = $this->shippingEngine->getShippingQuote($shippingMethodId, $cartItems);

            if (!$quote) {
                return ['error' => 'The selected shipping method is not available.'];
            }

            $shippingCost = $quote['cost_cents'];
        }

        return [
            'items_total_cents' => $itemsTotal,
            'shipping_cost_cents' => $shippingCost,
            'total_cents' => $itemsTotal + $shippingCost,
            'items' => $processedItems,
        ];
    }

    /**
     * Create the order record.
     *
     * @param Store $store
     * @param Customer $customer
     * @param array $validated
     * @param array $calculationResult
     * @return Order
     */
    protected function createOrder(
        Store $store,
        Customer $customer,
        array $validated,
        array $calculationResult
    ): Order {
        $orderData = [
            'public_id' => 'ORD-' . strtoupper(Str::random(12)),
            'merchant_id' => $store->merchant_id,
            'store_id' => $store->id,
            'customer_id' => $customer->id,

            // Customer snapshot (CRITICAL: Freeze contact details for immutability)
            'customer_name' => trim($customer->first_name . ' ' . $customer->last_name),
            'customer_email' => $customer->email,
            'customer_mobile' => $customer->mobile,

            // Order details
            'fulfilment_type' => $validated['fulfilment_type'] === 'delivery' ? 'shipping' : $validated['fulfilment_type'],
            'status' => 'pending',

            // Payment tracking
            'payment_status' => 'unpaid',
            'payment_method' => $validated['payment_method'] ?? 'card',
            'payment_reference' => null, // Will be set by payment gateway

            // Financial breakdown
            'items_total_cents' => $calculationResult['items_total_cents'],
            'discount_cents' => $validated['discount_cents'] ?? 0,
            'tax_cents' => $validated['tax_cents'] ?? 0,
            'shipping_cost_cents' => $calculationResult['shipping_cost_cents'],
            'total_cents' => $calculationResult['total_cents'] + ($validated['discount_cents'] ?? 0) + ($validated['tax_cents'] ?? 0),

            // Lifecycle timestamps
            'placed_at' => now(), // Order is placed at creation
        ];

        // Add delivery-specific fields
        if ($validated['fulfilment_type'] === 'delivery') {
            $orderData['shipping_method'] = $validated['shipping_method_id'];
            $orderData['shipping_status'] = 'pending';
            $orderData['shipping_name'] = $validated['shipping_address']['name'];
            $orderData['shipping_line1'] = $validated['shipping_address']['line1'];
            $orderData['shipping_line2'] = $validated['shipping_address']['line2'] ?? null;
            $orderData['city'] = $validated['shipping_address']['city'];
            $orderData['state'] = $validated['shipping_address']['state'];
            $orderData['postcode'] = $validated['shipping_address']['postcode'];
            $orderData['country'] = $validated['shipping_address']['country'];
        }

        // Add pickup-specific fields
        if ($validated['fulfilment_type'] === 'pickup') {
            $orderData['pickup_time'] = $validated['pickup_time'] ?? null;
            $orderData['pickup_notes'] = $validated['pickup_notes'] ?? null;
        }

        return Order::create($orderData);
    }

    /**
     * Create order items and their customization options.
     *
     * @param Order $order
     * @param array $requestItems
     * @param array $processedItems
     * @return void
     */
    protected function createOrderItems(Order $order, array $requestItems, array $processedItems): void
    {
        foreach ($processedItems as $index => $item) {
            $product = $item['product'];
            $quantity = $item['qty'];

            // Calculate customization cost per item
            $customizationCostPerUnit = 0;
            if (!empty($item['customizations'])) {
                $optionIds = collect($item['customizations'])->pluck('option_id');
                $options = CustomizationOption::whereIn('id', $optionIds)->get()->keyBy('id');

                foreach ($item['customizations'] as $customization) {
                    $option = $options->get($customization['option_id']);
                    if ($option) {
                        $customizationCostPerUnit += $option->price_delta_cents;
                    }
                }
            }

            // Calculate financial breakdown
            $unitPriceTotal = $product->price_cents + $customizationCostPerUnit;
            $lineSubtotal = $unitPriceTotal * $quantity;
            $taxAmount = 0; // TODO: Implement tax calculation based on store tax rate
            $lineTotal = $lineSubtotal + $taxAmount;

            // Create order item with frozen product data (CRITICAL for immutability)
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,

                // Frozen product data - preserves product info even if product changes/deleted
                'name' => $product->name,
                'sku' => $product->sku ?? null,

                // Quantity and pricing
                'quantity' => $quantity,
                'unit_price_cents' => $unitPriceTotal, // Includes customizations

                // Financial breakdown
                'line_subtotal_cents' => $lineSubtotal,
                'tax_cents' => $taxAmount,
                'total_cents' => $lineTotal,
            ]);

            // Create customization options
            if (!empty($item['customizations'])) {
                $optionIds = collect($item['customizations'])->pluck('option_id');
                $options = CustomizationOption::whereIn('id', $optionIds)->get()->keyBy('id');

                foreach ($item['customizations'] as $customization) {
                    $option = $options->get($customization['option_id']);
                    if ($option) {
                        OrderItemOption::create([
                            'order_item_id' => $orderItem->id,
                            'option_id' => $customization['option_id'],
                            'qty' => $quantity,
                            'price_delta_cents' => $option->price_delta_cents,
                        ]);
                    }
                }
            }
        }
    }
}
