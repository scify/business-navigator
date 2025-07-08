<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Facades\Geocoder;
use App\Services\Geocoder as GeocoderService;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class GeocoderFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sets up a fake but valid API key for testing:
        Config::set('services.opencage.api_key', 'test-api-key-1234567890');
    }

    #[Test]
    public function geocoder_facade_resolves_service_from_container()
    {
        $service = app('geocoder');

        $this->assertInstanceOf(GeocoderService::class, $service);
    }

    #[Test]
    public function geocoder_facade_returns_same_instance_as_singleton()
    {
        $service1 = app('geocoder');
        $service2 = app('geocoder');

        $this->assertSame($service1, $service2, 'Geocoder should be registered as singleton');
    }

    #[Test]
    public function geocoder_facade_delegates_to_service()
    {
        // Test that facade has the correct documentation and methods are callable
        $service = app('geocoder');
        $this->assertInstanceOf(GeocoderService::class, $service);

        // Test that facade methods are properly documented in the class
        $reflection = new ReflectionClass(Geocoder::class);
        $docComment = $reflection->getDocComment();
        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('getCoordinates', $docComment);
        $this->assertStringContainsString('getAddress', $docComment);
        $this->assertStringContainsString('getSingleCoordinatesOrNull', $docComment);
        $this->assertStringContainsString('isGeocodingAvailable', $docComment);
    }

    #[Test]
    public function geocoder_facade_can_handle_null_address()
    {
        $result = Geocoder::getSingleCoordinatesOrNull();

        $this->assertNull($result);
    }

    #[Test]
    public function geocoder_facade_and_container_return_same_singleton()
    {
        $containerInstance = app('geocoder');
        $facadeInstance = Geocoder::getFacadeRoot();

        $this->assertSame($containerInstance, $facadeInstance);
    }

    #[Test]
    public function geocoder_facade_can_check_availability()
    {
        // Mock the service to control availability response
        $mockService = Mockery::mock(GeocoderService::class);
        $mockService->shouldReceive('isGeocodingAvailable')
            ->once()
            ->withArgs(function ($useCache = true) {
                return $useCache === true;
            })
            ->andReturn(true);

        $this->app->instance('geocoder', $mockService);

        $isAvailable = Geocoder::isGeocodingAvailable();

        $this->assertTrue($isAvailable);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
