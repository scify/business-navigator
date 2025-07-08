<?php

namespace App\Models\Filters;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OfferType extends FilterableModel
{
    public static string $filterSlug = 'offer_types';

    /**
     * The organisations that belong to the offer type.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_offer_type')
            ->withTimestamps();
    }
}
