<?php

namespace App\BusinessLogicLayer;

use App\Models\Filters\Country;
use App\Models\Filters\EnterpriseFunction;
use App\Models\Filters\FilterableModel;
use App\Models\Filters\IndustrySector;
use App\Models\Filters\OfferType;
use App\Models\Filters\OrganisationType;
use App\Models\Filters\SolutionType;
use App\Models\Filters\TechnologyType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FilterService
{
    /**
     * List of filterable model class names.
     */
    public static array $filters = [
        OrganisationType::class,
        IndustrySector::class,
        EnterpriseFunction::class,
        SolutionType::class,
        TechnologyType::class,
        OfferType::class,
        Country::class,
    ];

    /**
     * Returns a list of all "filters" which can be applied to Organisations.
     *
     * Returns a collection of all filterable models formatted as filters,
     * keyed by their slug for easy access. Results are cached for 5 minutes
     * to improve performance since filter options don't change frequently.
     *
     * @return Collection<string, array> Collection of filters keyed by slug
     */
    public function all(): Collection
    {
        return Cache::remember('filter_service_all_filters', now()->addMinutes(5), function () {
            $filters = [];

            foreach (self::$filters as $filterableModel) {
                /** @var class-string<FilterableModel> $filterableModel */
                $filters[] = $filterableModel::asFilter();
            }

            return collect($filters)->keyBy('slug');
        });

    }
}
