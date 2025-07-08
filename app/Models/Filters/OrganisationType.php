<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrganisationType extends FilterableModel
{
    public static string $filterSlug = 'organisation_types';

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The organisations that belong to the organisation type.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_organisation_type')
            ->withTimestamps();
    }
}
