<?php

namespace App\Enums;

/**
 * Enum representing the type of geocoding cache entry.
 */
enum GeocodingCacheType: string
{
    case FORWARD = 'forward'; // Address to coordinates geocoding
    case REVERSE = 'reverse'; // Coordinates to address geocoding
}
