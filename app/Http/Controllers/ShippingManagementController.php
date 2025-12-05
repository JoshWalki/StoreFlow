<?php

namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShippingManagementController extends Controller
{
    /**
     * Display the shipping management interface.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $zones = ShippingZone::where('store_id', $storeId)
            ->with('shippingMethods')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();

        $methods = ShippingMethod::whereHas('shippingZone', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })
            ->with(['shippingZone', 'shippingRates'])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Shipping/Index', [
            'store' => $store,
            'user' => $user,
            'zones' => $zones,
            'methods' => $methods,
        ]);
    }

    /**
     * Store a new shipping zone.
     */
    public function storeZone(Request $request)
    {
        $this->authorize('create', ShippingZone::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'states' => 'nullable|array',
            'states.*' => 'string|max:64',
            'postcodes' => 'nullable|array',
            'postcodes.*' => 'string|max:20',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        $validated['store_id'] = session('store_id');
        $validated['merchant_id'] = auth()->user()->merchant_id;

        // Set defaults
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }
        if (!isset($validated['priority'])) {
            $validated['priority'] = 0;
        }

        $zone = ShippingZone::create($validated);

        return redirect()->back()->with('success', 'Shipping zone created successfully.');
    }

    /**
     * Update an existing shipping zone.
     */
    public function updateZone(Request $request, ShippingZone $zone)
    {
        $this->authorize('update', $zone);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'states' => 'nullable|array',
            'states.*' => 'string|max:64',
            'postcodes' => 'nullable|array',
            'postcodes.*' => 'string|max:20',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        $zone->update($validated);

        return redirect()->back()->with('success', 'Shipping zone updated successfully.');
    }

    /**
     * Delete a shipping zone.
     */
    public function destroyZone(ShippingZone $zone)
    {
        $this->authorize('delete', $zone);

        // Check if zone has active methods
        if ($zone->methods()->where('is_active', true)->exists()) {
            return redirect()->back()->withErrors([
                'zone' => 'Cannot delete zone with active shipping methods. Please deactivate or delete the methods first.',
            ]);
        }

        $zone->delete();

        return redirect()->back()->with('success', 'Shipping zone deleted successfully.');
    }

    /**
     * Store a new shipping method.
     */
    public function storeMethod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'zone_id' => 'required|exists:shipping_zones,id',
            'carrier' => 'nullable|string|max:255',
            'service_code' => 'nullable|string|max:255',
            'min_delivery_days' => 'nullable|integer|min:0',
            'max_delivery_days' => 'nullable|integer|min:0|gte:min_delivery_days',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        // Verify zone belongs to the current store
        $zone = ShippingZone::where('id', $validated['zone_id'])
            ->where('store_id', session('store_id'))
            ->firstOrFail();

        $this->authorize('create', [ShippingMethod::class, $zone]);

        // Add required fields
        $validated['merchant_id'] = $request->user()->merchant_id;
        $validated['store_id'] = session('store_id');
        $validated['shipping_zone_id'] = $validated['zone_id'];
        unset($validated['zone_id']);

        $method = ShippingMethod::create($validated);

        return redirect()->back()->with('success', 'Shipping method created successfully.');
    }

    /**
     * Update an existing shipping method.
     */
    public function updateMethod(Request $request, ShippingMethod $method)
    {
        $this->authorize('update', $method);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'zone_id' => 'required|exists:shipping_zones,id',
            'carrier' => 'nullable|string|max:255',
            'service_code' => 'nullable|string|max:255',
            'min_delivery_days' => 'nullable|integer|min:0',
            'max_delivery_days' => 'nullable|integer|min:0|gte:min_delivery_days',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        // Verify zone belongs to the current store
        $zone = ShippingZone::where('id', $validated['zone_id'])
            ->where('store_id', session('store_id'))
            ->firstOrFail();

        // Map zone_id to shipping_zone_id
        $validated['shipping_zone_id'] = $validated['zone_id'];
        unset($validated['zone_id']);

        $method->update($validated);

        return redirect()->back()->with('success', 'Shipping method updated successfully.');
    }

    /**
     * Delete a shipping method.
     */
    public function destroyMethod(ShippingMethod $method)
    {
        $this->authorize('delete', $method);

        $method->delete();

        return redirect()->back()->with('success', 'Shipping method deleted successfully.');
    }

    /**
     * Calculate shipping rates for testing purposes.
     */
    public function calculateRates(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|array',
            'address.address_line1' => 'required|string',
            'address.city' => 'required|string',
            'address.province' => 'required|string',
            'address.postal_code' => 'required|string',
            'address.country' => 'required|string|max:100',
            'order_details' => 'required|array',
            'order_details.subtotal' => 'required|numeric|min:0',
            'order_details.weight_kg' => 'required|numeric|min:0',
            'order_details.items_count' => 'required|integer|min:1',
        ]);

        $storeId = session('store_id');
        $address = $validated['address'];
        $orderDetails = $validated['order_details'];

        // Normalize country to ISO code for zone matching
        $normalizedCountry = CountryHelper::normalizeCountryCode($address['country']);

        // Build cart items from order details for the ShippingEngine
        // Note: quantity is set to 1 because weight_grams already represents total weight
        $cartItems = [[
            'product_id' => 0,
            'quantity' => 1,
            'price_cents' => (int)($orderDetails['subtotal'] * 100),
            'weight_grams' => (int)($orderDetails['weight_kg'] * 1000),
        ]];

        // Use the ShippingEngine to calculate rates
        $shippingEngine = app(\App\Services\Shipping\ShippingEngine::class);

        // Debug information
        $debug = [
            'merchant_id' => auth()->user()->merchant_id,
            'store_id' => $storeId,
            'country' => $address['country'],
            'country_normalized' => $normalizedCountry,
            'province' => $address['province'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'cart_total_cents' => (int)($orderDetails['subtotal'] * 100),
            'weight_grams' => (int)($orderDetails['weight_kg'] * 1000),
            'item_count' => $orderDetails['items_count'],
        ];

        // Get all zones for debugging
        $allZones = \App\Models\ShippingZone::where('store_id', $storeId)
            ->with(['shippingMethods.shippingRates'])
            ->get();

        // Build comprehensive diagnostic feedback
        $cartTotalCents = (int)($orderDetails['subtotal'] * 100);
        $weightGrams = (int)($orderDetails['weight_kg'] * 1000);
        $itemCount = $orderDetails['items_count'];

        $zoneFeedback = $allZones->map(function ($zone) use ($normalizedCountry, $address, $cartTotalCents, $weightGrams, $itemCount) {
            $matchResult = $zone->matchesAddress(
                $normalizedCountry,
                $address['province'] ?? null,
                $address['postal_code'] ?? null
            );

            // Determine why zone matched or didn't match
            $zoneReason = $this->getZoneMatchReason(
                $zone,
                $normalizedCountry,
                $address['province'] ?? null,
                $address['postal_code'] ?? null,
                $matchResult
            );

            // Analyze methods for this zone
            $methodsAnalysis = $zone->shippingMethods->map(function ($method) use ($cartTotalCents, $weightGrams, $itemCount) {
                if (!$method->is_active) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'available' => false,
                        'reason' => 'Method is not active',
                        'suggestion' => 'Activate this shipping method in your settings',
                    ];
                }

                if ($method->shippingRates->isEmpty()) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'available' => false,
                        'reason' => 'No shipping rates configured for this method',
                        'suggestion' => 'Add at least one shipping rate to this method',
                    ];
                }

                // Check which rates apply
                $rateAnalysis = $method->shippingRates->map(function ($rate) use ($cartTotalCents, $weightGrams, $itemCount) {
                    if (!$rate->is_active) {
                        return [
                            'id' => $rate->id,
                            'name' => $rate->name,
                            'applies' => false,
                            'reason' => 'Rate is not active',
                            'suggestion' => 'Activate this rate',
                        ];
                    }

                    $applies = $rate->appliesTo($weightGrams, $cartTotalCents, $itemCount);
                    $rateReason = $this->getRateApplicabilityReason($rate, $weightGrams, $cartTotalCents, $itemCount, $applies);

                    $cost = null;
                    if ($applies) {
                        $cost = $rate->calculateCost($weightGrams, $cartTotalCents, $itemCount);
                    }

                    return [
                        'id' => $rate->id,
                        'name' => $rate->name,
                        'applies' => $applies,
                        'cost_cents' => $cost,
                        'reason' => $rateReason['reason'],
                        'suggestion' => $rateReason['suggestion'] ?? null,
                    ];
                });

                $applicableRates = $rateAnalysis->where('applies', true);

                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'available' => $applicableRates->isNotEmpty(),
                    'reason' => $applicableRates->isNotEmpty() ? 'Available' : 'No applicable rates found',
                    'rates_analysis' => $rateAnalysis->values(),
                ];
            });

            $availableMethods = $methodsAnalysis->where('available', true);

            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'is_active' => $zone->is_active,
                'matched' => $matchResult,
                'reason' => $zoneReason['reason'],
                'suggestion' => $zoneReason['suggestion'] ?? null,
                'methods_total' => $zone->shippingMethods->count(),
                'methods_available' => $availableMethods->count(),
                'methods_analysis' => $methodsAnalysis->values(),
            ];
        });

        $matchedZones = $zoneFeedback->where('matched', true);
        $zonesWithMethods = $zoneFeedback->where('methods_available', '>', 0);

        try {
            $options = $shippingEngine->calculateShippingOptions(
                auth()->user()->merchant_id,
                $storeId,
                $cartItems,
                $normalizedCountry,
                $address['province'] ?? null,
                $address['postal_code'] ?? null
            );

            // Format the response for the frontend
            $rates = $options->map(function ($option) {
                return [
                    'method_id' => $option['shipping_method_id'],
                    'method_name' => $option['shipping_method_name'],
                    'zone_name' => $option['shipping_zone_name'],
                    'rate' => $option['cost_cents'] / 100,
                    'estimated_days_min' => $option['delivery_estimate']['min_days'] ?? 1,
                    'estimated_days_max' => $option['delivery_estimate']['max_days'] ?? 3,
                    'carrier' => $option['carrier'] ?? null,
                    'service_code' => $option['service_code'] ?? null,
                ];
            });

            // Build comprehensive diagnostic message
            $diagnosticMessage = $this->buildDiagnosticMessage(
                $allZones->count(),
                $matchedZones->count(),
                $zonesWithMethods->count(),
                $rates->count(),
                $weightGrams,
                $cartTotalCents
            );

            $feedback = [
                'summary' => [
                    'total_zones' => $allZones->count(),
                    'matched_zones' => $matchedZones->count(),
                    'zones_with_methods' => $zonesWithMethods->count(),
                    'available_options' => $rates->count(),
                    'cart_total_cents' => $cartTotalCents,
                    'weight_grams' => $weightGrams,
                    'item_count' => $itemCount,
                    'message' => $diagnosticMessage,
                ],
                'zones' => $zoneFeedback->values(),
            ];

            return response()->json([
                'success' => true,
                'rates' => $rates,
                'feedback' => $feedback,
                'debug' => $debug,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'rates' => [],
                'debug' => $debug,
            ], 422);
        }
    }

    /**
     * Store a new shipping rate.
     */
    public function storeRate(Request $request)
    {
        $validated = $request->validate([
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'name' => 'required|string|max:255',
            'pricing_model' => 'required|in:flat,weight_based,cart_total_based,item_count',
            'flat_rate_cents' => 'nullable|integer|min:0',
            'min_weight_grams' => 'nullable|integer|min:0',
            'max_weight_grams' => 'nullable|integer|min:0|gte:min_weight_grams',
            'weight_rate_cents' => 'nullable|integer|min:0',
            'base_weight_rate_cents' => 'nullable|integer|min:0',
            'min_cart_total_cents' => 'nullable|integer|min:0',
            'max_cart_total_cents' => 'nullable|integer|min:0|gte:min_cart_total_cents',
            'cart_total_rate_cents' => 'nullable|integer|min:0',
            'min_items' => 'nullable|integer|min:0',
            'max_items' => 'nullable|integer|min:0|gte:min_items',
            'item_rate_cents' => 'nullable|integer|min:0',
            'free_shipping_threshold_cents' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Verify method belongs to the current store
        $method = \App\Models\ShippingMethod::where('id', $validated['shipping_method_id'])
            ->where('store_id', session('store_id'))
            ->firstOrFail();

        // Add required fields
        $validated['merchant_id'] = $request->user()->merchant_id;
        $validated['store_id'] = session('store_id');

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $rate = \App\Models\ShippingRate::create($validated);

        return redirect()->back()->with('success', 'Shipping rate created successfully.');
    }

    /**
     * Update an existing shipping rate.
     */
    public function updateRate(Request $request, \App\Models\ShippingRate $rate)
    {
        // Verify rate belongs to the current store
        if ($rate->store_id !== session('store_id')) {
            abort(403, 'Unauthorized access to shipping rate.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pricing_model' => 'required|in:flat,weight_based,cart_total_based,item_count',
            'flat_rate_cents' => 'nullable|integer|min:0',
            'min_weight_grams' => 'nullable|integer|min:0',
            'max_weight_grams' => 'nullable|integer|min:0|gte:min_weight_grams',
            'weight_rate_cents' => 'nullable|integer|min:0',
            'base_weight_rate_cents' => 'nullable|integer|min:0',
            'min_cart_total_cents' => 'nullable|integer|min:0',
            'max_cart_total_cents' => 'nullable|integer|min:0|gte:min_cart_total_cents',
            'cart_total_rate_cents' => 'nullable|integer|min:0',
            'min_items' => 'nullable|integer|min:0',
            'max_items' => 'nullable|integer|min:0|gte:min_items',
            'item_rate_cents' => 'nullable|integer|min:0',
            'free_shipping_threshold_cents' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rate->update($validated);

        return redirect()->back()->with('success', 'Shipping rate updated successfully.');
    }

    /**
     * Delete a shipping rate.
     */
    public function destroyRate(\App\Models\ShippingRate $rate)
    {
        // Verify rate belongs to the current store
        if ($rate->store_id !== session('store_id')) {
            abort(403, 'Unauthorized access to shipping rate.');
        }

        $rate->delete();

        return redirect()->back()->with('success', 'Shipping rate deleted successfully.');
    }

    /**
     * Build a comprehensive diagnostic message for shipping calculation.
     */
    protected function buildDiagnosticMessage(
        int $totalZones,
        int $matchedZones,
        int $zonesWithMethods,
        int $availableOptions,
        int $weightGrams,
        int $cartTotalCents
    ): string {
        $weightKg = number_format($weightGrams / 1000, 2);
        $cartTotal = number_format($cartTotalCents / 100, 2);

        if ($availableOptions > 0) {
            return "Found {$availableOptions} shipping option(s) for {$weightKg}kg / AUD\${$cartTotal}";
        }

        if ($totalZones === 0) {
            return "No shipping zones configured. Please create at least one shipping zone.";
        }

        if ($matchedZones === 0) {
            return "No zones match the delivery address. Please check your zone country/state/postcode settings.";
        }

        if ($zonesWithMethods === 0) {
            return "Zone(s) matched but no shipping methods have applicable rates. Check the detailed analysis below for specific reasons (weight limits, price thresholds, etc.).";
        }

        // Zones have methods but no options returned
        return "No shipping options available. The order weight ({$weightKg}kg) or cart total (AUD\${$cartTotal}) may exceed configured rate limits. Check the rate analysis below for details.";
    }

    /**
     * Get a human-readable reason for zone match/mismatch.
     */
    protected function getZoneMatchReason(
        \App\Models\ShippingZone $zone,
        string $country,
        ?string $state,
        ?string $postcode,
        bool $matched
    ): array {
        if (!$zone->is_active) {
            return [
                'reason' => 'Zone is not active',
                'suggestion' => 'Activate this shipping zone to make it available for customers',
            ];
        }

        // Check country
        if (!empty($zone->countries)) {
            $countryMatches = false;
            foreach ($zone->countries as $zoneCountry) {
                if (strcasecmp($country, $zoneCountry) === 0) {
                    $countryMatches = true;
                    break;
                }
            }
            if (!$countryMatches) {
                return [
                    'reason' => "Country '{$country}' is not in this zone's country list: " . implode(', ', $zone->countries),
                    'suggestion' => "Add '{$country}' to this zone's countries if you want to ship to this location",
                ];
            }
        }

        // Check state
        if (!empty($zone->states) && $state !== null) {
            $stateMatches = false;
            foreach ($zone->states as $zoneState) {
                if (strcasecmp($state, $zoneState) === 0) {
                    $stateMatches = true;
                    break;
                }
            }
            if (!$stateMatches) {
                return [
                    'reason' => "State '{$state}' is not in this zone's state list: " . implode(', ', $zone->states),
                    'suggestion' => "Add '{$state}' to this zone's states, or remove state restrictions to ship to all states in {$country}",
                ];
            }
        }

        // Check postcode
        if (!empty($zone->postcodes) && $postcode !== null) {
            $postcodeMatches = false;
            foreach ($zone->postcodes as $pattern) {
                if ($this->matchesPostcodePattern($postcode, $pattern)) {
                    $postcodeMatches = true;
                    break;
                }
            }
            if (!$postcodeMatches) {
                return [
                    'reason' => "Postcode '{$postcode}' does not match any patterns in this zone: " . implode(', ', $zone->postcodes),
                    'suggestion' => "Update your postcode patterns to include '{$postcode}', or use '*' to match all postcodes",
                ];
            }
        }

        if ($matched) {
            return [
                'reason' => "Zone matches: Country '{$country}'" .
                    ($state ? ", State '{$state}'" : '') .
                    ($postcode ? ", Postcode '{$postcode}'" : ''),
            ];
        }

        return ['reason' => 'Zone does not match the delivery address'];
    }

    /**
     * Check if a postcode matches a pattern (duplicate of ShippingZone method).
     */
    protected function matchesPostcodePattern(string $postcode, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }

        if ($postcode === $pattern) {
            return true;
        }

        if (strpos($pattern, '-') !== false) {
            [$min, $max] = explode('-', $pattern, 2);
            return $postcode >= $min && $postcode <= $max;
        }

        if (strpos($pattern, '*') !== false) {
            $escapedPattern = preg_quote($pattern, '/');
            $regex = '/^' . str_replace('\\*', '.*', $escapedPattern) . '$/';
            return preg_match($regex, $postcode) === 1;
        }

        return false;
    }

    /**
     * Get a human-readable reason for rate applicability.
     */
    protected function getRateApplicabilityReason(
        \App\Models\ShippingRate $rate,
        int $weightGrams,
        int $cartTotalCents,
        int $itemCount,
        bool $applies
    ): array {
        if (!$rate->is_active) {
            return [
                'reason' => 'Rate is not active',
                'suggestion' => 'Activate this rate in your shipping settings',
            ];
        }

        $reasons = [];
        $suggestions = [];

        // Check based on pricing model
        switch ($rate->pricing_model) {
            case 'weight_based':
                if ($rate->min_weight_grams !== null && $weightGrams < $rate->min_weight_grams) {
                    $reasons[] = "Weight {$weightGrams}g is below minimum {$rate->min_weight_grams}g";
                    $suggestions[] = "Lower the minimum weight to {$weightGrams}g or less";
                }
                if ($rate->max_weight_grams !== null && $weightGrams > $rate->max_weight_grams) {
                    $reasons[] = "Weight {$weightGrams}g exceeds maximum {$rate->max_weight_grams}g";
                    $suggestions[] = "Increase the maximum weight to {$weightGrams}g or more, or create a new rate for heavier items";
                }
                if ($applies && empty($reasons)) {
                    $reasons[] = "Weight {$weightGrams}g is within range " .
                        ($rate->min_weight_grams ?? 0) . "g - " .
                        ($rate->max_weight_grams ?? '∞') . "g";
                }
                break;

            case 'cart_total_based':
                if ($rate->min_cart_total_cents !== null && $cartTotalCents < $rate->min_cart_total_cents) {
                    $minDollars = number_format($rate->min_cart_total_cents / 100, 2);
                    $currentDollars = number_format($cartTotalCents / 100, 2);
                    $reasons[] = "Cart total \${$currentDollars} is below minimum \${$minDollars}";
                    $suggestions[] = "Lower the minimum cart total to \${$currentDollars} or less";
                }
                if ($rate->max_cart_total_cents !== null && $cartTotalCents > $rate->max_cart_total_cents) {
                    $maxDollars = number_format($rate->max_cart_total_cents / 100, 2);
                    $currentDollars = number_format($cartTotalCents / 100, 2);
                    $reasons[] = "Cart total \${$currentDollars} exceeds maximum \${$maxDollars}";
                    $suggestions[] = "Increase the maximum cart total to \${$currentDollars} or more";
                }
                if ($applies && empty($reasons)) {
                    $currentDollars = number_format($cartTotalCents / 100, 2);
                    $reasons[] = "Cart total \${$currentDollars} is within range";
                }
                break;

            case 'item_count':
                if ($rate->min_items !== null && $itemCount < $rate->min_items) {
                    $reasons[] = "{$itemCount} items is below minimum {$rate->min_items}";
                    $suggestions[] = "Lower the minimum item count to {$itemCount} or less";
                }
                if ($rate->max_items !== null && $itemCount > $rate->max_items) {
                    $reasons[] = "{$itemCount} items exceeds maximum {$rate->max_items}";
                    $suggestions[] = "Increase the maximum item count to {$itemCount} or more";
                }
                if ($applies && empty($reasons)) {
                    $reasons[] = "{$itemCount} items is within range";
                }
                break;

            case 'flat':
                $reasons[] = "Flat rate applies to all orders";
                break;
        }

        // Check free shipping threshold
        if ($rate->free_shipping_threshold_cents !== null) {
            $thresholdDollars = number_format($rate->free_shipping_threshold_cents / 100, 2);
            $currentDollars = number_format($cartTotalCents / 100, 2);
            if ($cartTotalCents >= $rate->free_shipping_threshold_cents) {
                $reasons[] = "Free shipping (cart total \${$currentDollars} ≥ \${$thresholdDollars})";
            } else {
                $remaining = number_format(($rate->free_shipping_threshold_cents - $cartTotalCents) / 100, 2);
                $reasons[] = "Add \${$remaining} more to qualify for free shipping";
            }
        }

        return [
            'reason' => implode('; ', $reasons) ?: 'Rate does not apply',
            'suggestion' => !empty($suggestions) ? implode('; ', $suggestions) : null,
        ];
    }
}
