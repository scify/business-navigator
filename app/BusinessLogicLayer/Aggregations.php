<?php

namespace App\BusinessLogicLayer;

use App\Models\Filters\Country;
use App\Models\Filters\IndustrySector;
use App\Models\Organisation;
use Illuminate\Support\Facades\Cache;

/**
 * Handles aggregation operations for dashboard statistics and counts.
 *
 * Provides cached statistics about organisations, industries, and countries
 * for use in dashboards and overview pages.
 */
class Aggregations
{
    /**
     * Get cached aggregated counts for dashboard display.
     *
     * Returns both total counts and counts of entities that have associated organisations.
     * Results are cached for 5 minutes to improve performance.
     *
     * @return array{total: array, withOrganisations: array} Aggregated count data
     */
    public function count(): array
    {
        return Cache::remember('ilt_cache_aggregations', now()->addMinutes(5), function () {
            return [
                'total' => $this->countTotal(),
                'withOrganisations' => $this->countWithOrganisations(),
            ];
        });
    }

    /**
     * Get total counts of all entities.
     *
     * @return array{organisations: int, industries: int, countries: int} Total counts
     */
    public function countTotal(): array
    {
        return [
            'organisations' => Organisation::query()->count(),
            'industries' => IndustrySector::query()->count(),
            'countries' => Country::query()->count(),
        ];

    }

    /**
     * Get counts of entities that have associated organisations.
     *
     * For industries and countries, only counts those that have at least one
     * associated organisation.
     *
     * @return array{organisations: int, industries: int, countries: int} Filtered counts
     */
    public function countWithOrganisations(): array
    {
        return [
            'organisations' => Organisation::query()->count(),
            'industries' => IndustrySector::has('organisations')->count(),
            'countries' => Country::has('organisations')->count(),
        ];

    }
}
