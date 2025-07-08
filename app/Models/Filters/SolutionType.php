<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SolutionType extends FilterableModel
{
    public static string $filterSlug = 'solution_types';

    /**
     * The organisations that belong to the solution type.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_solution_type')
            ->withTimestamps();
    }
}
