<?php

namespace App\Providers;

use App\Services\Geocoder;
use Illuminate\Support\ServiceProvider;

class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton('geocoder', function () {
            return new Geocoder;
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        //
    }
}
