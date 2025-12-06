<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Events\OrderCreated;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\LoyaltyConfig;
use App\Models\Store;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Products\ProductDisplayService;
use App\Services\Shipping\ShippingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /**
     * Display the storefront homepage with frequent products and category sections.
     */
    public function index(Store $store, ProductDisplayService $productDisplayService): Response
    {
        // Get storefront data (frequent products + products by category)
        $storefrontData = $productDisplayService->getStorefrontData($store);

        // Get authenticated customer if logged in
        $customer = Auth::guard('customer')->user();
        $customerData = null;
        if ($customer && $customer->merchant_id === $store->merchant_id) {
            $customerData = [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ];
        }

        return Inertia::render('Storefront/Index', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'merchant_id' => $store->merchant_id,
                'theme' => $store->theme_key ?? 'classic',
                'logo_url' => $store->logo_path ? asset('storage/' . $store->logo_path) : null,
                'is_active' => $store->is_active ?? true,
                'open_time' => $store->open_time ? substr($store->open_time, 0, 5) : null,
                'close_time' => $store->close_time ? substr($store->close_time, 0, 5) : null,
            ],
            'frequent_products' => $storefrontData['frequent_products'],
            'categories' => $storefrontData['categories'],
            'customer' => $customerData,
        ]);
    }

    /**
     * Display products listing.
     */
    public function products(Request $request, Store $store): Response
    {
        $query = Product::with(['category', 'images'])
            ->where('merchant_id', $store->merchant_id)
            ->where(function ($q) use ($store) {
                $q->where('store_id', $store->id)
                  ->orWhereNull('store_id');
            })
            ->where('is_active', true);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(function ($product) {
                $primaryImage = $product->images->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price_cents' => $product->price_cents,
                    'category' => $product->category ? $product->category->name : null,
                    'is_active' => $product->is_active,
                    'is_shippable' => $product->is_shippable,
                    'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                    'images' => $product->images->map(fn($img) => '/storage/' . $img->image_path),
                ];
            });

        // Get all categories for filter (categories are merchant-wide, not store-specific)
        $categories = Category::where('merchant_id', $store->merchant_id)
            ->where('is_active', true)
            ->withCount('products')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products_count' => $category->products_count,
                ];
            });

        // Get authenticated customer if logged in
        $customer = Auth::guard('customer')->user();
        $customerData = null;
        if ($customer && $customer->merchant_id === $store->merchant_id) {
            $customerData = [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ];
        }

        return Inertia::render('Storefront/Products', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'theme' => $store->theme_key ?? 'classic',
                'logo_url' => $store->logo_path ? asset('storage/' . $store->logo_path) : null,
                'is_active' => $store->is_active ?? true,
                'open_time' => $store->open_time ? substr($store->open_time, 0, 5) : null,
                'close_time' => $store->close_time ? substr($store->close_time, 0, 5) : null,
            ],
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
            ],
            'customer' => $customerData,
        ]);
    }

    /**
     * Display a single product.
     */
    public function showProduct(Store $store, Product $product): Response
    {
        // Ensure product belongs to this store or is merchant-wide
        if ($product->merchant_id !== $store->merchant_id) {
            abort(404);
        }

        if ($product->store_id !== null && $product->store_id !== $store->id) {
            abort(404);
        }

        // Load product images
        $product->load(['category', 'images']);

        // Load related products from same category
        $relatedProducts = Product::with('images')
            ->where('merchant_id', $store->merchant_id)
            ->where(function ($q) use ($store) {
                $q->where('store_id', $store->id)
                  ->orWhereNull('store_id');
            })
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get()
            ->map(function ($relatedProduct) {
                $primaryImage = $relatedProduct->images->where('is_primary', true)->first();
                return [
                    'id' => $relatedProduct->id,
                    'name' => $relatedProduct->name,
                    'price_cents' => $relatedProduct->price_cents,
                    'is_shippable' => $relatedProduct->is_shippable,
                    'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                ];
            });

        $primaryImage = $product->images->where('is_primary', true)->first();

        // Get authenticated customer if logged in
        $customer = Auth::guard('customer')->user();
        $customerData = null;
        if ($customer && $customer->merchant_id === $store->merchant_id) {
            $customerData = [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ];
        }

        return Inertia::render('Storefront/ProductDetail', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'theme' => $store->theme_key ?? 'classic',
                'logo_url' => $store->logo_path ? asset('storage/' . $store->logo_path) : null,
                'is_active' => $store->is_active ?? true,
            ],
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'category' => $product->category ? $product->category->name : null,
                'is_active' => $product->is_active,
                'is_shippable' => $product->is_shippable,
                'weight_grams' => $product->weight_grams,
                'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                'images' => $product->images->map(fn($img) => [
                    'id' => $img->id,
                    'path' => '/storage/' . $img->image_path,
                    'is_primary' => $img->is_primary,
                ]),
            ],
            'relatedProducts' => $relatedProducts,
            'customer' => $customerData,
        ]);
    }

    /**
     * Display checkout page.
     */
    public function checkout(Store $store): Response
    {
        // Get available shipping methods for quotes
        $shippingEngine = app(ShippingEngine::class);
        $zones = $shippingEngine->getAvailableZones($store->merchant_id, $store->id);

        // Get authenticated customer if logged in
        $customer = Auth::guard('customer')->user();
        $customerData = null;
        $loyaltyReward = null;

        if ($customer && $customer->merchant_id === $store->merchant_id) {
            $customerData = [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'address_line1' => $customer->address_line1,
                'address_line2' => $customer->address_line2,
                'address_city' => $customer->address_city,
                'address_state' => $customer->address_state,
                'address_postcode' => $customer->address_postcode,
                'address_country' => $customer->address_country,
            ];

            // Get loyalty reward eligibility
            $loyaltyService = app(LoyaltyService::class);
            $loyaltyAccount = $customer->loyaltyAccount;

            if ($loyaltyAccount) {
                $loyaltyConfig = LoyaltyConfig::where('merchant_id', $store->merchant_id)
                    ->where('is_active', true)
                    ->first();

                if ($loyaltyConfig) {
                    $loyaltyReward = [
                        'points_balance' => $loyaltyAccount->points_balance,
                        'threshold' => $loyaltyConfig->threshold,
                        'eligible' => $loyaltyAccount->hasReachedThreshold($loyaltyConfig->threshold),
                        'reward_config' => $loyaltyConfig->reward_json,
                    ];
                }
            }
        }

        return Inertia::render('Storefront/Checkout', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'merchant_id' => $store->merchant_id,
                'shipping_enabled' => $store->shipping_enabled,
                'theme' => $store->theme_key ?? 'classic',
                'is_active' => $store->is_active ?? true,
            ],
            'zones' => $zones,
            'customer' => $customerData,
            'loyaltyReward' => $loyaltyReward,
        ]);
    }

    /**
     * Process checkout and create order.
     */
    public function processCheckout(Request $request, Store $store)
    {
        $validated = $request->validate([
            'contact' => 'required|array',
            'contact.first_name' => 'required|string|max:255',
            'contact.last_name' => 'required|string|max:255',
            'contact.email' => 'required|email|max:255',
            'contact.mobile' => 'nullable|string|max:20',
            'account' => 'nullable|array',
            'account.password' => 'nullable|string|min:8|confirmed',
            'account.password_confirmation' => 'nullable|string|min:8',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
            'fulfilment_type' => 'required|in:pickup,shipping',
            'shipping_address' => 'required_if:fulfilment_type,shipping|array',
            'shipping_address.name' => 'required_if:fulfilment_type,shipping|string',
            'shipping_address.line1' => 'required_if:fulfilment_type,shipping|string',
            'shipping_address.line2' => 'nullable|string',
            'shipping_address.city' => 'required_if:fulfilment_type,shipping|string',
            'shipping_address.state' => 'required_if:fulfilment_type,shipping|string',
            'shipping_address.postcode' => 'required_if:fulfilment_type,shipping|string',
            'shipping_address.country' => 'required_if:fulfilment_type,shipping|string|max:100',
            'shipping_method_id' => 'required_if:fulfilment_type,shipping|exists:shipping_methods,id',
            'payment_method' => 'nullable|string',
            'apply_loyalty_reward' => 'nullable|boolean',
        ]);

        // Check if store is active
        if (!$store->is_active) {
            return back()->withErrors([
                'store' => 'This store is currently closed and not accepting orders. Please check back during operating hours.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Validate that all items are shippable if shipping is selected
            if ($validated['fulfilment_type'] === 'shipping') {
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    if (!$product->is_shippable) {
                        return back()->withErrors([
                            'error' => 'Your cart contains pickup-only items that cannot be shipped. Please select pickup or remove these items.'
                        ]);
                    }
                }
            }

            // Find or create customer
            $customerData = [
                'first_name' => $validated['contact']['first_name'],
                'last_name' => $validated['contact']['last_name'],
                'mobile' => $validated['contact']['mobile'] ?? null,
            ];

            // Add password if provided (account creation during checkout)
            $createAccount = false;
            if (!empty($validated['account']['password'])) {
                $customerData['password'] = Hash::make($validated['account']['password']);
                $createAccount = true;
            }

            $customer = Customer::firstOrCreate(
                [
                    'email' => $validated['contact']['email'],
                    'merchant_id' => $store->merchant_id,
                ],
                $customerData
            );

            // If customer already exists but provided new password, update it
            if (!$customer->wasRecentlyCreated && $createAccount && empty($customer->password)) {
                $customer->update(['password' => Hash::make($validated['account']['password'])]);
            }

            // Calculate order totals
            $itemsTotalCents = 0;
            $cartItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $lineTotal = $product->price_cents * $item['quantity'];
                $itemsTotalCents += $lineTotal;

                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price_cents' => $product->price_cents,
                    'line_total_cents' => $lineTotal,
                ];
            }

            // Calculate shipping if needed
            $shippingCostCents = 0;
            $shippingMethodName = null;

            if ($validated['fulfilment_type'] === 'shipping') {
                $shippingEngine = app(ShippingEngine::class);

                // Build cart items for shipping calculation
                $shippingCartItems = collect($cartItems)->map(function ($item) {
                    return [
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'price_cents' => $item['product']->price_cents,
                        'weight_grams' => $item['product']->weight_grams ?? 0,
                    ];
                })->toArray();

                $quote = $shippingEngine->getShippingQuote(
                    $validated['shipping_method_id'],
                    $shippingCartItems
                );

                if ($quote) {
                    $shippingCostCents = $quote['cost_cents'];
                    $shippingMethodName = $quote['shipping_method_name'];
                }
            }

            $totalCents = $itemsTotalCents + $shippingCostCents;

            // Apply loyalty discount if requested and eligible
            $loyaltyDiscountCents = 0;
            if (!empty($validated['apply_loyalty_reward']) && $customer) {
                $loyaltyService = app(LoyaltyService::class);

                try {
                    // Apply redemption and get discount amount
                    $redemptionResult = $loyaltyService->applyRedemption($customer, $totalCents);

                    if ($redemptionResult['success']) {
                        $loyaltyDiscountCents = $redemptionResult['discount_cents'];
                        $totalCents -= $loyaltyDiscountCents;

                        \Log::info('Loyalty discount applied during checkout', [
                            'customer_id' => $customer->id,
                            'order_total_before' => $itemsTotalCents + $shippingCostCents,
                            'discount_cents' => $loyaltyDiscountCents,
                            'order_total_after' => $totalCents,
                            'points_redeemed' => $redemptionResult['points_redeemed'],
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to apply loyalty discount', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with order without discount if redemption fails
                }
            }

            // Create order
            $order = Order::create([
                'public_id' => 'ORD-' . strtoupper(Str::random(12)),
                'merchant_id' => $store->merchant_id,
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'customer_email' => $customer->email,
                'customer_mobile' => $customer->mobile,
                'fulfilment_type' => $validated['fulfilment_type'],
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PAID, // Simulated payment - always paid
                'payment_method' => $validated['payment_method'] ?? 'simulated',
                'payment_reference' => 'SIM-' . strtoupper(Str::random(16)), // Simulated payment reference
                'items_total_cents' => $itemsTotalCents,
                'shipping_cost_cents' => $shippingCostCents,
                'total_cents' => $totalCents,
                'placed_at' => now(),
                // Shipping details
                'shipping_method' => $shippingMethodName,
                'shipping_name' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['name'] : null,
                'shipping_line1' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['line1'] : null,
                'shipping_line2' => $validated['fulfilment_type'] === 'shipping' ? ($validated['shipping_address']['line2'] ?? null) : null,
                'city' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['city'] : null,
                'state' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['state'] : null,
                'postcode' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['postcode'] : null,
                'country' => $validated['fulfilment_type'] === 'shipping' ? $validated['shipping_address']['country'] : null,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'sku' => $item['product']->sku ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price_cents' => $item['unit_price_cents'],
                    'line_subtotal_cents' => $item['line_total_cents'],
                    'tax_cents' => 0,
                    'total_cents' => $item['line_total_cents'],
                ]);
            }

            DB::commit();

            // Process loyalty points for completed order (payment_status is always PAID)
            $loyaltyService = app(LoyaltyService::class);
            $loyaltyResult = $loyaltyService->processOrderLoyalty($order);

            // Log loyalty processing result for debugging
            if ($loyaltyResult['success']) {
                \Log::info('Loyalty points awarded', [
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'points_awarded' => $loyaltyResult['points_awarded'] ?? 0,
                    'reward_earned' => $loyaltyResult['reward'] !== null,
                ]);
            }

            // Broadcast order created event (for websocket)
            event(new OrderCreated($order));

            // If customer created account, log them in automatically
            if ($createAccount) {
                Auth::guard('customer')->login($customer);
                $request->session()->regenerate();
                session(['customer_store_id' => $store->id]);
            }

            return redirect()->route('storefront.order.success', ['store' => $store->id, 'order' => $order->public_id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process order: ' . $e->getMessage()]);
        }
    }

    /**
     * Display order success/confirmation page.
     */
    public function orderSuccess(Store $store, string $order): Response
    {
        // Find order by public_id
        $orderRecord = Order::with(['items.product.images', 'customer'])
            ->where('public_id', $order)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // Build shipping address from order fields
        $shippingAddress = null;
        if ($orderRecord->fulfilment_type === 'shipping') {
            $shippingAddress = [
                'name' => $orderRecord->shipping_name,
                'address_line_1' => $orderRecord->shipping_line1,
                'address_line_2' => $orderRecord->shipping_line2,
                'city' => $orderRecord->city,
                'state' => $orderRecord->state,
                'postcode' => $orderRecord->postcode,
                'country' => $orderRecord->country,
            ];
        }

        return Inertia::render('Storefront/OrderSuccess', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'theme' => $store->theme_key ?? 'classic',
                'contact_email' => $store->contact_email,
                'contact_phone' => $store->contact_phone,
                'address_primary' => $store->address_primary,
                'address_city' => $store->address_city,
                'address_state' => $store->address_state,
                'address_postcode' => $store->address_postcode,
            ],
            'order' => [
                'id' => $orderRecord->id,
                'public_id' => $orderRecord->public_id,
                'status' => $orderRecord->status,
                'payment_status' => $orderRecord->payment_status,
                'fulfilment_type' => $orderRecord->fulfilment_type,
                'customer_name' => $orderRecord->customer_name,
                'customer_email' => $orderRecord->customer_email,
                'customer_mobile' => $orderRecord->customer_mobile,
                'shipping_address' => $shippingAddress,
                'items' => $orderRecord->items->map(function ($item) {
                    $imageUrl = null;
                    if ($item->product && $item->product->images) {
                        $primaryImage = $item->product->images->where('is_primary', true)->first();
                        if ($primaryImage && isset($primaryImage->image_path)) {
                            $imageUrl = '/storage/' . $primaryImage->image_path;
                        }
                    }

                    return [
                        'product_name' => $item->name,
                        'quantity' => $item->quantity,
                        'unit_price_cents' => $item->unit_price_cents,
                        'total_cents' => $item->line_subtotal_cents,
                        'image' => $imageUrl,
                    ];
                }),
                'subtotal_cents' => $orderRecord->items_total_cents,
                'shipping_cost_cents' => $orderRecord->shipping_cost_cents,
                'tax_cents' => $orderRecord->tax_cents ?? 0,
                'total_cents' => $orderRecord->total_cents,
                'shipping_method' => $orderRecord->shipping_method ? [
                    'name' => $orderRecord->shipping_method,
                    'description' => null,
                ] : null,
                'created_at' => $orderRecord->created_at->format('d M Y, g:i A'),
                'estimated_delivery' => null, // Can be calculated based on shipping method if needed
            ],
        ]);
    }
}
