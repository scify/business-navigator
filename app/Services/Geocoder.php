<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\GeocodeResult;
use App\Enums\GeocodingCacheSource;
use App\Enums\GeocodingCacheType;
use App\Enums\OrganisationLocationSource;
use App\Models\GeocodingCache;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use OpenCage\Geocoder\Geocoder as OpenCageGeocoder;
use RuntimeException;

class Geocoder
{
    /**
     * Geocoding location data source.
     */
    private const GEOCODING_SOURCE = OrganisationLocationSource::OPENCAGE;

    /**
     * Cache duration in days.
     */
    private const CACHE_DURATION_DAYS = 90;

    /**
     * A set of reliably geocodable test addresses.
     *
     * @var array<string,string>
     */
    private const TEST_ADDRESSES = [
        // France – Eiffel Tower
        'FR' => '5 Avenue Anatole France, 75007 Paris',
        // Germany – Brandenburg Gate (Pariser Platz)
        'DE' => 'Pariser Platz, 10117 Berlin',
        // Italy – Colosseum
        'IT' => 'Piazza del Colosseo, 1, 00184 Roma RM',
        // Netherlands – Dam Square
        'NL' => 'Dam, 1012 NP Amsterdam',
        // Spain – Puerta del Sol
        'ES' => 'Puerta del Sol, 28013 Madrid',
    ];

    protected OpenCageGeocoder $openCageGeocoder;

    public function __construct()
    {
        $apiKey = config('services.opencage.api_key');
        if (empty($apiKey)) {
            throw new InvalidArgumentException('OpenCage API key is not configured');
        }

        $this->openCageGeocoder = new OpenCageGeocoder($apiKey);
    }

    /**
     * Get coordinates for an address ("forward" geocoding).
     *
     * @param  string  $address  The address to geocode.
     * @param  string|null  $alpha2  ISO alpha-2 country code (e.g. 'DE', 'FR') to bias results.
     * @param  int  $limit  Maximum number of results to return (default: 10, matching OpenCage default).
     * @param  bool  $useCache  Whether to use cached results (default: true).
     *
     * @return GeocodeResult Formatted geocoding result with lat, lng, formatted_address, etc.
     */
    public function getCoordinates(string $address, ?string $alpha2 = null, int $limit = 10, bool $useCache = true): GeocodeResult
    {
        // Normalize address for better geocoding results
        $normalizedAddress = $this->normaliseAddressForCountry($address, $alpha2);
        $optParams = [
            'language' => config('services.opencage.language', 'en'),
            'pretty' => config('services.opencage.pretty', 0),
            'no_record' => config('services.opencage.no_record', 1),
            'limit' => $limit,
        ];

        if ($alpha2 !== null) {
            $optParams['countrycode'] = strtolower($alpha2);
        }

        $cacheKey = $this->buildCacheKey($normalizedAddress, $optParams);

        if ($useCache) {
            $cached = $this->retrieveFromCache($cacheKey);
            if ($cached) {
                return $this->formatOpenCageResponse($address, $cached);
            }
        }

        try {
            $response = $this->openCageGeocoder->geocode($normalizedAddress, $optParams);
            if (! is_array($response)) {
                throw new RuntimeException('OpenCage API returned non-array response');
            }
            /** @var array<string, mixed> $response */
            $this->storeInCache($cacheKey, $response);

            return $this->formatOpenCageResponse($address, $response);

        } catch (Exception $e) {
            Log::error('OpenCage geocoding failed for address', [
                'address' => $address,
                'alpha2' => $alpha2,
                'error' => $e->getMessage(),
            ]);

            // Return empty result on API failure
            return $this->formatOpenCageResponse($address, [
                'results' => [],
                'total_results' => 0,
            ]);
        }
    }

    /**
     * Get an address for coordinates with database caching (reverse geocoding).
     *
     * @param  float  $lat  Latitude coordinate.
     * @param  float  $lng  Longitude coordinate.
     * @param  bool  $useCache  Whether to use cached results (default: true).
     *
     * @return GeocodeResult Formatted geocoding result with address information.
     */
    public function getAddress(float $lat, float $lng, bool $useCache = true): GeocodeResult
    {
        $optParams = [
            'language' => config('services.opencage.language', 'en'),
            'pretty' => config('services.opencage.pretty', 0),
            'no_record' => config('services.opencage.no_record', 1),
        ];

        $cacheKey = $this->buildCacheKey("$lat,$lng", $optParams);

        if ($useCache) {
            $cached = $this->retrieveFromCache($cacheKey);
            if ($cached) {
                return $this->formatOpenCageResponse("$lat, $lng", $cached);

            }
        }

        try {
            $response = $this->openCageGeocoder->geocode("$lat,$lng", $optParams);
            if (! is_array($response)) {
                throw new RuntimeException('OpenCage API returned non-array response');
            }
            /** @var array<string, mixed> $response */
            $this->storeInCache($cacheKey, $response, GeocodingCacheType::REVERSE);

            return $this->formatOpenCageResponse("$lat, $lng", $response);
        } catch (Exception $e) {
            Log::error('OpenCage reverse geocoding failed for coordinates', [
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage(),
            ]);

            // Returns empty result on API failure:
            return $this->formatOpenCageResponse("$lat, $lng", [
                'results' => [],
                'total_results' => 0,
            ]);
        }
    }

    /**
     * Get single geocoding coordinates with quality filtering.
     *
     * This is a thin wrapper around getCoordinates() that:
     * - Forces limit=1 (single result only)
     * - Returns null for null addresses (input safety)
     * - Returns null for confidence=0 results (quality filtering)
     * - Useful for imports with a "good result or nothing" (e.g. import)
     *
     * @param  string|null  $address  The address to be geocoded.
     * @param  string|null  $alpha2  ISO alpha-2 country code (e.g. 'DE', 'FR') to bias results.
     *
     * @return GeocodeResult|null Single geocoding result with confidence > 0, or null if address is null or no quality results found.
     */
    public function getSingleCoordinatesOrNull(?string $address = null, ?string $alpha2 = null): ?GeocodeResult
    {
        if ($address === null) {
            return null;
        }

        $result = $this->getCoordinates($address, $alpha2, 1);

        return $result->confidence > 0 ? $result : null;
    }

    /**
     * Check if the Geocoding API service is available.
     *
     * @param  bool  $useCache  Whether to use the 5-minute availability cache status (default: true) to avoid hammering the API.
     *
     * @return bool True if the Geocoding API service is available and seemingly functional.
     */
    public function isGeocodingAvailable(bool $useCache = true): bool
    {
        $cacheKey = 'geocoding_service_availability';

        if ($useCache) {
            $cached = cache($cacheKey);
            if ($cached !== null) {
                return (bool) $cached;
            }
        }

        $isAvailable = $this->performAvailabilityCheck();
        cache([$cacheKey => $isAvailable], now()->addMinutes(5));

        return $isAvailable;
    }

    /**
     * Format OpenCage response to application format.
     *
     * @param  string  $query  The query string (either `lat, long`, or an `address` string) for reference.
     * @param  array<string, mixed>  $openCageResponse  Raw OpenCage API response to be formatted.
     *
     * @return GeocodeResult Formatted response.
     */
    private function formatOpenCageResponse(string $query, array $openCageResponse): GeocodeResult
    {
        if (! isset($openCageResponse['results']) || ! is_array($openCageResponse['results']) ||
            ! isset($openCageResponse['total_results']) || $openCageResponse['total_results'] === 0) {
            return new GeocodeResult(
                query: $query,
                source: self::GEOCODING_SOURCE,
                confidence: 0,
                type: null,
                lat: null,
                lng: null,
                alpha2: null,
                country: null,
                region: null,
                city: null,
                postal_code: null,
                formatted_address: null,
                response: $openCageResponse,
            );
        }

        $firstResult = $openCageResponse['results'][0];
        if (! is_array($firstResult)) {
            throw new RuntimeException('Invalid OpenCage result structure');
        }

        $geometry = $firstResult['geometry'] ?? [];
        $components = $firstResult['components'] ?? [];

        if (! is_array($geometry)) {
            $geometry = [];
        }
        if (! is_array($components)) {
            $components = [];
        }

        return new GeocodeResult(
            query: $query,
            source: self::GEOCODING_SOURCE,
            confidence: is_int($firstResult['confidence'] ?? null) ? $firstResult['confidence'] : 0,
            type: is_string($components['_type'] ?? null) ? $components['_type'] : null,
            lat: is_numeric($geometry['lat'] ?? null) ? (float) ($geometry['lat']) : null,
            lng: is_numeric($geometry['lng'] ?? null) ? (float) ($geometry['lng']) : null,
            // The returned `country_code` is preferred, as the ISO could be a list.
            alpha2: isset($components['country_code']) && is_string($components['country_code']) ? strtoupper($components['country_code']) : null,
            country: is_string($components['country'] ?? null) ? $components['country'] : null,
            region: is_string($components['state'] ?? null) ? $components['state'] : null,
            city: is_string($components['_normalized_city'] ?? null) ? $components['_normalized_city'] : null,
            postal_code: is_string($components['postcode'] ?? null) ? $components['postcode'] : null,
            formatted_address: is_string($firstResult['formatted'] ?? null) ? $firstResult['formatted'] : null,
            response: $openCageResponse,
        );
    }

    /**
     * Get a test address by country code.
     *
     * @param  string  $alpha2  ISO-2 country code
     *
     * @return string The configured test address
     *
     * @throws InvalidArgumentException if a test address for an ISO-2 country code does not exist
     */
    private function getTestAddress(string $alpha2 = 'de'): string
    {
        if (! isset(self::TEST_ADDRESSES[$alpha2])) {
            throw new InvalidArgumentException("No test address for country '$alpha2'.");
        }

        return self::TEST_ADDRESSES[$alpha2];
    }

    /**
     * Check if the API key is configured and not empty.
     */
    private function hasApiKey(): bool
    {
        $apiKey = config('services.opencage.api_key');

        return ! empty($apiKey) && is_string($apiKey) && strlen(trim($apiKey)) > 10;
    }

    /**
     * Perform the availability check for the Geocoding API.
     *
     * @return bool True if geocoding API is available.
     */
    private function performAvailabilityCheck(): bool
    {
        try {
            // Validates the existence of an API key:
            if (! $this->hasApiKey()) {
                Log::warning('OpenCage API key is not configured properly');

                return false;
            }

            // Tests with a simple, reliable, well-known address:
            $testCountry = 'FR';
            $testAddress = $this->getTestAddress($testCountry);
            // Performs the API Call with no cache:
            $result = $this->getCoordinates($testAddress, $testCountry, 1, false);

            if (! $result->hasValidResponse()) {
                Log::warning('OpenCage API returned an invalid geocoding result', $result->toArray());

                return false;
            }

            // Validates that the result is meaningul:
            $confidence = $result->confidence;
            if ($confidence === 0) {
                Log::warning('OpenCage API test returned no meaningful results', [
                    'confidence' => $confidence,
                ]);

                return false;
            }

            // Validates that the coordinates are reasonable:
            if (! $result->hasValidCoordinates()) {
                Log::warning('OpenCage API returned invalid coordinates', [
                    'lat' => $result->lat ?? 'missing',
                    'lng' => $result->lng ?? 'missing',
                ]);

                return false;
            }

            Log::info('OpenCage geocoding service availability check passed');

            return true;

        } catch (Exception $e) {
            Log::error('OpenCage API failed with error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Build a cache key that includes all parameters affecting the API response.
     *
     * @param  string  $query  The geocoding query (address or coordinates)
     * @param  array<string, mixed>  $params  The query parameters
     *
     * @return string The cache key
     *
     * @throws RuntimeException
     */
    private function buildCacheKey(string $query, array $params): string
    {
        // Sorts params to ensure consistent cache keys regardless of order:
        ksort($params);

        // Creates a hash of the parameters to avoid very long cache keys:
        $jsonParams = json_encode($params);
        if ($jsonParams === false) {
            throw new RuntimeException('Failed to encode parameters to JSON');
        }
        $paramsHash = md5($jsonParams);

        return "$query:$paramsHash";
    }

    /**
     * Retrieves an entry on the Geocoding Cache table of the Database.
     *
     * @return array<string, mixed>|null
     */
    protected function retrieveFromCache(string $key): ?array
    {
        $cached = GeocodingCache::where('key', $key)->valid()->first();

        $response = $cached?->response;
        if ($response === null) {
            return null;
        }

        /** @var array<string, mixed> $response */
        return $response;
    }

    /**
     * Stores an entry on the Geocoding Cache table of the Database.
     *
     * @param  array<string, mixed>  $response
     */
    protected function storeInCache(string $key, array $response, GeocodingCacheType $type = GeocodingCacheType::FORWARD): void
    {
        GeocodingCache::updateOrCreate(
            ['key' => $key],
            [
                'source' => GeocodingCacheSource::OPENCAGE,
                'type' => $type,
                'response' => $response,
                'expires_at' => now()->addDays(self::CACHE_DURATION_DAYS),
            ]
        );
    }

    /**
     * Normalise address for better geocoding results based on country-specific rules.
     *
     * @param  string  $address  The original address
     * @param  string|null  $alpha2  ISO alpha-2 country code (e.g. 'GR', 'DE')
     *
     * @return string The normalised address
     */
    protected function normaliseAddressForCountry(string $address, ?string $alpha2 = null): string
    {
        if ($alpha2 === null) {
            return $address;
        }

        $normalisedAddress = $address;

        // Greece-specific normalisations
        if (strtoupper($alpha2) === 'GR') {
            // Remove various "Leoforos" prefixes as OSM data doesn't include them
            $normalisedAddress = preg_replace('/^(Leoforos|Leof\.|L\.|Λεωφόρος|Λεωφορος|Λεωφ\.)\s+/ui', '', $normalisedAddress) ?? $normalisedAddress;

        }

        // Cleans up any leading comma and spaces that might result from normalisations and returns.
        return preg_replace('/^[\s,]+/', '', $normalisedAddress) ?? $normalisedAddress;
    }
}
