<?php

namespace App\Enums;

use Illuminate\Support\Facades\Log;

/**
 * OrganisationTurnover (in million euros) for an organisation.
 *
 * The original values were mapped to integers for better storage and
 * processing:
 *
 * - Original: 0-1 million euros,   Integer: 1
 * - Original: 1-3 million euros,   Integer: 3
 * - Original: 3-5 million euros,   Integer: 5
 * - Original: >5 million euros,    Integer: 6
 */
enum OrganisationTurnover: int
{
    case LESS_THAN_1M = 1;
    case BETWEEN_1M_AND_3M = 3;
    case BETWEEN_3M_AND_5M = 5;
    case MORE_THAN_5M = 6;

    /**
     * Map the original input value to the corresponding enum case.
     *
     * @param  string|null  $input  Original input value (e.g., "<1", ">1").
     */
    public static function fromOriginalValue(?string $input): ?self
    {
        $match = match ($input) {
            '0-1 million euros' => self::LESS_THAN_1M,
            '1-3 million euros' => self::BETWEEN_1M_AND_3M,
            '3-5 million euros' => self::BETWEEN_3M_AND_5M,
            '>5 million euros' => self::MORE_THAN_5M,
            default => null,
        };
        if ($match === null && $input !== null) {
            Log::warning("Unable to match turnover enum value: '$input'");

        }

        return $match;
    }

    /**
     * Get the original input value corresponding to the enum case.
     */
    public function toOriginalValue(): string
    {
        return match ($this) {
            self::LESS_THAN_1M => '0-1 million euros',
            self::BETWEEN_1M_AND_3M => '1-3 million euros',
            self::BETWEEN_3M_AND_5M => '3-5 million euros',
            self::MORE_THAN_5M => '>5 million euros',
        };
    }

    /**
     * Get a human-readable range description for the enum case.
     */
    public function getRange(): string
    {
        return self::toOriginalValue();
    }

    /**
     * Get a human-readable range description for the enum case. Used on the
     * front-end as description for turnover cards.
     */
    public function getRangeProper(): string
    {
        return match ($this) {
            self::LESS_THAN_1M => 'Less than €1 million',
            self::BETWEEN_1M_AND_3M => 'Between €1 million and €3 million',
            self::BETWEEN_3M_AND_5M => 'Between €3 million and €5 million',
            self::MORE_THAN_5M => 'More than €5 million',
        };
    }

    /**
     * Get a human-readable range description for the enum case. Used on the
     * front-end as the main bold and exaggerated value.
     */
    public function getRangeProperShort(): string
    {
        return match ($this) {
            self::LESS_THAN_1M => '<€1M',
            self::BETWEEN_1M_AND_3M => '€1M-€3M',
            self::BETWEEN_3M_AND_5M => '€3M-€5M ',
            self::MORE_THAN_5M => '€5M+',
        };
    }

    /**
     * Validate if the input value matches any known original value.
     *
     * @param  string  $input  Original input value.
     */
    public static function isValidOriginalValue(string $input): bool
    {
        return in_array($input, [
            '0-1 million euros',
            '1-3 million euros',
            '3-5 million euros',
            '>5 million euros',
        ], true);
    }

    /**
     * Get the numeric bounds for the enum case.
     *
     * @return array{lower: float, upper: ?float} Bounds as [lower, upper] where upper can be null.
     */
    public function getBounds(): array
    {
        return match ($this) {
            self::LESS_THAN_1M => ['lower' => 0.0, 'upper' => 1.0],
            self::BETWEEN_1M_AND_3M => ['lower' => 1.0, 'upper' => 3.0],
            self::BETWEEN_3M_AND_5M => ['lower' => 3.0, 'upper' => 5.0],
            self::MORE_THAN_5M => ['lower' => 5.0, 'upper' => null],
        };
    }
}
