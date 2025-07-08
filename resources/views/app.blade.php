<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff">
        <meta name="color-scheme" content="light only">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <x-laravel-cookie-guard-scripts></x-laravel-cookie-guard-scripts>
        <!-- Scripts -->
        @routes
        @vite(['resources/scripts/app.ts'])
        @inertiaHead
    </head>
    <body>
        @inertia
        <x-laravel-cookie-guard></x-laravel-cookie-guard>
    </body>
</html>
