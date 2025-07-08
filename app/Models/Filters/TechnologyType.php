<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TechnologyType extends FilterableModel
{
    public static string $filterSlug = 'technology_types';

    /**
     * The organisations that belong to the technology type.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_technology_type')
            ->withTimestamps();
    }
}
