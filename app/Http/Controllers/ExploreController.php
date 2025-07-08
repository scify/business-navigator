<?php

namespace App\Http\Controllers;

use App\BusinessLogicLayer\FilterService;
use App\BusinessLogicLayer\OrganisationSearchService;
use App\Http\Requests\FiltersRequest;
use App\Models\Filters\FilterableModel;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    /**
     * The Organisation Search Service.
     */
    protected OrganisationSearchService $organisationSearchService;

    /**
     * The Organisation Filter Service.
     */
    protected FilterService $filterService;

    public function __construct(
        OrganisationSearchService $organisationSearchService,
        FilterService $filterService)
    {
        $this->organisationSearchService = $organisationSearchService;
        $this->filterService = $filterService;
    }

    /**
     * The ILT Explore page with default selection for filters and results.
     *
     * This page should help users Explore the data. It provides the initial
     * state: All available filters and results, with no filters applied on the
     * actual results. Therefore, Explore does not handle $requests, it just
     * provides the initial view (for SSR and therefore SEO). Further handling
     * should be done via the Vue.js page with direct calls on the Companies
     * API endpoint.
     *
     * @param  FiltersRequest  $request  The HTTP Filters Request.
     *
     * @return Response The Inertia Response.
     */
    public function __invoke(FiltersRequest $request): Response
    {
        // Gets *all* the available filters with their options:
        $allFilters = $this->filterService->all();

        // Gets *validated requested* filters, if any:
        $requestedFilters = $request->validated();
        Log::debug('Explore Controller Request', $requestedFilters);

        // Collects values for requested filters using FilterService:
        $filterValues = [];
        foreach (FilterService::$filters as $filterableModel) {
            /** @var class-string<FilterableModel> $filterableModel */
            $filterSlug = $filterableModel::getSingularSlug();
            $filterValues[] = $requestedFilters[$filterSlug] ?? null;
        }

        // Passes all filters & values to SearchService, building the query:
        $organisationsQuery = $this->organisationSearchService
            ->generateSearchBySlugQuery(...$filterValues);

        // Gets the matching organisations with proper meta & pagination for Vue.js:
        // @link https://codecourse.com/articles/pagination-with-inertia-and-vue
        $perPage = 20; // Results per page (preferably dividable by 4, i.e. 20).
        $results = $organisationsQuery->with('country')
            ->orderBy('name')
            ->paginate($perPage)
            ->onEachSide(3) // this is the default but meh.
            ->appends($requestedFilters); // Pagination w/ filters

        // Returns the view model of the page, with all required data:
        $viewModel = [
            'filters' => fn () => $allFilters->toArray(),
            'results' => fn () => $results,
            'selectedFilters' => $requestedFilters,
        ];

        return Inertia::render('Explore/Index/Page', $viewModel);

    }
}
