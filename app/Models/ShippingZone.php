<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'merchant_id',
        'store_id',
        'name',
        'description',
        'countries',
        'states',
        'postcodes',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'countries' => 'array',
        'states' => 'array',
        'postcodes' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }

    /**
     * Alias for shippingMethods() for backward compatibility.
     */
    public function methods(): HasMany
    {
        return $this->shippingMethods();
    }

    /**
     * Country code to name mapping.
     */
    protected static $countryMapping = [
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'US' => 'United States',
        'UK' => 'United Kingdom',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'ID' => 'Indonesia',
        'TH' => 'Thailand',
        'PH' => 'Philippines',
        'VN' => 'Vietnam',
        'JP' => 'Japan',
        'CN' => 'China',
        'KR' => 'South Korea',
        'HK' => 'Hong Kong',
        'TW' => 'Taiwan',
        'IN' => 'India',
    ];

    /**
     * Normalize country code or name to full country name.
     */
    protected function normalizeCountry(string $country): string
    {
        // If it's a 2-letter code in our mapping, convert to full name
        $upperCountry = strtoupper($country);
        if (isset(self::$countryMapping[$upperCountry])) {
            return self::$countryMapping[$upperCountry];
        }

        // Otherwise return as-is (already a full name)
        return $country;
    }

    /**
     * Check if this zone matches the given address.
     */
    public function matchesAddress(string $country, ?string $state = null, ?string $postcode = null): bool
    {
        // Normalize the input country to full name
        $normalizedCountry = $this->normalizeCountry($country);

        // Check country match (case-insensitive, with normalization)
        if (!empty($this->countries)) {
            $countryMatches = false;
            foreach ($this->countries as $zoneCountry) {
                // Normalize zone country as well
                $normalizedZoneCountry = $this->normalizeCountry($zoneCountry);

                if (strcasecmp($normalizedCountry, $normalizedZoneCountry) === 0) {
                    $countryMatches = true;
                    break;
                }
            }
            if (!$countryMatches) {
                return false;
            }
        }

        // Check state match if states are defined and state is provided (case-insensitive)
        if (!empty($this->states) && $state !== null) {
            $stateMatches = false;
            foreach ($this->states as $zoneState) {
                if (strcasecmp($state, $zoneState) === 0) {
                    $stateMatches = true;
                    break;
                }
            }
            if (!$stateMatches) {
                return false;
            }
        }

        // Check postcode match if postcodes are defined and postcode is provided
        if (!empty($this->postcodes) && $postcode !== null) {
            $matched = false;
            foreach ($this->postcodes as $pattern) {
                // Support wildcard patterns (e.g., "2000-2999" or "2*")
                if ($this->matchesPostcodePattern($postcode, $pattern)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a postcode matches a pattern.
     */
    protected function matchesPostcodePattern(string $postcode, string $pattern): bool
    {
        // Universal wildcard - matches everything
        if ($pattern === '*') {
            return true;
        }

        // Exact match
        if ($postcode === $pattern) {
            return true;
        }

        // Range pattern (e.g., "2000-2999")
        if (strpos($pattern, '-') !== false) {
            [$min, $max] = explode('-', $pattern, 2);
            return $postcode >= $min && $postcode <= $max;
        }

        // Wildcard pattern (e.g., "2*" matches "2000", "2100", etc.)
        if (strpos($pattern, '*') !== false) {
            // Replace * with .* for regex, but escape other special chars first
            $escapedPattern = preg_quote($pattern, '/');
            $regex = '/^' . str_replace('\\*', '.*', $escapedPattern) . '$/';
            return preg_match($regex, $postcode) === 1;
        }

        return false;
    }

    /**
     * Get metadata to include in audit logs.
     *
     * @return array
     */
    protected function getAuditMetadata(): array
    {
        return [
            'name' => $this->name,
            'store_id' => $this->store_id,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
        ];
    }

    /**
     * Get the fields that should be audited.
     *
     * @return array
     */
    protected function getAuditableFields(): array
    {
        return [
            'name',
            'description',
            'countries',
            'states',
            'postcodes',
            'is_active',
            'priority',
        ];
    }
}
