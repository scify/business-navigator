<?php

namespace App\Models;

use App\Enums\OrganisationLocationSource;
use App\Enums\OrganisationNumberOfEmployees;
use App\Enums\OrganisationSource;
use App\Enums\OrganisationTurnover;
use App\Models\Filters\Country;
use App\Models\Filters\EnterpriseFunction;
use App\Models\Filters\IndustrySector;
use App\Models\Filters\OfferType;
use App\Models\Filters\OrganisationType;
use App\Models\Filters\SolutionType;
use App\Models\Filters\TechnologyType;
use Database\Factories\OrganisationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Normalizer;

class Organisation extends Model
{
    /** @use HasFactory<OrganisationFactory> */
    use HasFactory;

    public const SHORT_DESCRIPTION_LIMIT = 140;

    protected $hidden = ['is_active', 'created_at', 'deleted_at'];

    protected $fillable = [
        'slug',
        'name',
        'short_description',
        'description',

        'country_id',
        'region',
        'city',
        'postal_code',
        'address_1',
        'address_2',
        'formatted_address',

        'lat',
        'lng',
        'location_confidence',
        'location_source',
        'location_data',

        'website_url',
        'social_bluesky',
        'social_facebook',
        'social_instagram',
        'social_linkedin',
        'social_x',

        'marketplace_slug',

        'founding_year',
        'number_of_employees',
        'turnover',

        'source',
        'is_active',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'location_confidence' => 'integer',
        'location_source' => OrganisationLocationSource::class,
        'location_data' => 'array',
        'founding_year' => 'integer',
        'number_of_employees' => OrganisationNumberOfEmployees::class,
        'turnover' => OrganisationTurnover::class,
        'source' => OrganisationSource::class,
        'is_active' => 'boolean',
    ];

    protected $with = ['logo'];

    /**
     * Get the logo associated with the organisation.
     *
     * @return HasOne<Logo, $this>
     */
    public function logo(): HasOne
    {
        return $this->hasOne(Logo::class);
    }

    /**
     * Get the country associated with the organisation('s HQ).
     *
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The organisation types associated with the organisation.
     *
     * @return BelongsToMany<OrganisationType, $this>
     */
    public function organisationTypes(): BelongsToMany
    {
        return $this->belongsToMany(OrganisationType::class, 'organisation_organisation_type')
            ->withTimestamps();
    }

    /**
     * The industry sectors associated with the organisation.
     *
     * @return BelongsToMany<IndustrySector, $this>
     */
    public function industrySectors(): BelongsToMany
    {
        return $this->belongsToMany(IndustrySector::class, 'organisation_industry_sector')
            ->withTimestamps();
    }

    /**
     * The enterprise functions associated with the organisation.
     *
     * @return BelongsToMany<EnterpriseFunction, $this>
     */
    public function enterpriseFunctions(): BelongsToMany
    {
        return $this->belongsToMany(EnterpriseFunction::class, 'organisation_enterprise_function')
            ->withTimestamps();
    }

    /**
     * The solution types associated with the organisation.
     *
     * @return BelongsToMany<SolutionType, $this>
     */
    public function solutionTypes(): BelongsToMany
    {
        return $this->belongsToMany(SolutionType::class, 'organisation_solution_type')
            ->withTimestamps();
    }

    /**
     * The technology types associated with the organisation.
     *
     * @return BelongsToMany<TechnologyType, $this>
     */
    public function technologyTypes(): BelongsToMany
    {
        return $this->belongsToMany(TechnologyType::class, 'organisation_technology_type')
            ->withTimestamps();
    }

    /**
     * The offer types associated with the organisation.
     *
     * @return BelongsToMany<OfferType, $this>
     */
    public function offerTypes(): BelongsToMany
    {
        return $this->belongsToMany(OfferType::class, 'organisation_offer_type')
            ->withTimestamps();
    }

    /**
     * Resolve the Organisation for a given route key (either an id or a slug).
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        // Will must likely be a string, but just to be safe:
        if (! $value) {
            return null;

        }

        // First, tries to find a model by its slug. This correctly handles
        // cases where a slug might be purely numeric:
        $model = $this->where('slug', $value)->first();

        if ($model) {
            return $model;

        }

        // If no model was found by slug and the value is numeric, then try to
        // find it by its ID and flag it for a redirect:
        if (is_numeric($value)) {
            $modelById = $this->where('id', $value)->first();

            if ($modelById) {
                request()->attributes->set('organisation_was_found_by_id', true);

                return $modelById;
            }
        }

        // If we've reached this point, no model was found by slug or a valid
        // ID. Returning null will cause Laravel to trigger a 404 Not Found
        // response, which is exactly what is needed.
        return null;
    }

    /**
     * Generate a unique match hash based on name and country.
     *
     * Used internally by model events and in tests for verification.
     * Public because it's needed for testing hash generation logic.
     */
    public function generateMatchHash(): string
    {
        return static::generateMatchHashFor($this->name, $this->country_id);
    }

    /**
     * Generate a match hash for given name and country ID.
     *
     * Used by import logic to find existing organisations and by model events
     * to generate/update hashes. Public static for import compatibility.
     */
    public static function generateMatchHashFor(string $name, ?int $countryId): string
    {
        // Attempt normalization with fallback
        $normalized = normalizer_normalize($name, Normalizer::FORM_C);
        if ($normalized === false) {
            // Log the failure for debugging but don't throw exception
            Log::warning('Failed to normalize organisation name during hash generation', [
                'name' => $name,
                'country_id' => $countryId,
                'encoding' => mb_detect_encoding($name),
                'is_valid_utf8' => mb_check_encoding($name, 'UTF-8'),
                'length' => mb_strlen($name),
            ]);

            // Fallback: try to clean the string first
            $cleaned = mb_scrub($name, 'UTF-8');
            if ($cleaned) {
                $normalized = normalizer_normalize($cleaned, Normalizer::FORM_C);
                if ($normalized === false) {
                    // Second normalization failed, uses cleaned string:
                    $normalized = $cleaned;
                }
            } else {
                Log::error(
                    'Critical: Unable to clean organisation name during hash generation', [
                        'name' => $name,
                        'country_id' => $countryId,
                    ]
                );
                // Uses original name as last resort:
                $normalized = $name;
            }
        }

        // Always ensure consistent lowercasing regardless of normalization success
        $normalizedName = mb_strtolower((string) $normalized, 'UTF-8');
        // Finally generates the hashData for the hash!
        $hashData = $normalizedName . '|' . ($countryId ?? 'unknown');

        return hash('sha256', $hashData);
    }

    /**
     * Generate a unique slug, handling collisions with country code and numeric suffixes.
     *
     * Used by model boot events during creation. Public because it may be needed
     * for manual slug regeneration in admin interfaces.
     */
    public function generateUniqueSlug(): string
    {
        $baseSlug = Str::slug($this->name);
        $countryCode = $this->country?->alpha2 ? strtolower($this->country->alpha2) : null;

        // Try base slug first
        if (! static::where('slug', $baseSlug)->where('id', '!=', $this->id ?? 0)->exists()) {
            return $baseSlug;
        }

        // If collision and there is a country, tries with country code:
        if ($countryCode) {
            $slugWithCountry = $baseSlug . '-' . $countryCode;

            if (! static::where('slug', $slugWithCountry)->where('id', '!=', $this->id ?? 0)->exists()) {
                return $slugWithCountry;
            }

            // If still collision, adds counter with country:
            $counter = 2;
            while (static::where('slug', $slugWithCountry . '-' . $counter)->where('id', '!=', $this->id ?? 0)->exists()) {
                $counter++;
            }

            return $slugWithCountry . '-' . $counter;
        }

        // Fallbacks to simple counter if no country:
        $counter = 2;
        while (static::where('slug', $baseSlug . '-' . $counter)->where('id', '!=', $this->id ?? 0)->exists()) {
            $counter++;
        }

        return $baseSlug . '-' . $counter;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Organisation $organisation) {
            $organisation->match_hash = static::generateMatchHashFor($organisation->name, $organisation->country_id);
            $organisation->slug = $organisation->generateUniqueSlug();
        });

        static::updating(function (Organisation $organisation) {
            if ($organisation->isDirty(['name', 'country_id'])) {
                $organisation->match_hash = static::generateMatchHashFor($organisation->name, $organisation->country_id);
            }
        });
    }
}
