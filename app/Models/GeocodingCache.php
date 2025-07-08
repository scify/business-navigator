<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeocodingCacheSource;
use App\Enums\GeocodingCacheType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder<static> valid()
 */
class GeocodingCache extends Model
{
    protected $table = 'geocoding_cache';

    protected $fillable = [
        'key',
        'source',
        'type',
        'response',
        'expires_at',
    ];

    protected $casts = [
        'source' => GeocodingCacheSource::class,
        'type' => GeocodingCacheType::class,
        'response' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Scope to filter valid cache entries (non-expired).
     *
     * @param  Builder<static>  $query
     *
     * @return Builder<static>
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
