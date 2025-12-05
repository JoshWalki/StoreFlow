<?php

namespace App\Services\Shipping;

use App\Helpers\CountryHelper;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Support\Collection;

class ShippingEngine
{
    /**
     * Calculate available shipping options for a cart.
     *
     * @param int $merchantId
     * @param int $storeId
     * @param array $cartItems Array of items with 'product_id', 'quantity', 'price_cents', 'weight_grams'
     * @param string $country Destination country (full name or ISO code)
     * @param string|null $state Destination state code
     * @param string|null $postcode Destination postcode
     * @return Collection Collection of shipping options
     */
    public function calculateShippingOptions(
        int $merchantId,
        int $storeId,
        array $cartItems,
        string $country,
        ?string $state = null,
        ?string $postcode = null
    ): Collection {
        // Normalize country to ISO code for zone matching
        try {
            $countryCode = CountryHelper::normalizeCountryCode($country);
            \Log::info('ShippingEngine: Country normalization successful', [
                'original' => $country,
                'normalized' => $countryCode,
            ]);
        } catch (\Exception $e) {
            \Log::error('ShippingEngine: CountryHelper error', [
                'error' => $e->getMessage(),
                'country' => $country,
            ]);
            // Fallback: use country as-is
            $countryCode = $country;
        }

        // Log for debugging
        \Log::info('ShippingEngine: Calculating options', [
            'original_country' => $country,
            'normalized_country' => $countryCode,
            'state' => $state,
            'postcode' => $postcode,
            'merchant_id' => $merchantId,
            'store_id' => $storeId,
        ]);

        // Calculate cart totals
        $cartTotalCents = $this->calculateCartTotal($cartItems);
        $totalWeightGrams = $this->calculateTotalWeight($cartItems);
        $itemCount = $this->calculateItemCount($cartItems);

        // Find matching zones (using normalized country code)
        $matchingZones = $this->findMatchingZones($merchantId, $storeId, $countryCode, $state, $postcode);

        \Log::info('ShippingEngine: Zones found', [
            'count' => $matchingZones->count(),
            'zone_ids' => $matchingZones->pluck('id')->toArray(),
        ]);

        if ($matchingZones->isEmpty()) {
            return collect();
        }

        // Get available shipping methods for matching zones
        $shippingOptions = collect();

        foreach ($matchingZones as $zone) {
            $methods = $this->getAvailableMethodsForZone($zone, $totalWeightGrams, $cartTotalCents, $itemCount);
            $shippingOptions = $shippingOptions->merge($methods);
        }

        // Sort by cost (lowest first) and then by display order
        return $shippingOptions->sortBy([
            ['cost_cents', 'asc'],
            ['display_order', 'asc'],
        ])->values();
    }

    /**
     * Get a specific shipping quote.
     *
     * @param int $shippingMethodId
     * @param array $cartItems
     * @return array|null
     */
    public function getShippingQuote(int $shippingMethodId, array $cartItems): ?array
    {
        $shippingMethod = ShippingMethod::with(['shippingRates', 'shippingZone'])
            ->where('is_active', true)
            ->find($shippingMethodId);

        if (!$shippingMethod) {
            return null;
        }

        // Calculate cart totals
        $cartTotalCents = $this->calculateCartTotal($cartItems);
        $totalWeightGrams = $this->calculateTotalWeight($cartItems);
        $itemCount = $this->calculateItemCount($cartItems);

        // Find applicable rate
        $rate = $this->findApplicableRate($shippingMethod, $totalWeightGrams, $cartTotalCents, $itemCount);

        if (!$rate) {
            return null;
        }

        $cost = $rate->calculateCost($totalWeightGrams, $cartTotalCents, $itemCount);

        return [
            'shipping_method_id' => $shippingMethod->id,
            'shipping_method_name' => $shippingMethod->name,
            'shipping_zone_name' => $shippingMethod->shippingZone->name,
            'carrier' => $shippingMethod->carrier,
            'service_code' => $shippingMethod->service_code,
            'cost_cents' => $cost,
            'delivery_estimate' => $shippingMethod->getDeliveryEstimate(),
            'description' => $shippingMethod->description,
        ];
    }

    /**
     * Find zones that match the given address.
     */
    protected function findMatchingZones(
        int $merchantId,
        int $storeId,
        string $country,
        ?string $state = null,
        ?string $postcode = null
    ): Collection {
        $allZones = ShippingZone::where('merchant_id', $merchantId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        \Log::info('ShippingEngine: All active zones', [
            'total_zones' => $allZones->count(),
            'zones' => $allZones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'countries' => $zone->countries,
                    'states' => $zone->states,
                    'postcodes' => $zone->postcodes,
                ];
            })->toArray(),
        ]);

        $matchingZones = $allZones->filter(function ($zone) use ($country, $state, $postcode) {
            $matches = $zone->matchesAddress($country, $state, $postcode);

            \Log::info('ShippingEngine: Zone match check', [
                'zone_id' => $zone->id,
                'zone_name' => $zone->name,
                'zone_countries' => $zone->countries,
                'checking_country' => $country,
                'matches' => $matches,
            ]);

            return $matches;
        });

        return $matchingZones;
    }

    /**
     * Get available shipping methods for a zone.
     */
    protected function getAvailableMethodsForZone(
        ShippingZone $zone,
        int $totalWeightGrams,
        int $cartTotalCents,
        int $itemCount
    ): Collection {
        $methods = ShippingMethod::with('shippingRates')
            ->where('shipping_zone_id', $zone->id)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        \Log::info('ShippingEngine: Methods for zone', [
            'zone_id' => $zone->id,
            'zone_name' => $zone->name,
            'total_methods' => $methods->count(),
            'methods' => $methods->map(function($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'is_active' => $m->is_active,
                    'rates_count' => $m->shippingRates->count(),
                ];
            })->toArray(),
        ]);

        $options = collect();

        foreach ($methods as $method) {
            $rate = $this->findApplicableRate($method, $totalWeightGrams, $cartTotalCents, $itemCount);

            \Log::info('ShippingEngine: Rate search for method', [
                'method_id' => $method->id,
                'method_name' => $method->name,
                'total_weight_grams' => $totalWeightGrams,
                'cart_total_cents' => $cartTotalCents,
                'item_count' => $itemCount,
                'rate_found' => $rate !== null,
                'rate_id' => $rate ? $rate->id : null,
            ]);

            if ($rate) {
                $cost = $rate->calculateCost($totalWeightGrams, $cartTotalCents, $itemCount);

                \Log::info('ShippingEngine: Rate cost calculated', [
                    'rate_id' => $rate->id,
                    'rate_name' => $rate->name,
                    'cost_cents' => $cost,
                ]);

                if ($cost !== null) {
                    $options->push([
                        'shipping_method_id' => $method->id,
                        'shipping_method_name' => $method->name,
                        'shipping_zone_id' => $zone->id,
                        'shipping_zone_name' => $zone->name,
                        'carrier' => $method->carrier,
                        'service_code' => $method->service_code,
                        'cost_cents' => $cost,
                        'delivery_estimate' => $method->getDeliveryEstimate(),
                        'description' => $method->description,
                        'display_order' => $method->display_order,
                    ]);
                }
            }
        }

        return $options;
    }

    /**
     * Find the applicable rate for a shipping method.
     */
    protected function findApplicableRate(
        ShippingMethod $method,
        int $totalWeightGrams,
        int $cartTotalCents,
        int $itemCount
    ): ?ShippingRate {
        // Get all active rates for this method
        $rates = $method->shippingRates()
            ->where('is_active', true)
            ->get();

        \Log::info('ShippingEngine: Finding applicable rate', [
            'method_id' => $method->id,
            'total_rates' => $rates->count(),
            'rates' => $rates->map(function($r) use ($totalWeightGrams, $cartTotalCents, $itemCount) {
                $applies = $r->appliesTo($totalWeightGrams, $cartTotalCents, $itemCount);
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'is_active' => $r->is_active,
                    'pricing_model' => $r->pricing_model,
                    'min_weight' => $r->min_weight_grams,
                    'max_weight' => $r->max_weight_grams,
                    'min_price' => $r->min_order_total_cents,
                    'max_price' => $r->max_order_total_cents,
                    'applies' => $applies,
                ];
            })->toArray(),
        ]);

        // Find the first rate that applies
        foreach ($rates as $rate) {
            if ($rate->appliesTo($totalWeightGrams, $cartTotalCents, $itemCount)) {
                return $rate;
            }
        }

        return null;
    }

    /**
     * Calculate the total cart value in cents.
     */
    protected function calculateCartTotal(array $cartItems): int
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item['price_cents'] ?? 0) * ($item['quantity'] ?? 1);
        }
        return $total;
    }

    /**
     * Calculate the total weight in grams.
     */
    protected function calculateTotalWeight(array $cartItems): int
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $weight = $item['weight_grams'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $total += $weight * $quantity;
        }
        return $total;
    }

    /**
     * Calculate the total number of items.
     */
    protected function calculateItemCount(array $cartItems): int
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['quantity'] ?? 1;
        }
        return $total;
    }

    /**
     * Validate if shipping is available for the given address.
     */
    public function isShippingAvailable(
        int $merchantId,
        int $storeId,
        string $country,
        ?string $state = null,
        ?string $postcode = null
    ): bool {
        $matchingZones = $this->findMatchingZones($merchantId, $storeId, $country, $state, $postcode);
        return $matchingZones->isNotEmpty();
    }

    /**
     * Get all available shipping zones for a store.
     */
    public function getAvailableZones(int $merchantId, int $storeId): Collection
    {
        return ShippingZone::with('shippingMethods')
            ->where('merchant_id', $merchantId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Calculate shipping for a specific rate and cart.
     */
    public function calculateShippingForRate(int $rateId, array $cartItems): ?int
    {
        $rate = ShippingRate::where('is_active', true)->find($rateId);

        if (!$rate) {
            return null;
        }

        $cartTotalCents = $this->calculateCartTotal($cartItems);
        $totalWeightGrams = $this->calculateTotalWeight($cartItems);
        $itemCount = $this->calculateItemCount($cartItems);

        if (!$rate->appliesTo($totalWeightGrams, $cartTotalCents, $itemCount)) {
            return null;
        }

        return $rate->calculateCost($totalWeightGrams, $cartTotalCents, $itemCount);
    }
}
