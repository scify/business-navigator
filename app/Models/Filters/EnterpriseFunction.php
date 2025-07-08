<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EnterpriseFunction extends FilterableModel
{
    public static string $filterSlug = 'enterprise_functions';

    /**
     * The organisations that belong to the enterprise function.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_enterprise_function')
            ->withTimestamps();
    }
}
