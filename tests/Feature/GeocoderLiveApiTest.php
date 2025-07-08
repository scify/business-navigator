<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace Tests\Feature;

use App\DataObjects\GeocodeResult;
use App\Facades\Geocoder;
use App\Services\Geocoder as GeocoderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Live API integration tests for the Geocoder service.
 *
 * These tests make actual API calls to OpenCage and are marked with the live-api
 * group so they can be run separately from the core test suite.
 *
 * Run with: php artisan test --group=live-api
 * Skip with: php artisan test --exclude-group=live-api
 */
class GeocoderLiveApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip if no API key is configured
        if (empty(config('services.opencage.api_key'))) {
            $this->markTestSkipped('OpenCage API key is not configured for live API tests');
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_can_geocode_famous_addresses()
    {
        $testCases = [
            'FR' => ['address' => '5 Avenue Anatole France, 75007 Paris', 'expected_country' => 'France'],
            'DE' => ['address' => 'Pariser Platz, 10117 Berlin', 'expected_country' => 'Germany'],
            'IT' => ['address' => 'Piazza del Colosseo, 1, 00184 Roma RM', 'expected_country' => 'Italy'],
            'NL' => ['address' => 'Dam, 1012 NP Amsterdam', 'expected_country' => 'Netherlands'],
            'ES' => ['address' => 'Puerta del Sol, 28013 Madrid', 'expected_country' => 'Spain'],
        ];

        foreach ($testCases as $countryCode => $testCase) {
            $result = Geocoder::getCoordinates($testCase['address'], strtolower($countryCode), 1, false);

            $this->assertGreaterThan(0, $result->confidence, "Geocoding should return confidence > 0 for {$testCase['address']}");
            $this->assertNotNull($result->lat, "Latitude should not be null for {$testCase['address']}");
            $this->assertNotNull($result->lng, "Longitude should not be null for {$testCase['address']}");
            $this->assertNotNull($result->formatted_address, "Formatted address should not be null for {$testCase['address']}");
            $this->assertEquals(strtoupper($countryCode), $result->alpha2, "Country code should match for {$testCase['address']}");
            $this->assertTrue($result->hasValidResponse(), "GeocodeResult should be valid for {$testCase['address']}");

            // Validate coordinates are within reasonable bounds
            $this->assertGreaterThanOrEqual(-90, $result->lat);
            $this->assertLessThanOrEqual(90, $result->lat);
            $this->assertGreaterThanOrEqual(-180, $result->lng);
            $this->assertLessThanOrEqual(180, $result->lng);
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_can_perform_reverse_geocoding()
    {
        $testCases = [
            ['lat' => 48.8566, 'lng' => 2.3522, 'expected_country' => 'France'], // Paris
            ['lat' => 52.5200, 'lng' => 13.4050, 'expected_country' => 'Germany'], // Berlin
            ['lat' => 41.9028, 'lng' => 12.4964, 'expected_country' => 'Italy'], // Rome
        ];

        foreach ($testCases as $testCase) {
            $result = Geocoder::getAddress($testCase['lat'], $testCase['lng'], false);

            $this->assertGreaterThan(0, $result->confidence, "Reverse geocoding should return confidence > 0 for {$testCase['lat']}, {$testCase['lng']}");
            $this->assertNotNull($result->formatted_address, "Formatted address should not be null for {$testCase['lat']}, {$testCase['lng']}");
            $this->assertTrue($result->hasValidResponse(), "GeocodeResult should be valid for {$testCase['lat']}, {$testCase['lng']}");
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_handles_country_bias_correctly()
    {
        // Test with "Berlin" - should return Berlin, Germany when biased to DE
        $resultDE = Geocoder::getCoordinates('Berlin', 'de', 1, false);
        $this->assertEquals('DE', $resultDE->alpha2);
        $this->assertGreaterThan(0, $resultDE->confidence);

        // Test with "Paris" - should return Paris, France when biased to FR
        $resultFR = Geocoder::getCoordinates('Paris', 'fr', 1, false);
        $this->assertEquals('FR', $resultFR->alpha2);
        $this->assertGreaterThan(0, $resultFR->confidence);
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_handles_invalid_addresses_gracefully()
    {
        $invalidAddresses = [
            'ThisIsNotARealAddressAnywhere12345',
            'Invalid Street 99999, NonExistentCity',
            'ñóñ-ëxîstëñt àddréss',
        ];

        foreach ($invalidAddresses as $address) {
            $result = Geocoder::getCoordinates($address, null, 1, false);

            // Should either return no results (confidence 0) or very low confidence
            $this->assertLessThanOrEqual(3, $result->confidence, "Invalid address should return low confidence: $address");
            $this->assertTrue($result->hasValidResponse(), "GeocodeResult should still be valid structure for invalid address: $address");
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_handles_invalid_coordinates_gracefully()
    {
        $invalidCoordinates = [
            ['lat' => 91.0, 'lng' => 0.0], // Latitude out of bounds
            ['lat' => 0.0, 'lng' => 181.0], // Longitude out of bounds
        ];

        foreach ($invalidCoordinates as $coords) {
            $result = Geocoder::getAddress($coords['lat'], $coords['lng'], false);

            $this->assertFalse($result->hasValidResponse(), "Invalid coordinates should not return valid geolocation: {$coords['lat']}, {$coords['lng']}");
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_respects_result_limits()
    {
        $result = Geocoder::getCoordinates('Berlin', 'de', 1, false);

        $this->assertTrue($result->hasValidResponse());

        // The response should contain the limited results
        $response = $result->response;
        $this->assertIsArray($response);
        $this->assertArrayHasKey('results', $response);
        $this->assertLessThanOrEqual(1, count($response['results']));
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_caches_results_correctly()
    {
        $address = 'Brandenburg Gate, Berlin';
        $countryCode = 'de';

        // First call - should hit API
        $startTime = microtime(true);
        $result1 = Geocoder::getCoordinates($address, $countryCode, 1);
        $firstCallTime = microtime(true) - $startTime;

        // Second call - should use cache (should be faster)
        $startTime = microtime(true);
        $result2 = Geocoder::getCoordinates($address, $countryCode, 1);
        $secondCallTime = microtime(true) - $startTime;

        $this->assertEquals($result1->lat, $result2->lat);
        $this->assertEquals($result1->lng, $result2->lng);
        $this->assertEquals($result1->confidence, $result2->confidence);
        $this->assertEquals($result1->formatted_address, $result2->formatted_address);

        // Cache should make second call faster (though this can be flaky)
        $this->assertLessThan($firstCallTime * 2, $secondCallTime, 'Cached call should be faster than API call');
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_performs_availability_check_correctly()
    {
        $geocoder = app(GeocoderService::class);

        // Test availability check (bypassing cache)
        $isAvailable = $geocoder->isGeocodingAvailable(false);

        $this->assertTrue($isAvailable, 'Service should be available with valid API key');

        // Test with invalid API key
        Config::set('services.opencage.api_key', 'invalid-key');
        $geocoderWithInvalidKey = new GeocoderService;

        $isAvailableWithInvalidKey = $geocoderWithInvalidKey->isGeocodingAvailable(false);
        $this->assertFalse($isAvailableWithInvalidKey, 'Service should not be available with invalid API key');
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_handles_single_coordinates_method()
    {
        // Test with valid address
        $result = Geocoder::getSingleCoordinatesOrNull('Eiffel Tower, Paris', 'fr');
        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertGreaterThan(0, $result->confidence);
        $this->assertEquals('FR', $result->alpha2);

        // Test with null address
        $result = Geocoder::getSingleCoordinatesOrNull();
        $this->assertNull($result);

        // Test with very ambiguous address (might return null or low confidence)
        $result = Geocoder::getSingleCoordinatesOrNull('Main Street');
        if ($result !== null) {
            $this->assertInstanceOf(GeocodeResult::class, $result);
            $this->assertGreaterThan(0, $result->confidence);
        }
    }

    #[Test]
    #[Group('live-api')]
    public function geocoder_live_api_handles_unicode_addresses()
    {
        $unicodeAddresses = [
            'Champs-Élysées, Paris', // French accents
            'Potsdamer Platz, Berlin', // German umlaut
        ];

        foreach ($unicodeAddresses as $address) {
            $result = Geocoder::getCoordinates($address, null, 1, false);

            $this->assertTrue($result->hasValidResponse(), "Should handle unicode address: $address");
        }
    }
}
