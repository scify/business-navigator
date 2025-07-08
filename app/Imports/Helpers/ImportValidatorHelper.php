<?php

declare(strict_types=1);

namespace App\Imports\Helpers;

use Illuminate\Support\Collection;

/**
 * Class ImportValidatorHelper
 * Handles validation logic for import operations.
 */
class ImportValidatorHelper
{
    /**
     * Validate that all required columns are present in a row.
     *
     * @param  Collection  $row  The row data
     * @param  array  $requiredColumns  Array of required column keys
     *
     * @return bool True if all required columns are present
     */
    public static function validateRequiredColumns(Collection $row, array $requiredColumns): bool
    {
        foreach ($requiredColumns as $key) {
            if (! $row->has($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that required fields have non-empty values.
     *
     * @param  Collection  $row  The row data
     * @param  array  $requiredFields  Array of required field names
     * @param  callable  $valueExtractor  Function to extract and trim values: fn($row, $field) => string|null
     *
     * @return bool True if all required fields have valid values
     */
    public static function validateRequiredFields(
        Collection $row, array $requiredFields, callable $valueExtractor
    ): bool {
        // Check if the entire row is empty (all values are null or empty strings):
        if (self::isRowEmpty($row)) {
            return false;

        }

        foreach ($requiredFields as $fieldName) {
            $value = $valueExtractor($row, $fieldName);
            if (empty($value)) {
                return false;

            }
        }

        return true;
    }

    /**
     * Validate a website URL.
     *
     * @param  string|null  $url  The URL to validate
     *
     * @return array [isValid: bool, cleanUrl: string|null]
     */
    public static function validateWebsiteUrl(?string $url): array
    {
        if (empty($url)) {
            return ['isValid' => true, 'cleanUrl' => null];

        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['isValid' => false, 'cleanUrl' => null];

        }

        return ['isValid' => true, 'cleanUrl' => $url];

    }

    /**
     * Validate a founding year with range checking.
     *
     * @param  int|string|null  $year  The year to validate
     *
     * @return array [isValid: bool, cleanYear: int|null]
     */
    public static function validateFoundingYear(int|string|null $year): array
    {
        if ($year === null || $year === '') {
            return ['isValid' => true, 'cleanYear' => null];

        }

        $yearInt = (int) $year;
        $currentYear = (int) date('Y');

        if ($yearInt < 1800 || $yearInt > $currentYear) {
            return ['isValid' => false, 'cleanYear' => null];

        }

        return ['isValid' => true, 'cleanYear' => $yearInt];
    }

    /**
     * Validate string length.
     *
     * @param  string|null  $value  The value to validate
     * @param  int  $maxLength  Maximum allowed length
     *
     * @return bool True if valid length
     */
    public static function validateLength(?string $value, int $maxLength = 255): bool
    {
        if (empty($value)) {
            return true;

        }

        return mb_strlen($value) <= $maxLength;

    }

    /**
     * Check if a row is completely empty.
     *
     * @param  Collection  $row  The row to check
     *
     * @return bool True if row is empty
     */
    public static function isRowEmpty(Collection $row): bool
    {
        return $row->filter(fn ($value) => ! filled($value))->count() === $row->count();

    }
}
