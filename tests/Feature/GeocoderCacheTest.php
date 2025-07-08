<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GeocodingCacheType;
use App\Enums\GeocodingCacheSource;
use App\Models\GeocodingCache;
use App\Services\Geocoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\MockInterface;
use OpenCage\Geocoder\Geocoder as OpenCageGeocoder;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class GeocoderCacheTest extends TestCase
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
    public function geocoder_cache_stores_forward_geocoding()
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
            ->andReturn($openCageResponse);

        $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        // Verify cache entry was created
        $this->assertDatabaseHas('geocoding_cache', [
            'source' => GeocodingCacheSource::OPENCAGE->value,
            'type' => GeocodingCacheType::FORWARD->value,
        ]);

        $cacheEntry = GeocodingCache::first();
        $this->assertNotNull($cacheEntry);
        $this->assertEquals(GeocodingCacheSource::OPENCAGE, $cacheEntry->source);
        $this->assertEquals(GeocodingCacheType::FORWARD, $cacheEntry->type);
        $this->assertEquals($openCageResponse, $cacheEntry->response);
        $this->assertNotNull($cacheEntry->expires_at);
        $this->assertTrue($cacheEntry->expires_at->greaterThan(now()->addDays(89)));
    }

    #[Test]
    public function geocoder_cache_stores_reverse_geocoding()
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
            ->andReturn($openCageResponse);

        $this->geocoder->getAddress(52.5200, 13.4050);

        // Verify cache entry was created with REVERSE type
        $this->assertDatabaseHas('geocoding_cache', [
            'source' => GeocodingCacheSource::OPENCAGE->value,
            'type' => GeocodingCacheType::REVERSE->value,
        ]);

        $cacheEntry = GeocodingCache::first();
        $this->assertEquals(GeocodingCacheType::REVERSE, $cacheEntry->type);
    }

    #[Test]
    public function geocoder_cache_retrieves_on_subsequent_calls()
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

        // First call - should hit API
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($openCageResponse);

        $result1 = $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        // Second call - should use cache (no API call)
        $result2 = $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        $this->assertEquals($result1->confidence, $result2->confidence);
        $this->assertEquals($result1->lat, $result2->lat);
        $this->assertEquals($result1->lng, $result2->lng);
        $this->assertEquals($result1->formatted_address, $result2->formatted_address);

        // Verify only one cache entry exists
        $this->assertEquals(1, GeocodingCache::count());
    }

    #[Test]
    public function geocoder_cache_bypasses_when_disabled()
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

        // Both calls should hit API when cache is disabled
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->twice()
            ->andReturn($openCageResponse);

        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);
        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);

        // Verify cache entry was created/updated (updateOrCreate means same entry updated)
        $this->assertEquals(1, GeocodingCache::count());
    }

    #[Test]
    public function geocoder_cache_ignores_expired_entries()
    {
        // Create an expired cache entry
        $expiredEntry = GeocodingCache::create([
            'key' => 'test-key',
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => [
                'results' => [
                    [
                        'confidence' => 5,
                        'formatted' => 'Old Berlin, Germany',
                        'geometry' => ['lat' => 52.0000, 'lng' => 13.0000],
                        'components' => ['country' => 'Germany', 'country_code' => 'de'],
                    ],
                ],
                'total_results' => 1,
            ],
            'expires_at' => now()->subDay(), // Expired yesterday
        ]);

        $newOpenCageResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'New Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        // Mock the cache key generation to match the expired entry
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $buildCacheKeyMethod = $reflection->getMethod('buildCacheKey');

            $params = [
                'language' => 'en',
                'pretty' => 1,
                'no_record' => 1,
                'limit' => 10,
                'countrycode' => 'de',
            ];

            $cacheKey = $buildCacheKeyMethod->invoke($this->geocoder, 'Berlin, Germany', $params);
        } catch (ReflectionException $e) {
            $this->fail('Failed to generate cache key: ' . $e->getMessage());
        }

        // Update the expired entry to use the correct cache key
        $expiredEntry->update(['key' => $cacheKey]);

        // Should hit API because cache is expired
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($newOpenCageResponse);

        $result = $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        $this->assertEquals(9, $result->confidence);
        $this->assertEquals('New Berlin, Germany', $result->formatted_address);
    }

    #[Test]
    public function geocoder_cache_uses_valid_entries()
    {
        $cachedResponse = [
            'results' => [
                [
                    'confidence' => 8,
                    'formatted' => 'Cached Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        // Create a valid cache entry
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $buildCacheKeyMethod = $reflection->getMethod('buildCacheKey');

            $params = [
                'language' => 'en',
                'pretty' => 1,
                'no_record' => 1,
                'limit' => 10,
                'countrycode' => 'de',
            ];

            $cacheKey = $buildCacheKeyMethod->invoke($this->geocoder, 'Berlin, Germany', $params);
        } catch (ReflectionException $e) {
            $this->fail('Failed to generate cache key: ' . $e->getMessage());
        }

        GeocodingCache::create([
            'key' => $cacheKey,
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => $cachedResponse,
            'expires_at' => now()->addDays(30), // Valid for 30 more days
        ]);

        // Should NOT hit API because cache is valid
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->never();

        $result = $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        $this->assertEquals(8, $result->confidence);
        $this->assertEquals('Cached Berlin, Germany', $result->formatted_address);
    }

    #[Test]
    public function geocoder_cache_updates_existing_entries()
    {
        $oldResponse = [
            'results' => [
                [
                    'confidence' => 7,
                    'formatted' => 'Old Berlin, Germany',
                    'geometry' => ['lat' => 52.0000, 'lng' => 13.0000],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $newResponse = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'New Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        // First call
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($oldResponse);

        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);

        $this->assertEquals(1, GeocodingCache::count());
        $firstEntry = GeocodingCache::first();
        $this->assertEquals($oldResponse, $firstEntry->response);

        // Second call with cache disabled (should update existing entry)
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($newResponse);

        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 10, false);

        $this->assertEquals(1, GeocodingCache::count()); // Still only one entry
        $updatedEntry = GeocodingCache::first();
        $this->assertEquals($newResponse, $updatedEntry->response);
    }

    #[Test]
    public function geocoder_cache_handles_different_parameters_with_different_keys()
    {
        $response1 = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany (limit 1)',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        $response2 = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Berlin, Germany (limit 5)',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        // Two calls with different limits should create separate cache entries
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($response1);

        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->once()
            ->andReturn($response2);

        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 1);
        $this->geocoder->getCoordinates('Berlin, Germany', 'de', 5);

        $this->assertEquals(2, GeocodingCache::count());
    }

    #[Test]
    public function geocoder_cache_handles_scope_for_valid_entries()
    {
        // Create a mix of valid and expired entries
        GeocodingCache::create([
            'key' => 'valid-key',
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => ['results' => [], 'total_results' => 0],
            'expires_at' => now()->addDays(30),
        ]);

        GeocodingCache::create([
            'key' => 'expired-key',
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => ['results' => [], 'total_results' => 0],
            'expires_at' => now()->subDay(),
        ]);

        GeocodingCache::create([
            'key' => 'null-expiry-key',
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => ['results' => [], 'total_results' => 0],
            'expires_at' => null, // Never expires
        ]);

        $validEntries = GeocodingCache::valid()->get();

        $this->assertEquals(2, $validEntries->count());
        $this->assertTrue($validEntries->contains('key', 'valid-key'));
        $this->assertTrue($validEntries->contains('key', 'null-expiry-key'));
        $this->assertFalse($validEntries->contains('key', 'expired-key'));
    }

    #[Test]
    public function geocoder_cache_handles_null_expiry_dates_as_never_expiring()
    {
        $response = [
            'results' => [
                [
                    'confidence' => 9,
                    'formatted' => 'Never Expires Berlin, Germany',
                    'geometry' => ['lat' => 52.5200, 'lng' => 13.4050],
                    'components' => ['country' => 'Germany', 'country_code' => 'de'],
                ],
            ],
            'total_results' => 1,
        ];

        // Create cache entry with null expiry
        try {
            $reflection = new ReflectionClass($this->geocoder);
            $buildCacheKeyMethod = $reflection->getMethod('buildCacheKey');

            $params = [
                'language' => 'en',
                'pretty' => 1,
                'no_record' => 1,
                'limit' => 10,
                'countrycode' => 'de',
            ];

            $cacheKey = $buildCacheKeyMethod->invoke($this->geocoder, 'Berlin, Germany', $params);
        } catch (ReflectionException $e) {
            $this->fail('Failed to generate cache key: ' . $e->getMessage());
        }

        GeocodingCache::create([
            'key' => $cacheKey,
            'source' => GeocodingCacheSource::OPENCAGE,
            'type' => GeocodingCacheType::FORWARD,
            'response' => $response,
            'expires_at' => null, // Never expires
        ]);

        // Should use cache (no API call)
        $this->mockOpenCageGeocoder
            ->shouldReceive('geocode')
            ->never();

        $result = $this->geocoder->getCoordinates('Berlin, Germany', 'de');

        $this->assertEquals(9, $result->confidence);
        $this->assertEquals('Never Expires Berlin, Germany', $result->formatted_address);
    }
}
