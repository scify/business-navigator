<?php

declare(strict_types=1);

namespace App\Imports\Helpers;

use App\DataObjects\GeocodeResult;
use App\DataObjects\GeoLocationObject;
use App\Facades\Geocoder;
use App\Models\Filters\Country;
use CommerceGuys\Addressing\Address;
use Illuminate\Support\Collection;
use Rpungello\LaravelAddressing\Facades\AddressFormatter;
use Throwable;

/**
 * Class LocationResolverHelper
 * Handles location resolution and geocoding for import operations.
 */
class OrganisationsImportLocationHelper
{
    /**
     * Check if Geocoding API is available.
     *
     * @return bool True if the Geocoding API is available.
     */
    public static function isGeocodingAvailable(): bool
    {
        return Geocoder::isGeocodingAvailable(false);

    }

    /**
     * Resolve location data for an organisation from row data.
     *
     * @param  Collection<string, mixed>  $row  The row data
     * @param  callable  $valueExtractor  Function to extract trimmed values: fn($row, $field) => string|null
     * @param  array<string>  $addressFields  Fields to combine for address lookup (default: ['address', 'place', 'region'])
     *
     * @return GeoLocationObject|false Location data or false if country not supported
     */
    public static function resolveLocationData(
        Collection $row,
        callable $valueExtractor,
        array $addressFields = ['address_1', 'city', 'region'],
        ?ImportLoggerHelper $logger = null,
        ?int $index = null
    ): GeoLocationObject|false {

        $name = $valueExtractor($row, 'name') ?? 'Unknown Organisation';

        $country = $valueExtractor($row, 'country');
        $sourceCountry = Country::findByName((string) $country);
        $sourcePostalCode = $valueExtractor($row, 'postal_code');
        $sourcePostalCode = is_string($sourcePostalCode) ? $sourcePostalCode : null;

        // Abort if country is not supported
        if ($sourceCountry === null) {
            if ($logger && $index !== null) {
                // Error will be logged on parent class.
                $logger->recordDebug($index, 'Unsupported country ' . $country . " for '$name'.");
            }

            return false;

        }

        // Build address string from multiple fields
        $address = self::buildAddressString($row, $valueExtractor, $addressFields, ', ');

        // Attempts to resolve coordinates via Geocoder:
        $geocodeResult = Geocoder::getSingleCoordinatesOrNull($address, $sourceCountry->alpha2);

        // Starts building a location object regardless of the result:
        $location = new GeoLocationObject;
        $location->setCountry($sourceCountry);
        $location->setPostalCode($sourcePostalCode);

        // Adds any additional information provided via the Geocoder:
        if ($geocodeResult !== null) {
            self::handlePostalCodeDifferences(
                $geocodeResult,
                $sourcePostalCode,
                $name,
                $location,
                $logger,
                $index
            );

            $resolvedCountry = self::handleCountryDifferences(
                $geocodeResult,
                $sourceCountry,
                $name,
                $location,
                $logger,
                $index
            );

            // Return false if resolved country is not supported
            if ($resolvedCountry === false) {
                return false;

            }

            // Sets geocoded location details
            $location->setSource($geocodeResult->source);
            $location->setConfidence($geocodeResult->confidence);
            $location->setLat($geocodeResult->lat);
            $location->setLng($geocodeResult->lng);
            // Use enhanced address formatter if possible, fallback to OpenCage formatted address
            $enhancedAddress = self::formatAddressFromGeocodeResult($geocodeResult, $row, $valueExtractor);
            $location->setFormattedAddress($enhancedAddress);
            $location->setResponse($geocodeResult->response);
        }

        return $location;
    }

    /**
     * Build address string from multiple row fields.
     *
     * @param  Collection<string, mixed>  $row  The pure raw data.
     * @param  callable  $valueExtractor  Function to extract trimmed values.
     * @param  array<string>  $addressFields  Fields to combine for address.
     * @param  string  $separator  Separator for fields.
     *
     * @return string The combined address string
     */
    private static function buildAddressString(
        Collection $row,
        callable $valueExtractor,
        array $addressFields,
        string $separator = ' '
    ): string {
        $addressParts = [];

        foreach ($addressFields as $field) {
            $value = $valueExtractor($row, $field);
            if (! empty($value)) {
                $addressParts[] = $value;
            }
        }

        return implode($separator, $addressParts);
    }

    /**
     * Handle postal code differences between source and geocoded data.
     *
     * @param  GeocodeResult  $geocodeResult  Geocoded location details
     * @param  string|null  $sourcePostalCode  Original postal code from row
     * @param  string  $name  Organisation name for logging
     * @param  GeoLocationObject  $location  Location object to update
     */
    private static function handlePostalCodeDifferences(
        GeocodeResult $geocodeResult,
        ?string $sourcePostalCode,
        string $name,
        GeoLocationObject $location,
        ?ImportLoggerHelper $logger = null,
        ?int $index = null
    ): void {
        if (empty($sourcePostalCode) || $geocodeResult->postal_code === $sourcePostalCode) {
            return;

        }

        if ($logger && $index !== null) {
            $logger->recordDebug($index, "Resolved postal code $geocodeResult->postal_code for $name differs from source ($sourcePostalCode).");
        }

        // Check if they're the same when normalized (removing spaces and dashes)
        $normalizedResolved = preg_replace('/[\s-]+/', '', $geocodeResult->postal_code ?? '');
        $normalizedSource = preg_replace('/[\s-]+/', '', $sourcePostalCode);

        if ($normalizedResolved === $normalizedSource) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Updating postal code $sourcePostalCode to $geocodeResult->postal_code for consistency.");
            }
            $location->setPostalCode($geocodeResult->postal_code);
        }
    }

    /**
     * Handle country differences between source and geocoded data.
     *
     * @param  GeocodeResult  $geocodeResult  Geocoded location details
     * @param  Country  $sourceCountry  Original country from row
     * @param  string  $name  Organisation name for logging
     * @param  GeoLocationObject  $location  Location object to update
     *
     * @return bool|Country False if resolved country not supported, Country object otherwise
     */
    private static function handleCountryDifferences(
        GeocodeResult $geocodeResult,
        Country $sourceCountry,
        string $name,
        GeoLocationObject $location,
        ?ImportLoggerHelper $logger = null,
        ?int $index = null
    ): bool|Country {
        // Check if geocoded country differs from source
        if ($geocodeResult->alpha2 !== $sourceCountry->alpha2) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Resolved country $geocodeResult->alpha2 for $name differs from source ($sourceCountry->alpha2).");
            }
        }

        $resolvedCountry = Country::findByShortCode($geocodeResult->alpha2);

        // Returns false if resolved country is not supported (is null):
        if ($resolvedCountry === null) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Unsupported country $geocodeResult->alpha2 for $name.");
            }

            return false;

        }

        // Updates location if countries differ:
        if ($resolvedCountry->alpha2 !== $sourceCountry->alpha2) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Updating country for $name from $sourceCountry->alpha2 to $resolvedCountry->alpha2.");
            }
            $location->setCountry($resolvedCountry);
        }

        return $resolvedCountry;

    }

    /**
     * Format address using address formatter library, with fallback to OpenCage formatted address.
     *
     * @param  GeocodeResult  $geocodeResult  Geocoded location details
     * @param  Collection<string, mixed>  $row  The raw row data
     * @param  callable  $valueExtractor  Function to extract trimmed values
     *
     * @return string|null The formatted address
     */
    private static function formatAddressFromGeocodeResult(
        GeocodeResult $geocodeResult,
        Collection $row,
        callable $valueExtractor
    ): ?string {
        $originalCountry = $valueExtractor($row, 'country');
        $sourceCountry = $originalCountry ? Country::findByName((string) $originalCountry) : null;
        try {
            // Create address object prioritizing original data over geocoded data
            $address = new Address(
                countryCode: $sourceCountry?->alpha2 ?: $geocodeResult->alpha2 ?: 'FR',
                administrativeArea: $valueExtractor($row, 'region') ?: $geocodeResult->region,
                locality: $valueExtractor($row, 'city') ?: $geocodeResult->city,
                postalCode: $valueExtractor($row, 'postal_code') ?: $geocodeResult->postal_code,
                addressLine1: $valueExtractor($row, 'address_1'),
                addressLine2: $valueExtractor($row, 'address_2'),
            );

            // Use the address formatter to create a properly formatted address
            $formattedAddress = AddressFormatter::formatDefault($address);
            // Clean up any carriage returns, keep newlines for proper formatting
            return $formattedAddress ? str_replace(["\r"], '', $formattedAddress) : null;
            // return $formattedAddress ? mb_trim($formattedAddress) : null;

        } catch (Throwable) {
            // Fallback to OpenCage formatted address if address formatter fails
            return $geocodeResult->formatted_address ? mb_trim($geocodeResult->formatted_address) : null;
        }
    }
}
