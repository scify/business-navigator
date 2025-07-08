<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class IndustrySector extends FilterableModel
{
    public static string $filterSlug = 'industry_sectors';

    /**
     * The organisations that belong to the industry.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_industry_sector')
            ->withTimestamps();
    }
}
