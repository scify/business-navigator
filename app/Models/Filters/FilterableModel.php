<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class FilterableModel extends Model
{
    /**
     * The filter slug used for translations and identification
     * on the front-end. Must be defined in the child class.
     * Usually the name of the model in plural form (e.g. solution_types).
     */
    public static string $filterSlug;

    /**
     * The default column to order by. Can be overridden in the
     * child class or set to null to disable ordering.
     */
    public static ?string $filterOrder = 'order';

    /**
     * Whether to only include records that have related organisations.
     * Can be overridden in child classes.
     */
    public static bool $filterOnlyWithOrganisations = true;

    /* Hides timestamps from serialisation. */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Returns data formatted for filtering, with consistent label and options.
     */
    public static function asFilter(): array
    {

        $query = static::query();

        if (static::$filterOrder) {
            $query->orderBy(static::$filterOrder);
        }

        // Filter to only include records with organisations if configured
        if (static::$filterOnlyWithOrganisations) {
            $query->has('organisations');
        }

        // Fetch options, hiding timestamps
        $options = $query->get()->makeHidden(['created_at', 'updated_at']);

        return [
            'label' => [
                'singular' => __('filters.' . static::$filterSlug . '.singular'),
                'plural' => __('filters.' . static::$filterSlug . '.plural'),
            ],
            'options' => $options,
            'slug' => static::$filterSlug,
        ];
    }

    public static function getSingularSlug(): string
    {
        return Str::singular(static::$filterSlug);
    }
}
