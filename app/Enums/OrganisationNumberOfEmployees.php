<?php

namespace App\Enums;

use Illuminate\Support\Facades\Log;

/**
 * Number of employees in an organisation.
 *
 * The original values were mapped to integers to allow comparisons:
 *
 * - Original: 1-10,    Integer: 10
 * - Original: 11-50,   Integer: 50
 * - Original: 51-100,  Integer: 100
 * - Original: 101-250, Integer: 250
 * - Original: >250,    Integer: 251
 */
enum OrganisationNumberOfEmployees: int
{
    case LESS_THAN_10 = 10;
    case BETWEEN_10_AND_50 = 50;
    case BETWEEN_50_AND_100 = 100;
    case BETWEEN_100_AND_250 = 250;
    case MORE_THAN_250 = 251;

    /**
     * Map the original input value (e.g., "1-10") to the corresponding enum case.
     *
     * @param  string|null  $input  Original input value.
     *
     * @return self|null Enum case if found, null otherwise.
     */
    public static function fromOriginalValue(?string $input): ?self
    {
        $match = match ($input) {
            '1-10' => self::LESS_THAN_10,
            '11-50' => self::BETWEEN_10_AND_50,
            '51-100' => self::BETWEEN_50_AND_100,
            '101-250' => self::BETWEEN_100_AND_250,
            '>250' => self::MORE_THAN_250,
            default => null,
        };
        if ($match === null && $input !== null) {
            Log::warning("Unable to match num of employees enum value: '$input'");
        }

        return $match;
    }

    /**
     * Get the original input value corresponding to the enum case.
     *
     * @return string Original input value.
     */
    public function toOriginalValue(): string
    {
        return match ($this) {
            self::LESS_THAN_10 => '1-10',
            self::BETWEEN_10_AND_50 => '11-50',
            self::BETWEEN_50_AND_100 => '51-100',
            self::BETWEEN_100_AND_250 => '101-250',
            self::MORE_THAN_250 => '>250',
        };
    }

    /**
     * Get a human-readable range description for the enum case.
     *
     * @return string Readable range description (e.g., "1-10").
     */
    public function getRange(): string
    {
        return self::toOriginalValue();
    }

    /**
     * Validate if the input value matches any known original value.
     *
     * @param  string  $input  Original input value.
     *
     * @return bool True if valid, false otherwise.
     */
    public static function isValidOriginalValue(string $input): bool
    {
        return in_array($input, ['1-10', '11-50', '51-100', '101-250', '>250'], true);
    }

    /**
     * Get the numeric bounds for the enum case.
     *
     * @return array{lower: int, upper: ?int}
     */
    public function getBounds(): array
    {
        return match ($this) {
            self::LESS_THAN_10 => ['lower' => 1, 'upper' => 10],
            self::BETWEEN_10_AND_50 => ['lower' => 11, 'upper' => 50],
            self::BETWEEN_50_AND_100 => ['lower' => 51, 'upper' => 100],
            self::BETWEEN_100_AND_250 => ['lower' => 101, 'upper' => 250],
            self::MORE_THAN_250 => ['lower' => 251, 'upper' => null],
        };
    }
}
