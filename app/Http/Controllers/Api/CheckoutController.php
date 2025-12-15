<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Customer;
use App\Models\CustomizationOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\OrderItemOption;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\Store;
use App\Services\AddonPricingService;
use App\Services\Shipping\ShippingEngine;
use App\Services\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected ShippingEngine $shippingEngine;
    protected AddonPricingService $addonPricingService;
    protected StripeConnectService $stripeConnectService;

    public function __construct(
        ShippingEngine $shippingEngine,
        AddonPricingService $addonPricingService,
        StripeConnectService $stripeConnectService
    ) {
        $this->shippingEngine = $shippingEngine;
        $this->addonPricingService = $addonPricingService;
        $this->stripeConnectService = $stripeConnectService;
    }

    /**
     * Create a Stripe PaymentIntent for checkout.
     *
     * @param CheckoutRequest $request
     * @param Store $store
     * @return JsonResponse
     */
    public function createPaymentIntent(CheckoutRequest $request, Store $store): JsonResponse
    {
        $validated = $request->validated();

        // Check if store is active
        if (!$store->is_active) {
            return response()->json([
                'message' => 'This store is currently not accepting orders.',
            ], 422);
        }

        try {
            // Calculate order totals
            $calculationResult = $this->calculateOrderTotals(
                $validated['items'],
                $validated['fulfilment_type'],
                $store,
                $validated['shipping_method_id'] ?? null,
                $validated['shipping_address'] ?? null
            );

            if (isset($calculationResult['error'])) {
                return response()->json([
                    'message' => $calculationResult['error'],
                ], 422);
            }

            $totalCents = $calculationResult['total_cents'];

            // Add discount/tax if provided
            if (isset($validated['discount_cents'])) {
                $totalCents += $validated['discount_cents'];
            }
            if (isset($validated['tax_cents'])) {
                $totalCents += $validated['tax_cents'];
            }

            // Find or create customer and potentially Stripe Customer
            $customer = $this->findOrCreateCustomer(
                $store->merchant_id,
                $validated['contact']
            );

            $stripeCustomerId = null;

            // If creating account with password, create Stripe Customer
            if (!empty($validated['account']['password']) && !$customer->stripe_customer_id) {
                try {
                    $stripeCustomer = $this->stripeConnectService->createStripeCustomer(
                        $store->merchant,
                        $validated['contact']
                    );

                    $customer->update([
                        'stripe_customer_id' => $stripeCustomer['id'],
                    ]);

                    $stripeCustomerId = $stripeCustomer['id'];

                    \Log::info('Stripe Customer created during checkout', [
                        'customer_id' => $customer->id,
                        'stripe_customer_id' => $stripeCustomerId,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to create Stripe Customer, continuing as guest', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue as guest if Stripe Customer creation fails
                }
            } else if ($customer->stripe_customer_id) {
                // Use existing Stripe Customer ID
                $stripeCustomerId = $customer->stripe_customer_id;
            }

            // Create Stripe PaymentIntent with destination charge
            $merchant = $store->merchant;

            $paymentIntent = $this->stripeConnectService->createPaymentIntent(
                $merchant,
                $totalCents,
                [
                    'store_id' => $store->id,
                    'order_type' => 'checkout',
                    'customer_id' => $customer->id,
                ],
                $stripeCustomerId
            );

            return response()->json([
                'client_secret' => $paymentIntent['client_secret'],
                'amount_cents' => $totalCents,
                'payment_intent_id' => $paymentIntent['id'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create payment intent', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to initialize payment.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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

        // Check if store is active
        if (!$store->is_active) {
            return response()->json([
                'message' => 'This store is currently not accepting orders.',
            ], 422);
        }

        \Log::info('Checkout request received', [
            'store_id' => $store->id,
            'items' => $validated['items'] ?? [],
            'payment_intent_id' => $validated['payment_intent_id'] ?? null
        ]);

        // DEBUG: Write to file
        file_put_contents(
            storage_path('logs/checkout-debug.txt'),
            date('Y-m-d H:i:s') . " - Checkout request:\n" .
            json_encode($validated['items'], JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );

        try {
            // Verify payment intent if provided
            $paymentIntentId = $validated['payment_intent_id'] ?? null;
            $paymentStatus = 'unpaid';
            $paymentReference = null;

            if ($paymentIntentId) {
                try {
                    // Retry logic to handle race conditions with payment processing
                    $maxRetries = 3;
                    $retryDelay = 500; // milliseconds
                    $paymentIntent = null;

                    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                        try {
                            $paymentIntent = $this->stripeConnectService->retrievePaymentIntent($paymentIntentId);
                            break; // Success, exit retry loop
                        } catch (\Exception $e) {
                            $errorCode = $e->getCode();
                            $errorMessage = $e->getMessage();

                            \Log::warning('Payment intent retrieval attempt failed', [
                                'attempt' => $attempt,
                                'payment_intent_id' => $paymentIntentId,
                                'error' => $errorMessage,
                                'error_code' => $errorCode,
                            ]);

                            // If this is not the last attempt and error is retriable, wait and retry
                            if ($attempt < $maxRetries && str_contains($errorMessage, 'unexpected_state')) {
                                usleep($retryDelay * 1000); // Convert to microseconds
                                $retryDelay *= 2; // Exponential backoff
                                continue;
                            }

                            // If last attempt or non-retriable error, throw exception
                            throw $e;
                        }
                    }

                    if (!$paymentIntent) {
                        throw new \Exception('Failed to retrieve payment intent after retries');
                    }

                    // Verify payment is successful
                    if ($paymentIntent['status'] !== 'succeeded') {
                        return response()->json([
                            'message' => 'Payment has not been completed. Please complete payment before checkout.',
                            'payment_status' => $paymentIntent['status'],
                        ], 422);
                    }

                    $paymentStatus = 'paid';
                    $paymentReference = $paymentIntentId;

                } catch (\Exception $e) {
                    \Log::error('Failed to verify payment intent', [
                        'payment_intent_id' => $paymentIntentId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return response()->json([
                        'message' => 'Failed to verify payment. Please try again.',
                        'error' => config('app.debug') ? $e->getMessage() : null,
                    ], 422);
                }
            }

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

            // Create order with payment status
            $order = $this->createOrder(
                $store,
                $customer,
                $validated,
                $calculationResult,
                $paymentStatus,
                $paymentReference
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

            // Add addon costs
            $addonCost = 0;
            $processedAddons = [];
            if (!empty($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    // Addon data comes from cart with price_adjustment in dollars
                    $priceAdjustmentCents = isset($addon['price_adjustment'])
                        ? (int) round($addon['price_adjustment'] * 100)
                        : 0;

                    $addonQty = $addon['quantity'] ?? 1;
                    $addonCost += $priceAdjustmentCents * $addonQty * $item['qty'];

                    $processedAddons[] = [
                        'addon_name' => $addon['addon_name'] ?? 'Addon',
                        'option_name' => $addon['option_name'] ?? '',
                        'price_adjustment' => $addon['price_adjustment'] ?? 0,
                        'quantity' => $addonQty,
                    ];
                }
            }

            $itemTotal += $customizationCost + $addonCost;
            $itemsTotal += $itemTotal;

            $processedItems[] = [
                'product' => $product,
                'qty' => $item['qty'],
                'unit_price_cents' => $product->price_cents,
                'customizations' => $item['customizations'] ?? [],
                'addons' => $processedAddons,
                'specialMessage' => $item['specialMessage'] ?? null,
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
     * @param string $paymentStatus
     * @param string|null $paymentReference
     * @return Order
     */
    protected function createOrder(
        Store $store,
        Customer $customer,
        array $validated,
        array $calculationResult,
        string $paymentStatus = 'unpaid',
        ?string $paymentReference = null
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
            'payment_status' => $paymentStatus,
            'payment_method' => $validated['payment_method'] ?? 'card',
            'payment_reference' => $paymentReference,

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
        \Log::info('createOrderItems called', [
            'order_id' => $order->id,
            'processedItems_count' => count($processedItems),
            'processedItems' => $processedItems,
            'requestItems' => $requestItems
        ]);

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

                // Special instructions
                'special_instructions' => $item['specialMessage'] ?? null,
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

            // Create addon selections (frozen snapshot)
            if (!empty($item['addons'])) {
                \Log::info('Creating addon records', [
                    'order_item_id' => $orderItem->id,
                    'addons' => $item['addons']
                ]);

                foreach ($item['addons'] as $addonData) {
                    $addonQty = $addonData['quantity'] ?? 1;
                    $priceAdjustmentCents = isset($addonData['price_adjustment'])
                        ? (int) round($addonData['price_adjustment'] * 100)
                        : 0;

                    $addonRecord = [
                        'order_item_id' => $orderItem->id,
                        'product_addon_id' => null,
                        'name' => ($addonData['addon_name'] ?? 'Addon') . ': ' . ($addonData['option_name'] ?? ''),
                        'description' => null,
                        'quantity' => $addonQty,
                        'unit_price_cents' => $priceAdjustmentCents,
                        'total_price_cents' => $priceAdjustmentCents * $addonQty,
                    ];

                    \Log::info('Creating OrderItemAddon', $addonRecord);

                    // Store addon selection with frozen data
                    $created = OrderItemAddon::create($addonRecord);

                    \Log::info('Created OrderItemAddon', ['id' => $created->id]);
                }
            } else {
                \Log::info('No addons to create for order item', ['order_item_id' => $orderItem->id]);
            }
        }
    }
}
