<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Display a clustered map of all organisations.
     */
    public function __invoke(): Response
    {
        $organisations = Organisation::with(['country:id,name', 'organisationTypes:id,name'])
            // Minimum req: Array<{ lat: number, lng: number, id: string, [prop: string]: unknown }
            ->select('id', 'name', 'formatted_address', 'country_id', 'lat', 'lng', 'slug')
            ->orderBy('name')
            ->get();

        $organisationsGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $organisations->filter(
                fn ($org) => ! is_null($org->lat) && ! is_null($org->lng)
            )->map(function ($organisation) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$organisation->lng, $organisation->lat],
                    ],
                    'properties' => [
                        'id' => $organisation->id,
                        'slug' => $organisation->slug,
                        'name' => $organisation->name,
                        'address' => $organisation->formatted_address ?? null,
                        'country' => $organisation->country->name ?? null,
                        'organisation_types' => $organisation->organisationTypes
                            ->pluck('name')->toArray(),
                    ],
                ];
            })->values()->toArray(),
        ];

        return Inertia::render('Map/Index/Page', [
            'organisations' => $organisations,
            'organisationsGeoJson' => $organisationsGeoJson,
        ]);
    }
}
