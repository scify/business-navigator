<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\OrganisationNumberOfEmployees;
use App\Enums\OrganisationSource;
use App\Enums\OrganisationTurnover;
use App\Helpers\SocialHelper;
use App\Imports\Helpers\ImportLoggerHelper;
use App\Imports\Helpers\ImportValidatorHelper;
use App\Imports\Helpers\OrganisationsImportLocationHelper;
use App\Imports\Helpers\OrganisationsImportLogoHelper;
use App\Models\Filters\EnterpriseFunction;
use App\Models\Filters\IndustrySector;
use App\Models\Filters\OfferType;
use App\Models\Filters\OrganisationType;
use App\Models\Filters\SolutionType;
use App\Models\Filters\TechnologyType;
use App\Models\Organisation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Class OrganisationsSheetImport
 * Handles import logic for a specific sheet containing organisation data.
 */
class OrganisationsSheetImport implements ToCollection, WithHeadingRow
{
    /**
     * Map of columns (i.e. row keys) to Organisation's model fields/attributes.
     *
     * The format is as follows: 'column_name' => 'organisation_attribute'. For
     * example, if the values on the column `linkedin` should be assigned to an
     * Organisation's `social_linkedin` field, add the following to the array:
     * `'linkedin' => 'social_linkedin'` All named columns are **required**, or
     * else, the entire sheet will simply be ignored.
     *
     * @see self::ASSOCIATIONS_MAP for columns which contain "associations".
     *
     * @version 2.4 2025-07-04
     *
     * @author codeuxius
     *
     * @var array<string, string>
     */
    private const COLUMN_TO_ATTRIBUTE_MAP = [
        // slug(sheet_column_name) => table_column_name
        'name' => 'name',
        'short_description' => 'short_description',
        'full_description' => 'description',
        'founding_year' => 'founding_year',
        'country' => 'country',
        'region' => 'region',
        'city' => 'city',
        'postal_code' => 'postal_code',
        'address_line_1' => 'address_1',
        'address_line_2' => 'address_2',
        'website' => 'website_url',
        'linkedin' => 'social_linkedin',
        'x' => 'social_x',
        'facebook' => 'social_facebook',
        'instagram' => 'social_instagram',
        'bluesky' => 'social_bluesky',
        'marketplace_vendor_slug' => 'marketplace_slug',
        'number_of_employees' => 'number_of_employees',
        'turnover' => 'turnover',
    ];

    /**
     * Associations: Map values of specific columns to associated models.
     *
     * Configurable array, mapping one or more columns to a specific model, e.g.
     * columns industry_sector_1 and industry_sector_2 to IndustrySector::class.
     * All the columns described are *required* , or else the entire sheet will
     * be ignored.
     *
     * @internal The existence of these columns is not validated yet on code and
     * therefore, an attempted import from a sheet without these fields might
     * fail catastrophically.
     *
     * @version 2.4 2025-07-04
     *
     * @author codeuxius
     *
     * @var array<string, array{columns: array<string>, model: class-string, relation: string}>
     */
    private const ASSOCIATIONS_MAP = [
        'organisation_types' => [
            'columns' => ['organisation_type'],
            'model' => OrganisationType::class,
            'relation' => 'organisationTypes',
        ],
        'industry_sectors' => [
            'columns' => ['industry_sector_1', 'industry_sector_2'],
            'model' => IndustrySector::class,
            'relation' => 'industrySectors',
        ],
        'enterprise_functions' => [
            'columns' => ['enterprise_function_1', 'enterprise_function_2'],
            'model' => EnterpriseFunction::class,
            'relation' => 'enterpriseFunctions',
        ],
        'ai_solutions' => [
            'columns' => ['ai_solution_1', 'ai_solution_2'],
            'model' => SolutionType::class,
            'relation' => 'solutionTypes',
        ],
        'technology_types' => [
            'columns' => ['technology_type_1', 'technology_type_2'],
            'model' => TechnologyType::class,
            'relation' => 'technologyTypes',
        ],
        'offer_types' => [
            'columns' => ['offer_type_1', 'offer_type_2'],
            'model' => OfferType::class,
            'relation' => 'offerTypes',
        ],
    ];

    /**
     * Required fields for an Organisation.
     *
     * Configurable array with the values on each row that should be filled, in
     * order to allow its import. For example, each row of the sheet should have
     * at least the following values filled for an Organisations, or else the
     * row will be skipped on import: `['name', 'country', 'website_url']`.
     *
     * @var array<string>
     */
    private const REQUIRED_FIELDS = [
        'name',
        'country',
        'website_url',
    ];

    /**
     * The limit in characters for the 'short' description of an Organisation.
     *
     * Configurable. Defaults to 140 according to given specs but can be up to
     * 255 characters. If the short description is longer, it will be trimmed on
     * import.
     *
     * @var int
     */
    private const SHORT_DESCRIPTION_LIMIT = 140;

    /**
     * Cache for model lookups to reduce database queries.
     *
     * @var array<string, int|null>
     */
    private array $modelLookupCache = [];

    /**
     * The full path of the imported Excel file.
     */
    protected string $filePath;

    /**
     * Logger helper for handling statistics and logging.
     */
    protected ImportLoggerHelper $logger;

    /**
     * Create a new sheet import instance.
     *
     * @param  string  $filePath  The full path of the Excel file being imported.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->logger = new ImportLoggerHelper('Organisations Import', $filePath, 2);
    }

    /**
     * Processes the collection of imported data.
     *
     * @param  Collection<int, Collection<string, mixed>>  $collection  The collection of imported rows.
     */
    public function collection(Collection $collection): void
    {
        // Logs import start with file details:
        $this->logger->logImportStart([
            'file' => basename($this->filePath),
            'rows' => $collection->count(),
        ]);

        // Aborts if the Collection is empty!
        if ($collection->isEmpty()) {
            $error = 'Sheet is empty. No data to import.';
            $this->logger->recordFatalError($error);

            return; // Stops entire import. Fatality!

        }

        // Aborts if the the structure of the sheet is invalid!
        $firstRow = $collection->first();
        $requiredColumns = array_keys(self::COLUMN_TO_ATTRIBUTE_MAP);
        if (! ImportValidatorHelper::validateRequiredColumns($firstRow, $requiredColumns)) {
            $error = 'Sheet does not have the required columns. Expected: ' . implode(', ', $requiredColumns);
            $this->logger->recordFatalError($error);

            return; // Stops entire import. Fatality!

        }

        // Aborts if Geocoding API is not available!
        if (! OrganisationsImportLocationHelper::isGeocodingAvailable()) {
            $error = 'Geocoding API service is not available';
            $this->logger->recordFatalError($error);

            return; // Stops entire import. Fatality!

        }

        // Process each row to import each organisation:
        foreach ($collection as $index => $row) {
            $this->processOrganisation($row, $index);
        }

        // 🎉 Logs import completion:
        $this->logger->logImportCompletion([
            'file' => basename($this->filePath),
            'rows' => $collection->count(),
        ]);

    }

    /**
     * Process a single organisation row.
     *
     * @param  Collection<string, mixed>  $row  The row data
     * @param  int  $index  The row index
     */
    private function processOrganisation(Collection $row, int $index): void
    {
        $this->logger->recordProcessed($index);

        // Step 1: Validates row data (critical - must succeed)
        try {
            // Skips row if it is completely empty:
            if (ImportValidatorHelper::isRowEmpty($row)) {
                $this->logger->recordSkip($index, 'Row is empty');

                return;
            }

            // Errors in current row if required field values are missing (i.e. empty):
            if (! ImportValidatorHelper::validateRequiredFields(
                $row,
                self::REQUIRED_FIELDS,
                [$this, 'getTrimmedValue']
            )) {
                $error = 'Missing required field values. Expected: ' . implode(', ', self::REQUIRED_FIELDS);
                $this->logger->recordError($index, $error);

                return;
            }

            // Resolves Organisation's name:
            $name = $this->getTrimmedValue($row, 'name');
            // ... or skips Organisation if name is not existent (sanity!)
            if ($name === null) {
                $error = "Trimmed value for 'name' is... null.";
                $this->logger->recordError($index, $error);

                return;
            }
            // ... or skips Organisation if name is too long:
            if (! ImportValidatorHelper::validateLength($name)) {
                $error = "Too long name for '$name' - " . mb_strlen($name) . ' characters (max: 255)';
                $this->logger->recordError($index, $error);

                return;
            }

            // Resolves Organisation's country (or skips Organisation):
            $location = OrganisationsImportLocationHelper::resolveLocationData(
                $row,
                [$this, 'getTrimmedValue'],
                ['address_1', 'city', 'region'],
                $this->logger,
                $index
            );
            // ... or skips Organisation if Country is not supported:
            if (! $location) {
                $error = "Invalid, unsupported or unresolved country for '$name'.";
                $this->logger->recordError($index, $error);

                return;
            }

            // Validates Organisation's website URL (if any):
            $websiteValidation = ImportValidatorHelper::validateWebsiteUrl(
                $this->getTrimmedValue($row, 'website_url')
            );
            if (! $websiteValidation['isValid']) {
                $this->logger->recordWarning($index, "Invalid website URL for '$name'");
            }
            $websiteUrl = $websiteValidation['cleanUrl'];

            // Validates Organisation's founding year (if any):
            $yearValidation = ImportValidatorHelper::validateFoundingYear(
                $this->getTrimmedValue($row, 'founding_year')
            );
            if (! $yearValidation['isValid']) {
                $this->logger->recordWarning($index, "Invalid founding year for '$name'");
            }
            $foundingYear = $yearValidation['cleanYear'];

            // Builds the Organisation Data:
            $organisationData = [
                'name' => $name,
                'short_description' => Str::limit(
                    $this->getTrimmedValue($row, 'short_description') ?? '',
                    self::SHORT_DESCRIPTION_LIMIT,
                ),
                'description' => $this->getTrimmedValue($row, 'description'),
                'country_id' => $location->country?->id,
                'region' => $this->getTrimmedValue($row, 'region'),
                'city' => $this->getTrimmedValue($row, 'city'),
                'postal_code' => $location->postalCode,
                'address_1' => $this->getTrimmedValue($row, 'address_1'),
                'address_2' => $this->getTrimmedValue($row, 'address_2'),
                'formatted_address' => $location->formattedAddress,
                'lat' => $location->lat,
                'lng' => $location->lng,
                'location_confidence' => $location->confidence,
                'location_source' => $location->source,
                'location_data' => $location->response,
                'website_url' => $websiteUrl,
                'social_bluesky' => SocialHelper::extractBlueskyHandleFromUrl(
                    $this->getTrimmedValue($row, 'social_bluesky')
                ),
                'social_facebook' => SocialHelper::extractFacebookPathFromUrl(
                    $this->getTrimmedValue($row, 'social_facebook')
                ),
                'social_instagram' => SocialHelper::extractInstagramHandleFromUrl(
                    $this->getTrimmedValue($row, 'social_instagram')
                ),
                'social_linkedin' => SocialHelper::extractLinkedInHandleFromUrl(
                    $this->getTrimmedValue($row, 'social_linkedin')
                ),
                'social_x' => SocialHelper::extractXHandleFromUrl(
                    $this->getTrimmedValue($row, 'social_x')
                ),
                'marketplace_slug' => $this->getTrimmedValue($row, 'marketplace_slug'),
                'founding_year' => $foundingYear,
                'number_of_employees' => OrganisationNumberOfEmployees::fromOriginalValue(
                    $this->getTrimmedValue($row, 'number_of_employees')
                ),
                'turnover' => OrganisationTurnover::fromOriginalValue(
                    $this->getTrimmedValue($row, 'turnover')
                ),
                'source' => OrganisationSource::IMPORT_XLS,
                'is_active' => true,
            ];
        } catch (Throwable $e) {
            $error = 'Failed to validate/process row data' . $e->getMessage();
            $this->logger->recordError($index, $error);

            return;
        }

        // Step 2: Creates/Updates Organisation (critical - must succeed)
        $matchHash = Organisation::generateMatchHashFor($name, $location->country?->id);
        try {
            $existingOrganisation = Organisation::where('match_hash', $matchHash)->first();
            if ($existingOrganisation) {
                // Updates existing organisation (hash auto-updates if name/country changed):
                $existingOrganisation->update($organisationData);
                $organisation = $existingOrganisation;
            } else {
                // Creates new organisation (hash auto-generated via model events):
                $organisation = Organisation::create($organisationData);
            }
        } catch (Throwable $e) {
            $error = "Failed to create/update organisation '$name': " . $e->getMessage();
            $this->logger->recordError($index, $error);

            return; // Stop here - can't continue without organisation
        }

        // Step 3: Handle Logo (optional - continue even if it fails)
        $folderPath = dirname($this->filePath);
        $logoFilename = OrganisationsImportLogoHelper::findLogoInImportFolder($name, $folderPath, $this->logger, $index);
        if ($logoFilename) {
            try {
                OrganisationsImportLogoHelper::importLogo(
                    $organisation,
                    $folderPath,
                    basename($logoFilename),
                    $this->logger,
                    $index,
                );
            } catch (Throwable $e) {
                $error = "Failed to import logo for '$name': " . $e->getMessage();
                $this->logger->recordWarning($index, $error);
            }
        } else {
            // Delete existing logo from previous import (if there was one from a previous import):
            try {
                $organisation->logo()->delete();
            } catch (Throwable $e) {
                $error = "Failed to delete existing logo for '$name': " . $e->getMessage();
                $this->logger->recordWarning($index, $error);
            }
        }

        // Step 4: Handle Associations (optional - continue even if it fails)
        try {
            DB::transaction(function () use ($row, $organisation) {
                $this->handleAssociations($row, $organisation);
            });
        } catch (Throwable $e) {
            $error = "Failed to sync associations for '$name': " . $e->getMessage();
            $this->logger->recordWarning($index, $error);
        }

        // Success! Log the final result!
        $wasRecentlyCreated = $organisation->wasRecentlyCreated;
        if ($wasRecentlyCreated) {
            $this->logger->recordCreated($name);
        } else {
            $this->logger->recordUpdated($name);
        }

    }

    /**
     * Get a trimmed value for a specific attribute in the row.
     *
     * @param  Collection<string, mixed>  $row  The row data as a collection.
     * @param  string  $attribute  The attribute to retrieve and trim.
     *
     * @return string|null Trimmed value or null if empty.
     */
    public function getTrimmedValue(Collection $row, string $attribute): ?string
    {
        $key = $this->getKeyForAttribute($attribute);
        $rawValue = $key !== null ? $row->get($key) : null;
        $value = is_scalar($rawValue) ? mb_trim((string) $rawValue) : null;

        return $value !== '' ? $value : null;

    }

    /**
     * Get the row key for a specific attribute.
     *
     * @param  string  $attribute  The attribute name
     *
     * @return string|null The corresponding row key
     */
    private function getKeyForAttribute(string $attribute): ?string
    {
        $result = array_search($attribute, self::COLUMN_TO_ATTRIBUTE_MAP, true);

        return is_string($result) ? $result : null;

    }

    /**
     * Handle dynamic associations for an organisation.
     *
     * @param  Collection<string, mixed>  $row  The row data
     * @param  Organisation  $organisation  The organisation instance
     */
    private function handleAssociations(Collection $row, Organisation $organisation): void
    {
        foreach (self::ASSOCIATIONS_MAP as $association) {
            $ids = [];
            foreach ($association['columns'] as $column) {
                $value = $row->get($column);
                if (! empty($value) && is_scalar($value)) {
                    $name = (string) $value;

                    // Creates a cache key combining model and name:
                    $cacheKey = $association['model'] . ':' . $name;

                    // Checks if this combination has already been looked-up:
                    if (! array_key_exists($cacheKey, $this->modelLookupCache)) {
                        // First time seeing this name - do the database lookup:
                        $modelInstance = $association['model']::where('name', $name)->first();
                        // Stores ID (or null if not found):
                        $this->modelLookupCache[$cacheKey] = $modelInstance?->id;
                    }

                    // Uses the cached ID (avoids repeated database queries):
                    if ($this->modelLookupCache[$cacheKey] !== null) {
                        $ids[] = $this->modelLookupCache[$cacheKey];
                    }
                }
            }

            // Always sync, even if $ids is empty
            $organisation->{$association['relation']}()->sync($ids);
        }
    }
}
