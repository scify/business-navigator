<?php

namespace App\Http\Controllers;

use App\Helpers\TextFormatter;
use App\Models\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OrganisationsController extends Controller
{
    /**
     * Display the specified organisation.
     */
    public function show(Organisation $organisation): Response|SymfonyResponse
    {
        if (request()->attributes->get('organisation_was_found_by_id') && $organisation->slug) {
            return Inertia::location(route('organisations.show', ['organisation' => $organisation->slug]));

        }

        // Load the organisation with relations.
        $organisation->load([
            'country',
            'organisationTypes',
            'industrySectors',
            'enterpriseFunctions',
            'solutionTypes',
            'technologyTypes',
            'offerTypes',
        ]);

        // Renders Description as Markdown using the TextFormatter (includes HTML Purifier):
        $organisation->description = TextFormatter::markdown($organisation->description);

        return Inertia::render('Organisations/Show/Page', [
            'organisation' => [
                ...$organisation->toArray(),
                'number_of_employees' => $organisation->number_of_employees?->getRange(),
                'turnover' => $organisation->turnover?->getRangeProper(),
                'turnover_short' => $organisation->turnover?->getRangeProperShort(),
            ],
        ]);
    }
}
