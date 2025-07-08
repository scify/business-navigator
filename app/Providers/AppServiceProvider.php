<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configurePaginator();
        $this->configureUrl();
        $this->configureVite();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        /*
        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );
        */
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();
        // Model::unguard();
    }

    /**
     * Configure the application's URL.
     */
    private function configureUrl(): void
    {
        URL::forceScheme('https');
    }

    /**
     * Configure the application's Pagination output.
     *
     * @link https://laravel.com/docs/11.x/pagination#using-bootstrap
     */
    private function configurePaginator(): void
    {
        Paginator::useBootstrapFive();
    }

    private function configureVite(): void
    {
        Vite::usePrefetchStrategy('aggressive');
        // Vite::prefetch(concurrency: 3);
    }
}
