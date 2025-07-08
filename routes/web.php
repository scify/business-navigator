<?php

/** @noinspection SpellCheckingInspection */

use App\Facades\Geocoder;
use App\Http\Controllers\Auth\UploadFileController;
use App\Http\Controllers\Dashboard\OrganisationController as DashboardOrganisationController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OrganisationsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes:
Route::get('/', HomeController::class)->name('index');
Route::get('/explore', ExploreController::class)->name('explore');
Route::get('/explore/org/{organisation}', [OrganisationsController::class, 'show'])
    ->name('organisations.show');
Route::get('/map', MapController::class)->name('map');

// Authenticated routes:
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index/Page');
    })->middleware('verified')->name('dashboard');

    // Dashboard routes:
    Route::prefix('dashboard')->group(function () {
        Route::get('/organisations', [DashboardOrganisationController::class, 'index'])->name('dashboard.organisations.index');
        Route::get('/organisations/{organisation}/edit', [DashboardOrganisationController::class, 'edit'])->name('dashboard.organisations.edit');
        Route::patch('/organisations/{organisation}', [DashboardOrganisationController::class, 'update'])->name('dashboard.organisations.update');
    });

    // Profile routes:
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // CSV Upload routes:
    Route::prefix('csv')->group(function () {
        Route::get('/', [UploadFileController::class, 'index'])->name('csv.index');
        Route::post('/csv/upload', [UploadFileController::class, 'upload'])->name('csv.upload');
        Route::get('/csv/download', [UploadFileController::class, 'download'])->name('csv.download');
        Route::delete('/csv/delete', [UploadFileController::class, 'delete'])->name('csv.delete');
    });

});

// Testing routes (temporary):
Route::prefix('test')->group(function () {

    // Geocoding test.
    Route::get('/geocode', function () {
        $address = request('address');
        $alpha2 = request('country');

        $address = is_string($address) ? $address : 'Neapoleos 27, Paraskevi';
        $alpha2 = is_string($alpha2) ? $alpha2 : 'GR';
        $useCache = filter_var(request('useCache', true), FILTER_VALIDATE_BOOLEAN);

        $result = Geocoder::getCoordinates($address, $alpha2, 1, $useCache);

        // Gets all supported countries for the dropdown:
        $countries = \App\Models\Filters\Country::select('alpha2', 'name')->orderBy('name')->get();

        return inertia('Test/Geocode', [
            'address' => $address,
            'country' => $alpha2,
            'useCache' => $useCache,
            'result' => $result,
            'countries' => $countries,
        ]);
    })->middleware('throttle:50,1')
        ->name('test.geocode');

    // Fonts & widgets test.
    Route::get('/fonts', function () {
        return inertia('Test/Fonts');
    })->name('test.fonts');

    // The default page for Inertia Laravel.
    Route::get('/laravel', function () {
        return Inertia::render('Test/Laravel', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

});

// Include Auth Routes
require __DIR__ . '/auth.php';
