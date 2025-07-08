<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::daily()
    ->timezone('Europe/Berlin')
    ->group(function () {
        Schedule::command('app:clear-expired-geocoding-cache');
    });
