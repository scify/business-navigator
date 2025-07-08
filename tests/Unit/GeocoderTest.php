<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace Tests\Unit;

use App\DataObjects\GeocodeResult;
use App\Services\Geocoder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use OpenCage\Geocoder\Geocoder as OpenCageGeocoder;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Tests\TestCase;

class GeocoderTest extends TestCase
{
    use RefreshDatabase;

    private Geocoder $geocoder;

    private MockInterface $mockOpenCageGeocoder;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up valid API key for testing
        Config::set('services.opencage.api_key', 'test-api-key-1234567890');

        // Mock the OpenCage geocoder
        $this->mockOpenCageGeocoder = Mockery::mock(OpenCageGeocoder::class);

        // Create geocoder instance and inject mock
        $this->geocoder = new Geocoder;
        $reflection = new ReflectionClass($this->geocoder);
        $property = $reflection->getProperty('openCageGeocoder');
        $property->setValue($this->geocoder, $this->mockOpenCageGeocoder);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function geocoder_throws_exception_when_api_key_is_missing()
    {
        Config::set('services.opencage.api_key', '');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenCage API key is not configured');

        new Geocoder;
    }

    #[Test]
    public function geocoder_generates_consistent_cache_keys()
    {
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('buildCacheKey');

            $params1 = ['limit' => 10, 'countrycode' => 'de'];
            $params2 = ['countrycode' => 'de', 'limit' => 10]; // Different order

            $key1 = $method->invoke($this->geocoder, 'Berlin, Germany', $params1);
            $key2 = $method->invoke($this->geocoder, 'Berlin, Germany', $params2);

            $this->assertEquals($key1, $key2, 'Cache keys should be consistent regardless of parameter order');

            // Different address should produce different key
            $key3 = $method->invoke($this->geocoder, 'Paris, France', $params1);
            $this->assertNotEquals($key1, $key3, 'Different addresses should produce different cache keys');
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }

    #[Test]
    public function geocoder_formats_valid_opencage_response_correctly()
    {
        $openCageResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => [
                        'lat' => 52.5200,
                        'lng' => 13.4050,
                    ],
                    'components' => [
                        'country' => 'Germany',
                        'country_code' => 'de',
                        'postcode' => '10117',
                    ],
                ],
            ],
            'total_results' => 1,
        ];

        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('formatOpenCageResponse');
            $result = $method->invoke($this->geocoder, 'Berlin, Germany', $openCageResponse);
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals('Berlin, Germany', $result->query);
        $this->assertEquals(9, $result->confidence);
        $this->assertEquals(52.5200, $result->lat);
        $this->assertEquals(13.4050, $result->lng);
        $this->assertEquals('Berlin, Germany', $result->formatted_address);
        $this->assertEquals('10117', $result->postal_code);
        $this->assertEquals('DE', $result->alpha2);
        $this->assertEquals('Germany', $result->country);
        $this->assertEquals($openCageResponse, $result->response);
    }

    #[Test]
    public function geocoder_handles_empty_opencage_response()
    {
        $openCageResponse = [
            'results' => [],
            'total_results' => 0,
        ];

        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('formatOpenCageResponse');
            $result = $method->invoke($this->geocoder, 'Invalid Address', $openCageResponse);
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals('Invalid Address', $result->query);
        $this->assertEquals(0, $result->confidence);
        $this->assertNull($result->lat);
        $this->assertNull($result->lng);
        $this->assertNull($result->formatted_address);
        $this->assertNull($result->postal_code);
        $this->assertNull($result->alpha2);
        $this->assertNull($result->country);
    }

    #[Test]
    public function geocoder_handles_malformed_opencage_response()
    {
        $openCageResponse = [
            'results' => [
                'invalid_structure', // Non-array result
            ],
            'total_results' => 1,
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid OpenCage result structure');

        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('formatOpenCageResponse');
            $method->invoke($this->geocoder, 'Test Query', $openCageResponse);
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }

    #[Test]
    public function geocoder_returns_null_for_null_address_in_single_coordinates()
    {
        $result = $this->geocoder->getSingleCoordinatesOrNull();

        $this->assertNull($result);
    }

    #[Test]
    public function geocoder_returns_null_for_zero_confidence_in_single_coordinates()
    {
        $openCageResponse = [
            'results' => [
                [
                    'confidence' => 0,
                    'formatted' => 'Low confidence result',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => [],
                ],
            ],
            'total_results' => 1,
        ];

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($openCageResponse);

        $result = $this->geocoder->getSingleCoordinatesOrNull('Ambiguous Address');

        $this->assertNull($result);
    }

    #[Test]
    public function geocoder_returns_result_for_positive_confidence_in_single_coordinates()
    {
        $openCageResponse = [
            'results' => [
                [
                    'confidence' => 8,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($openCageResponse);

        $result = $this->geocoder->getSingleCoordinatesOrNull('Berlin, Germany');

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(8, $result->confidence);
    }

    #[Test]
    public function geocoder_gets_test_address_for_valid_country_codes()
    {
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('getTestAddress');

            $this->assertEquals('5 Avenue Anatole France, 75007 Paris', $method->invoke($this->geocoder, 'FR'));
            $this->assertEquals('Pariser Platz, 10117 Berlin', $method->invoke($this->geocoder, 'DE'));
            $this->assertEquals('Piazza del Colosseo, 1, 00184 Roma RM', $method->invoke($this->geocoder, 'IT'));
            $this->assertEquals('Dam, 1012 NP Amsterdam', $method->invoke($this->geocoder, 'NL'));
            $this->assertEquals('Puerta del Sol, 28013 Madrid', $method->invoke($this->geocoder, 'ES'));
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }

    #[Test]
    public function geocoder_throws_exception_for_invalid_country_code_in_test_address()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No test address for country 'XX'.");

        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('getTestAddress');
            $method->invoke($this->geocoder, 'XX');
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }

    #[Test]
    public function geocoder_validates_api_key_correctly()
    {
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('hasApiKey');

            // Valid API key
            Config::set('services.opencage.api_key', 'valid-api-key-1234567890');
            $this->assertTrue($method->invoke($this->geocoder));

            // Short API key
            Config::set('services.opencage.api_key', 'short');
            $this->assertFalse($method->invoke($this->geocoder));

            // Empty API key
            Config::set('services.opencage.api_key', '');
            $this->assertFalse($method->invoke($this->geocoder));

            // Null API key
            Config::set('services.opencage.api_key');
            $this->assertFalse($method->invoke($this->geocoder));

            // Whitespace-only API key
            Config::set('services.opencage.api_key', '   ');
            $this->assertFalse($method->invoke($this->geocoder));
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }

    #[Test]
    public function geocoder_handles_successful_forward_geocoding()
    {
        $openCageResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->with('Berlin, Germany', Mockery::type('array'))
            ->andReturn($openCageResponse);

        $result = $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(9, $result->confidence);
        $this->assertEquals(52.5200, $result->lat);
        $this->assertEquals(13.4050, $result->lng);
    }

    #[Test]
    public function geocoder_handles_failed_forward_geocoding()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('OpenCage geocoding failed for address', Mockery::type('array'));

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andThrow(new Exception('API Error'));

        $result = $this->geocoder->getCoordinates('Invalid Address', 'de', 10, false);

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(0, $result->confidence);
        $this->assertNull($result->lat);
        $this->assertNull($result->lng);
    }

    #[Test]
    public function geocoder_handles_successful_reverse_geocoding()
    {
        $openCageResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->with('52.52,13.405', Mockery::type('array'))
            ->andReturn($openCageResponse);

        $result = $this->geocoder->getAddress(52.52, 13.405, false);

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(9, $result->confidence);
        $this->assertEquals('Berlin, Germany', $result->formatted_address);
    }

    #[Test]
    public function geocoder_handles_failed_reverse_geocoding()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('OpenCage reverse geocoding failed for coordinates', Mockery::type('array'));

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andThrow(new Exception('API Error'));

        $result = $this->geocoder->getAddress(52.52, 13.405, false);

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(0, $result->confidence);
        $this->assertNull($result->formatted_address);
    }

    #[Test]
    public function geocoder_handles_non_array_api_response_gracefully()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('OpenCage geocoding failed for address', Mockery::type('array'));

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn('invalid response');

        $result = $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);

        $this->assertInstanceOf(GeocodeResult::class, $result);
        $this->assertEquals(0, $result->confidence);
        $this->assertNull($result->lat);
        $this->assertNull($result->lng);
    }

    #[Test]
    public function geocoder_handles_json_encoding_failure_in_cache_key()
    {
        // Create a parameter that cannot be JSON encoded
        $params = ['resource' => fopen('php://memory', 'r')];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to encode parameters to JSON');

        try {
            $reflection = new ReflectionClass($this->geocoder);
            $method = $reflection->getMethod('buildCacheKey');
            $method->invoke($this->geocoder, 'test', $params);
        } catch (ReflectionException $e) {
            $this->fail('Reflection failed: ' . $e->getMessage());
        }
    }
}
