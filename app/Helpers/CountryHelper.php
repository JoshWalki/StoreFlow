<?php

namespace App\Helpers;

class CountryHelper
{
    /**
     * Map of country names to ISO 3166-1 alpha-2 codes
     */
    private static array $countryMap = [
        // Full name => ISO Code
        'Australia' => 'AU',
        'New Zealand' => 'NZ',
        'United States' => 'US',
        'United States of America' => 'US',
        'USA' => 'US',
        'United Kingdom' => 'GB',
        'UK' => 'GB',
        'Great Britain' => 'GB',
        'Canada' => 'CA',
        'China' => 'CN',
        'Japan' => 'JP',
        'Germany' => 'DE',
        'France' => 'FR',
        'Italy' => 'IT',
        'Spain' => 'ES',
        'India' => 'IN',
        'Brazil' => 'BR',
        'Singapore' => 'SG',
        'Malaysia' => 'MY',
        'Indonesia' => 'ID',
        'Thailand' => 'TH',
        'Philippines' => 'PH',
        'Vietnam' => 'VN',
        'South Korea' => 'KR',
        'Korea' => 'KR',
        'Hong Kong' => 'HK',
        'Taiwan' => 'TW',
        'Mexico' => 'MX',
        'South Africa' => 'ZA',
        'Netherlands' => 'NL',
        'Belgium' => 'BE',
        'Switzerland' => 'CH',
        'Austria' => 'AT',
        'Sweden' => 'SE',
        'Norway' => 'NO',
        'Denmark' => 'DK',
        'Finland' => 'FI',
        'Ireland' => 'IE',
        'Portugal' => 'PT',
        'Poland' => 'PL',
        'Czech Republic' => 'CZ',
        'Greece' => 'GR',
        'Russia' => 'RU',
        'Argentina' => 'AR',
        'Chile' => 'CL',
        'Colombia' => 'CO',
        'Peru' => 'PE',
        'Venezuela' => 'VE',
        'Egypt' => 'EG',
        'Turkey' => 'TR',
        'Israel' => 'IL',
        'United Arab Emirates' => 'AE',
        'UAE' => 'AE',
        'Saudi Arabia' => 'SA',
    ];

    /**
     * Map of ISO codes to full country names
     */
    private static array $codeToNameMap = [
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'CN' => 'China',
        'JP' => 'Japan',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'IN' => 'India',
        'BR' => 'Brazil',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'ID' => 'Indonesia',
        'TH' => 'Thailand',
        'PH' => 'Philippines',
        'VN' => 'Vietnam',
        'KR' => 'South Korea',
        'HK' => 'Hong Kong',
        'TW' => 'Taiwan',
        'MX' => 'Mexico',
        'ZA' => 'South Africa',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'IE' => 'Ireland',
        'PT' => 'Portugal',
        'PL' => 'Poland',
        'CZ' => 'Czech Republic',
        'GR' => 'Greece',
        'RU' => 'Russia',
        'AR' => 'Argentina',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'PE' => 'Peru',
        'VE' => 'Venezuela',
        'EG' => 'Egypt',
        'TR' => 'Turkey',
        'IL' => 'Israel',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
    ];

    /**
     * Convert country name to ISO code
     * Accepts full name or ISO code and returns ISO code
     *
     * @param string $countryInput
     * @return string|null ISO code or null if not found
     */
    public static function getCountryCode(string $countryInput): ?string
    {
        $input = trim($countryInput);

        // If it's already a 2-letter code, check if it's valid and return it
        if (strlen($input) === 2) {
            $upperCode = strtoupper($input);
            if (isset(self::$codeToNameMap[$upperCode])) {
                return $upperCode;
            }
        }

        // Try to find by full name (case-insensitive)
        foreach (self::$countryMap as $name => $code) {
            if (strcasecmp($name, $input) === 0) {
                return $code;
            }
        }

        // Not found
        return null;
    }

    /**
     * Convert ISO code to full country name
     *
     * @param string $code
     * @return string|null Country name or null if not found
     */
    public static function getCountryName(string $code): ?string
    {
        $upperCode = strtoupper(trim($code));
        return self::$codeToNameMap[$upperCode] ?? null;
    }

    /**
     * Normalize country input to ISO code
     * Returns the input as-is if it can't be converted
     *
     * @param string $countryInput
     * @return string ISO code or original input
     */
    public static function normalizeCountryCode(string $countryInput): string
    {
        $code = self::getCountryCode($countryInput);
        return $code ?? $countryInput;
    }

    /**
     * Get all supported countries as name => code pairs
     *
     * @return array
     */
    public static function getAllCountries(): array
    {
        $countries = [];
        foreach (self::$codeToNameMap as $code => $name) {
            $countries[$name] = $code;
        }
        ksort($countries);
        return $countries;
    }

    /**
     * Get list of country names for dropdowns
     *
     * @return array
     */
    public static function getCountryNames(): array
    {
        $names = array_values(self::$codeToNameMap);
        sort($names);
        return $names;
    }
}
