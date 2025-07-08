<?php

namespace App\Enums;

/**
 * Enum representing the source of organisation location data.
 */
enum OrganisationLocationSource: string
{
    case MANUAL = 'manual'; // User manually set location
    case OPENCAGE = 'opencage'; // OpenCage Data geocoding service
    case GOOGLE = 'google'; // Google Maps Geocoding API (legacy)
    case MAPBOX = 'mapbox'; // MapBox geocoding API (legacy)
    case OSM = 'osm'; // OpenStreetMap data
    case IMPORT_XLS = 'import_xls'; // Imported with location data from Excel/CSV
    case UNKNOWN = 'unknown'; // For legacy or uncategorised data
}
