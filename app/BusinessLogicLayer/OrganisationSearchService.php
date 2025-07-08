<?php

namespace App\BusinessLogicLayer;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;

class OrganisationSearchService
{
    /**
     * Generate a search query based on provided slugs.
     */
    public function generateSearchBySlugQuery(
        ?string $organisationTypeSlug = null,
        ?string $industrySectorSlug = null,
        ?string $enterpriseFunctionSlug = null,
        ?string $solutionTypeSlug = null,
        ?string $technologyTypeSlug = null,
        ?string $offerTypeSlug = null,
        ?string $countrySlug = null,
    ): Builder {

        // Initiates the Builder Query:
        $query = Organisation::query();

        // Applies dynamic filters:
        $relations = [
            'organisationTypes' => $organisationTypeSlug,
            'industrySectors' => $industrySectorSlug,
            'enterpriseFunctions' => $enterpriseFunctionSlug,
            'solutionTypes' => $solutionTypeSlug,
            'technologyTypes' => $technologyTypeSlug,
            'offerTypes' => $offerTypeSlug,
            'country' => $countrySlug,
        ];

        foreach ($relations as $relation => $slug) {
            if ($slug) {
                $query->whereHas($relation, function (Builder $q) use ($slug) {
                    $q->where('slug', $slug);
                });
            }
        }

        return $query;
    }
}
