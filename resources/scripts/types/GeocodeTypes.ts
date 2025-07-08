import type { Country } from './ModelTypes';

export interface GeocodeResult {
    query: string; // The original query string
    source: string; // Geocoding source (e.g. "opencage")
    confidence: number; // OpenCage confidence level (0-10 scale)
    type: string | null; // What the matched location is believed to be: building, road, place, hamlet, ...
    lat: number | null; // Latitude of the location
    lng: number | null; // Longitude of the location
    alpha2: string | null; // Country code (e.g. "DE", "FR")
    country: string | null; // Country name
    region: string | null; // State/region name
    city: string | null; // City name
    postal_code: string | null; // Postal code from OpenCage
    formatted_address: string | null; // Human-readable formatted address
    response: Record<string, unknown>; // Full OpenCage response for debugging
}

export interface GeocodeResponse {
    address: string; // Address used for the geocoding request
    country: string; // Selected country code (alpha2)
    useCache: boolean; // Indicates whether the cache was used
    result: GeocodeResult; // Result from getCoordinates()
    countries: Country[]; // Available countries for selection
}
