<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Io238\ISOCountries\Models\Country as IsoCountry;

return new class extends Migration
{
    /**
     * List of the Countries supported by this app using their ISO 3166-1
     * alpha-2 country code (see link #1). Please note that this initial list
     * includes all the countries mentioned in the official EU documents (see
     * link #2). The countries will be inserted in alphabetical order based on
     * their official English name.
     *
     * @link https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
     * @link https://european-union.europa.eu/easy-read_en
     */
    private const COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
        'SI', 'ES', 'SE',
    ];

    /**
     * Populates the database with the initial data for Countries.
     *
     * All The data are extracted from `laravel-iso-countries`.
     *
     * @internal As the dependency was not updated for Laravel 11 a fork is used
     * for seeding the database with accurate data and to provide functions
     * which are crucial for l10n & i18n.
     *
     * @link https://github.com/io238/laravel-iso-countries
     * @link https://github.com/io238/laravel-iso-countries/pull/10
     * @link https://packagist.org/packages/arthurydalgo/laravel-iso-countries
     */
    public function up(): void
    {
        $entries = [];

        foreach (self::COUNTRY_CODES as $countryCode) {
            $country = IsoCountry::find($countryCode, [
                // Left out:
                // - (str) 'native_name',
                // - (str) 'capital',
                // - (int) 'population'.
                'id', 'name', 'alpha2', 'alpha3', 'demonym', 'lat', 'lon',
            ]);

            if ($country) {
                $entries[] = [
                    'slug' => Str::slug($country->name),
                    // Short name in English.
                    'name' => $country->name,
                    // ISO 3166-1 alpha-2 country code.
                    'alpha2' => $country->id,
                    // ISO 3166-1 alpha-3 country code.
                    'alpha3' => $country->getAttribute('alpha3'),
                    // How people of this country are called in English.
                    'demonym' => $country->demonym ?? null,
                    'lat' => $country->lat ?? null,
                    'lng' => $country->lon ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Sort countries alphabetically by name before inserting
        $sortedEntries = collect($entries)->sortBy('name')->values()->all();

        // Insert all countries at once.
        DB::table('countries')->insert($sortedEntries);
    }

    /**
     * Reverse the population of database with Countries data.
     *
     * Intentionally does nothing.
     *
     * @internal This migration rollback is intentionally disabled to prevent
     * data integrity issues. Rolling back would either fail due to foreign key
     * constraints (organisations reference countries with restrictOnDelete) or
     * orphan organisation records if constraints were bypassed. Countries are
     * foundational reference data that should persist. Use migrate:fresh to
     * reset all tables if needed.
     */
    public function down(): void
    {
        // Intentionally left empty - see @internal comment above
    }
};
