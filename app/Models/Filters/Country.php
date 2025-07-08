<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends FilterableModel
{
    public static string $filterSlug = 'countries';

    public static ?string $filterOrder = null;

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Get the organisations associated with the country.
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    /**
     * Retrieve a country by its shortcode (alpha2 code, e.g. "FR").
     */
    public static function findByShortCode(string $shortcode): ?self
    {
        return static::where('alpha2', $shortcode)->first();
    }

    /**
     * Retrieve a country by its name (e.g. "France").
     */
    public static function findByName(string $countryName): ?self
    {
        return static::where('name', $countryName)->first();
    }

    /**
     * Sets the default sort order for Countries as 'by Name'.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('orderByName', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
}
