<?php

namespace App\Enums;

/**
 * Enum representing the source of geocoding cache entries.
 * Only includes actual geocoding services that can be cached.
 */
enum GeocodingCacheSource: string
{
    case OPENCAGE = 'opencage'; // OpenCage Data geocoding service
    case GOOGLE = 'google'; // Google Maps Geocoding API (legacy)
    case MAPBOX = 'mapbox'; // MapBox geocoding API (legacy)
}