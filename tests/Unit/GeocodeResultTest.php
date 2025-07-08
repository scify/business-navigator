<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DataObjects\GeocodeResult;
use App\Enums\OrganisationLocationSource;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeocodeResultTest extends TestCase
{
    #[Test]
    public function geocode_result_creates_with_all_properties()
    {
        $response = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['state' => 'Berlin', 'country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $response
        );

        $this->assertEquals('Berlin, Germany', $result->query);
        $this->assertEquals(9, $result->confidence);
        $this->assertEquals(52.5200, $result->lat);
        $this->assertEquals(13.4050, $result->lng);
        $this->assertEquals('Berlin, Germany', $result->formatted_address);
        $this->assertEquals('10117', $result->postal_code);
        $this->assertEquals('DE', $result->alpha2);
        $this->assertEquals('Germany', $result->country);
        $this->assertEquals($response, $result->response);
    }

    #[Test]
    public function geocode_result_creates_with_null_values()
    {
        $response = ['results' => [], 'total_results' => 0];

        $result = new GeocodeResult(
            query: 'Invalid Address',
            source: OrganisationLocationSource::OPENCAGE,
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
            response: $response
        );

        $this->assertEquals('Invalid Address', $result->query);
        $this->assertEquals(0, $result->confidence);
        $this->assertNull($result->lat);
        $this->assertNull($result->lng);
        $this->assertNull($result->formatted_address);
        $this->assertNull($result->postal_code);
        $this->assertNull($result->alpha2);
        $this->assertNull($result->country);
        $this->assertEquals($response, $result->response);
    }

    #[Test]
    public function geocode_result_converts_to_array_correctly()
    {
        $response = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                ],
            ],
            'total_results' => 1,
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $response
        );

        $expected = [
            'query' => 'Berlin, Germany',
            'source' => 'opencage',
            'confidence' => 9,
            'type' => 'city',
            'lat' => 52.5200,
            'lng' => 13.4050,
            'alpha2' => 'DE',
            'country' => 'Germany',
            'region' => 'Berlin',
            'city' => 'Berlin',
            'postal_code' => '10117',
            'formatted_address' => 'Berlin, Germany',
            'response' => $response,
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[Test]
    public function geocode_result_validates_proper_response_structure()
    {
        $validResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $validResponse
        );

        $this->assertTrue($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_validates_empty_response_structure()
    {
        $emptyResponse = [
            'results' => [],
            'total_results' => 0,
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Invalid Address',
            source: OrganisationLocationSource::OPENCAGE,
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
            response: $emptyResponse
        );

        $this->assertTrue($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_missing_results()
    {
        $invalidResponse = [
            'total_results' => 1,
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_with_non_array_results()
    {
        $invalidResponse = [
            'results' => 'invalid',
            'total_results' => 1,
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_missing_total_results()
    {
        $invalidResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                ],
            ],
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_with_non_200_status()
    {
        $invalidResponse = [
            'results' => [],
            'total_results' => 0,
            'status' => ['code' => 402, 'message' => 'Payment Required'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
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
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_with_non_ok_status_message()
    {
        $invalidResponse = [
            'results' => [],
            'total_results' => 0,
            'status' => ['code' => 200, 'message' => 'Error'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
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
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_invalidates_response_missing_required_result_fields()
    {
        $invalidResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    // Missing geometry
                ],
            ],
            'total_results' => 1,
            'status' => ['code' => 200, 'message' => 'OK'],
        ];

        $result = new GeocodeResult(
            query: 'Berlin, Germany',
            source: OrganisationLocationSource::OPENCAGE,
            confidence: 9,
            type: 'city',
            lat: 52.5200,
            lng: 13.4050,
            alpha2: 'DE',
            country: 'Germany',
            region: 'Berlin',
            city: 'Berlin',
            postal_code: '10117',
            formatted_address: 'Berlin, Germany',
            response: $invalidResponse
        );

        $this->assertFalse($result->hasValidResponse());
    }

    #[Test]
    public function geocode_result_validates_coordinates_correctly()
    {
        // Valid coordinates
        $berlinResult = new GeocodeResult('Berlin', OrganisationLocationSource::OPENCAGE, 5, 'city', 52.5200, 13.4050, 'DE', 'Germany', 'Berlin', 'Berlin', null, 'Berlin, Germany', []);
        $this->assertTrue($berlinResult->hasValidCoordinates());

        $buenosAiresResult = new GeocodeResult('Buenos Aires', OrganisationLocationSource::OPENCAGE, 5, 'city', -34.6037, -58.3816, 'AR', 'Argentina', 'Buenos Aires', 'Buenos Aires', null, 'Buenos Aires, Argentina', []);
        $this->assertTrue($buenosAiresResult->hasValidCoordinates());

        // Boundary values - valid
        $northPoleResult = new GeocodeResult('North Pole', OrganisationLocationSource::OPENCAGE, 5, 'point', 90.0, 180.0, null, null, null, null, null, 'North Pole', []);
        $this->assertTrue($northPoleResult->hasValidCoordinates());

        $southPoleResult = new GeocodeResult('South Pole', OrganisationLocationSource::OPENCAGE, 5, 'point', -90.0, -180.0, null, null, null, null, null, 'South Pole', []);
        $this->assertTrue($southPoleResult->hasValidCoordinates());

        // Valid zero coordinates (not null island)
        $zeroLatResult = new GeocodeResult('Zero Lat', OrganisationLocationSource::OPENCAGE, 5, 'water', 0.0, 1.0, null, null, null, null, null, 'Gulf of Guinea', []);
        $this->assertTrue($zeroLatResult->hasValidCoordinates());

        $zeroLngResult = new GeocodeResult('Zero Lng', OrganisationLocationSource::OPENCAGE, 5, 'water', 1.0, 0.0, null, null, null, null, null, 'Atlantic Ocean', []);
        $this->assertTrue($zeroLngResult->hasValidCoordinates());

        // Invalid coordinates
        $highLatResult = new GeocodeResult('Invalid High Lat', OrganisationLocationSource::OPENCAGE, 0, null, 91.0, 0.0, null, null, null, null, null, null, []);
        $this->assertFalse($highLatResult->hasValidCoordinates());

        $lowLatResult = new GeocodeResult('Invalid Low Lat', OrganisationLocationSource::OPENCAGE, 0, null, -91.0, 0.0, null, null, null, null, null, null, []);
        $this->assertFalse($lowLatResult->hasValidCoordinates());

        $highLngResult = new GeocodeResult('Invalid High Lng', OrganisationLocationSource::OPENCAGE, 0, null, 0.0, 181.0, null, null, null, null, null, null, []);
        $this->assertFalse($highLngResult->hasValidCoordinates());

        $lowLngResult = new GeocodeResult('Invalid Low Lng', OrganisationLocationSource::OPENCAGE, 0, null, 0.0, -181.0, null, null, null, null, null, null, []);
        $this->assertFalse($lowLngResult->hasValidCoordinates());

        // Null island rejection
        $nullIslandResult = new GeocodeResult('Null Island', OrganisationLocationSource::OPENCAGE, 0, null, 0.0, 0.0, null, null, null, null, null, null, []);
        $this->assertFalse($nullIslandResult->hasValidCoordinates());

        // Null coordinates
        $nullCoordsResult = new GeocodeResult('No Coords', OrganisationLocationSource::OPENCAGE, 0, null, null, null, null, null, null, null, null, null, []);
        $this->assertFalse($nullCoordsResult->hasValidCoordinates());

        // Non-numeric coordinates (should be caught by type system in real usage)
        $mixedCoordsResult = new GeocodeResult('Mixed Coords', OrganisationLocationSource::OPENCAGE, 0, null, null, 13.4050, null, null, null, null, null, null, []);
        $this->assertFalse($mixedCoordsResult->hasValidCoordinates());
    }
}
