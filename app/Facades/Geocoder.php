<?php

declare(strict_types=1);

namespace App\Facades;

use App\DataObjects\GeocodeResult;
use Illuminate\Support\Facades\Facade;

/**
 * Geocoder Facade
 *
 * This facade provides a convenient static interface to the Geocoder service,
 * which handles address-to-coordinates conversion (forward geocoding) and
 * coordinates-to-address conversion (reverse geocoding) using the OpenCage API.
 *
 * The facade allows you to use geocoding methods statically throughout your
 * application without manually resolving the service from the container.
 *
 * Example usage:
 *   $result = Geocoder::getCoordinates('Berlin, Germany', 'de');
 *   $address = Geocoder::getAddress(52.5200, 13.4050);
 *   $isAvailable = Geocoder::isGeocodingAvailable();
 *
 * All geocoding results are cached for 90 days to improve performance and
 * reduce API calls. The service includes quality filtering and validation.
 *
 * @method static GeocodeResult getCoordinates(string $address, ?string $alpha2 = null, int $limit = 10, bool $useCache = true) Convert address to coordinates (forward geocoding)
 * @method static GeocodeResult getAddress(float $lat, float $lng, bool $useCache = true) Convert coordinates to address (reverse geocoding)
 * @method static GeocodeResult|null getSingleCoordinatesOrNull(?string $address = null, ?string $alpha2 = null) Get single high-quality result or null (useful for imports)
 * @method static bool isGeocodingAvailable(bool $useCache = true) Check if the geocoding service is available
 *
 * @see \App\Services\Geocoder The underlying service implementation
 */
class Geocoder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'geocoder';
    }
}
