<?php

namespace App\DataObjects;

use App\Enums\OrganisationLocationSource;
use App\Models\Filters\Country;

/**
 * Class GeoLocationObject
 * Represents the geolocation data of a resolved address.
 */
class GeoLocationObject
{
    /**
     * @var OrganisationLocationSource|null The source of the location data.
     */
    public ?OrganisationLocationSource $source;

    /**
     * @var int Confidence score (0-10): The precision and accuracy of the result. 10 = exact match, 0 = unable to determine. In general, results under 9 should be considered bad.
     */
    public int $confidence;

    /**
     * @var float|null The latitude of the location.
     */
    public ?float $lat;

    /**
     * @var float|null The longitude of the location.
     */
    public ?float $lng;

    /**
     * @var Country|null The country associated with the location.
     */
    public ?Country $country;

    /**
     * @var string|null The formatted postal code of the location.
     */
    public ?string $postalCode;

    /**
     * @var string|null The formatted address of the location.
     */
    public ?string $formattedAddress;

    /**
     * @var mixed|null The raw response from the geolocation service.
     */
    public mixed $response;

    /**
     * Constructor for the LocationObject.
     */
    public function __construct(
        ?OrganisationLocationSource $source = null,
        int $confidence = 0,
        ?float $lat = null,
        ?float $lng = null,
        ?Country $country = null,
        ?string $postalCode = null,
        ?string $formattedAddress = null,
        mixed $response = null
    ) {
        $this->source = $source;
        $this->confidence = $confidence;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->country = $country;
        $this->postalCode = $postalCode;
        $this->formattedAddress = $formattedAddress;
        $this->response = $response;
    }

    /**
     * Set the confidence score of the geolocation result.
     */
    public function setConfidence(int $confidence): void
    {
        $this->confidence = $confidence;
    }

    /**
     * Set the source of the location data.
     */
    public function setSource(?OrganisationLocationSource $source): void
    {
        $this->source = $source;
    }

    /**
     * Set the latitude of the location.
     */
    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * Set the longitude of the location.
     */
    public function setLng(?float $lng): void
    {
        $this->lng = $lng;
    }

    /**
     * Set the country associated with the location.
     */
    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    /**
     * Set the formatted postal code of the location.
     */
    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Set the formatted address of the location.
     */
    public function setFormattedAddress(?string $formattedAddress): void
    {
        $this->formattedAddress = $formattedAddress;
    }

    /**
     * Set the raw response from the geolocation service.
     *
     * @param  mixed|null  $response
     */
    public function setResponse(mixed $response): void
    {
        $this->response = $response;
    }
}
