<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\OrganisationLocationSource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * GeocodeResult - Raw API response data (immutable)
 *
 * Direct representation of what the geocoding API returns. Contains the
 * original query, the provider (source) of the response, precision score,
 * latitude and longitude, address and the raw response itself.
 */
readonly class GeocodeResult implements Arrayable, Jsonable
{
    /**
     * @param  string  $query  The original address query sent to the geocoding API.
     * @param  OrganisationLocationSource  $source  The geocoding provider that returned this result.
     * @param  int  $confidence  Confidence score (0-10): how confident the API is about the precision of the result (10 = exact match, 0 = no confidence).
     * @param  string|null  $type  What the matched location is believed to be: building, road, place, hamlet, village, neighbourhood, city, county, postcode, partial_postcode, terminated_postcode, postal_city, state_district, state, region, island, body_of_water, country, continent, fictitious, unknown
     * @param  float|null  $lat  Latitude coordinate in decimal degrees.
     * @param  float|null  $lng  Longitude coordinate in decimal degrees.
     * @param  string|null  $alpha2  ISO 3166-1 alpha-2 country code (e.g. 'AT', 'GR', 'DE').
     * @param  string|null  $country  Country name in English.
     * @param  string|null  $region  Normalised region/state of the location, if it exists (e.g. Bavaria, Attica, etc.).
     * @param  string|null  $city  Normalised place/city (or town, township, village, municipality, neighborhood, suburb, city district...)
     * @param  string|null  $postal_code  Postal code extracted from the geocoding result.
     * @param  string|null  $formatted_address  Human-readable formatted address returned by the API.
     * @param  array<string, mixed>  $response  Complete raw response from the geocoding API.
     */
    public function __construct(
        public string $query,
        public OrganisationLocationSource $source,
        public int $confidence,
        public ?string $type,
        public ?float $lat,
        public ?float $lng,
        public ?string $alpha2,
        public ?string $country,
        public ?string $region,
        public ?string $city,
        public ?string $postal_code,
        public ?string $formatted_address,
        public array $response,
    ) {}

    /**
     * Convert to array for JSON serialization (e.g. for Inertia responses).
     *
     * @return array{query: string, source: string, confidence: int, lat: float|null, lng: float|null, formatted_address: string|null, postal_code: string|null, alpha2: string|null, country: string|null, response: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'source' => $this->source->value,
            'confidence' => $this->confidence,
            'type' => $this->type,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'alpha2' => $this->alpha2,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'formatted_address' => $this->formatted_address,
            'response' => $this->response,
        ];
    }

    /**
     * Convert to JSON string for JSON serialization.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Validate that this instance's coordinates are within reasonable bounds.
     *
     * Checks if the coordinates are actually valid. Rejects null values, sets
     * proper Earth's bounds and avoids the null island..
     *
     * @return bool True if the instance coordinates are reasonably valid.
     */
    public function hasValidCoordinates(): bool
    {
        // One or more of the values is null:
        if (! is_numeric($this->lat) || ! is_numeric($this->lng)) {
            return false;

        }

        // Force cast to float.
        $lat = (float) $this->lat;
        $lng = (float) $this->lng;

        // One or more of the values is off the limits of the Earth:
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return false;

        }

        // Null island check. Extremely unlikely situation.
        if ($lat === 0.0 && $lng === 0.0) {
            return false;
        }

        return true;
    }

    /**
     * Validate that this instance's API response structure is valid.
     *
     * Checks if the response received from the API contains a valid and usable
     * response: 1 or more results, results with proper geometry, a formatted
     * address and a confidence score, and a geometry with lat and lng values.
     *
     * @return bool True if the instance API response structure is valid.
     */
    public function hasValidResponse(): bool
    {
        // Checks that the OpenCage response has expected structure:
        $response = $this->response;

        if (! isset($response['results']) || ! is_array($response['results'])) {
            return false;
        }

        if (! isset($response['total_results']) || ! is_int($response['total_results'])) {
            return false;
        }

        // Checks status code and message if present:
        if (isset($response['status'])) {
            $status = $response['status'];
            if (! is_array($status) || ! isset($status['code']) || $status['code'] !== 200) {
                return false;
            }
            if (isset($status['message']) && $status['message'] !== 'OK') {
                return false;
            }
        }

        // For successful responses, validate first result structure:
        if ($response['total_results'] > 0 && isset($response['results'][0])) {
            $firstResult = $response['results'][0];
            if (! is_array($firstResult)) {
                return false;
            }

            // Checks required fields in the first result:
            if (! isset($firstResult['geometry'], $firstResult['formatted'], $firstResult['confidence'])) {
                return false;
            }

            // Validates geometry structure:
            $geometry = $firstResult['geometry'];
            if (! is_array($geometry) || ! isset($geometry['lat'], $geometry['lng'])) {
                return false;
            }
        }

        return true;
    }
}
