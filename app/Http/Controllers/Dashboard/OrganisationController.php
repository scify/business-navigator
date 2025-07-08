<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Models\Filters\SolutionType;
use App\Models\Filters\TechnologyType;
use App\Models\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OrganisationController extends Controller
{
    /**
     * Display a listing of the Organisation(s) resource.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard/Organisations/Index/Page', [
            'organisations' => Organisation::with(['country'])->get(),
        ]);
    }

    /**
     * Show the form for editing the specified Organisation resource.
     */
    public function edit(Organisation $organisation): Response
    {
        return Inertia::render('Dashboard/Organisations/Edit/Page', [
            'organisation' => $organisation->load(['solutionTypes', 'technologyTypes']),
            'shortDescriptionLimit' => Organisation::SHORT_DESCRIPTION_LIMIT,
            'solutionTypes' => SolutionType::all(),
            'technologyTypes' => TechnologyType::all(),
        ]);
    }

    /**
     * Update the specified Organisation resource in storage.
     */
    public function update(UpdateOrganisationRequest $request, Organisation $organisation): RedirectResponse
    {
        $data = $request->validated();

        // Prevents accidental slug changes (even though this should never happen):
        unset($data['slug']);

        // Extracts relationship data from the request & removes them from the main array:
        $relationshipData = [
            'solutionTypes' => $data['solution_types'] ?? [],
            'technologyTypes' => $data['technology_types'] ?? [],
        ];

        // Removes relationship keys from the main array:
        unset($data['solution_types'], $data['technology_types']);

        try {
            DB::transaction(function () use ($organisation, $data, $relationshipData) {
                $organisation->update($data);

                foreach ($relationshipData as $relationshipName => $items) {
                    $organisation->{$relationshipName}()->sync($items);
                }
            });
        } catch (Throwable $e) {
            Log::error('Failed to update organisation', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('flash', [
                'message' => 'Error updating organisation: ' . $e->getMessage(),
                'id' => uniqid(),
                'type' => 'error',
            ]);
        }

        return redirect()->back()->with('flash', [
            'message' => 'Organisation updated successfully.',
            'id' => uniqid(),
            'type' => 'success',
        ]);
    }
}
