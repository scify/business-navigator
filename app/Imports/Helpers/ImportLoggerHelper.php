<?php

declare(strict_types=1);

namespace App\Imports\Helpers;

use App\Events\OrganisationsImportCompletedEvent;
use Illuminate\Support\Facades\Log;

/**
 * Class ImportLoggerHelper
 * Handles logging and statistics tracking for import operations.
 */
class ImportLoggerHelper
{
    /**
     * Import statistics for better reporting.
     *
     * @var array{processed: int, skipped: int, created: int, updated: int, errors: string[], warnings: string[], fatal_error: string|null, start_time: float|null, finish_time: float|null}
     */
    private array $importStats = [
        'processed' => 0,
        'skipped' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => [],
        'warnings' => [],
        'fatal_error' => null,
        'start_time' => null,
        'finish_time' => null,
    ];

    /**
     * The name of the import for logging context (e.g. `Organisations`)
     */
    private string $importName;

    /**
     * The filepath of the sheet imported for logging context.
     */
    private string $importFilePath;

    /**
     * The row offset to apply when calculating Excel row numbers.
     *
     * For WithHeadingRow this should be '2', as the first row process in a
     * Collection with index = 0, is the 2nd line on the Sheet.
     */
    private int $importRowOffset;

    /**
     * Create a new logger helper instance.
     *
     * @param  string  $importName  The name of the import for logging context
     *                              (e.g. 'Organisations').
     * @param  string  $importFilePath  The file path being imported.
     * @param  int  $importRowOffset  The offset to add when calculating Excel
     *                                row numbers. This should be '2' for sheets
     *                                WithHeadingRow. (Defaults to 0).
     */
    public function __construct(
        string $importName = 'Generic',
        string $importFilePath = '',
        int $importRowOffset = 0)
    {
        $this->importName = $importName;
        $this->importFilePath = $importFilePath;
        $this->importRowOffset = $importRowOffset;
    }

    /**
     * Calculate the duration between start and finish times in seconds.
     *
     * @return float Log Duration in seconds or 0 if times are not set.
     */
    public function calculateExecutionTimeInSeconds(): float
    {
        if (! isset($this->importStats['finish_time']) || ! isset($this->importStats['start_time'])) {
            return 0;

        }

        $duration = $this->importStats['finish_time'] - $this->importStats['start_time'];

        return round($duration, 2);

    }

    /**
     * Get the number of created rows.
     */
    public function getCreated(): int
    {
        return $this->importStats['created'];
    }

    /**
     * Get the errors (failures) array.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->importStats['errors'];
    }

    /**
     * Get the fatal error message.
     */
    public function getFatalError(): ?string
    {
        return $this->importStats['fatal_error'];
    }

    /**
     * Get the name for the Import itself (e.g. `Organisations`).
     */
    public function getImportName(): string
    {
        return $this->importName;
    }

    /**
     * Get comprehensive import statistics.
     *
     * @return array{processed: int, skipped: int, created: int, updated: int, errors: string[], warnings: string[], fatal_error: string|null, start_time: float|null, finish_time: float|null}
     */
    public function getImportStats(): array
    {
        return $this->importStats;
    }

    /**
     * Get the number of processed rows.
     */
    public function getProcessed(): int
    {
        return $this->importStats['processed'];
    }

    /**
     * Get the number of succeeded rows (created + updated).
     */
    public function getSucceeded(): int
    {
        return $this->importStats['created'] + $this->importStats['updated'];
    }

    /**
     * Get a summary string of the import results.
     */
    public function getSummary(): string
    {
        $processed = $this->importStats['processed'];
        $skipped = $this->importStats['skipped'];
        $created = $this->importStats['created'];
        $updated = $this->importStats['updated'];
        $warnings = count($this->importStats['warnings']);
        $errors = count($this->importStats['errors']);
        $executionTimeInSeconds = $this->calculateExecutionTimeInSeconds();

        return sprintf(
            '%s: Processed %d, skipped %d rows. Created %d, updated %d entries with %d warnings. %d failed. Completed in %ss',
            $this->importName,
            $processed,
            $skipped,
            $created,
            $updated,
            $warnings,
            $errors,
            $executionTimeInSeconds
        );
    }

    /**
     * Get the number of updated rows.
     */
    public function getUpdated(): int
    {
        return $this->importStats['updated'];
    }

    /**
     * Get the warnings array.
     *
     * @return array<string>
     */
    public function getWarnings(): array
    {
        return $this->importStats['warnings'];
    }

    /**
     * Check if import has any errors.
     */
    public function hasErrors(): bool
    {
        return ! empty($this->importStats['errors']);
    }

    /**
     * Check if import has a fatal error.
     */
    public function hasFatalError(): bool
    {
        return $this->importStats['fatal_error'] !== null;
    }

    /**
     * Check if import has any warnings.
     */
    public function hasWarnings(): bool
    {
        return ! empty($this->importStats['warnings']);
    }

    /**
     * Log import start.
     *
     * @param  array<string, mixed>  $context  Additional context for logging
     */
    public function logImportStart(array $context = []): void
    {
        // Start timer.
        $this->importStats['start_time'] = microtime(true);

        $contextWithName = array_merge(['importName' => $this->importName], $context);
        Log::channel('imports')->info('🟢 {importName} started', $contextWithName);
        // Also broadcasted to main channel.
        Log::info('🟢 Import started; check import logs for details');
    }

    /**
     * Log import completion with comprehensive statistics.
     *
     * @param  array<string, mixed>  $context  Additional context for logging
     */
    public function logImportCompletion(array $context = []): void
    {
        // Records finish time:
        $this->importStats['finish_time'] = microtime(true);

        // Enriches context:
        $executionTimeInSeconds = $this->calculateExecutionTimeInSeconds();
        $stats = array_merge(
            ['importName' => $this->importName],
            ['executionTimeInSeconds' => $executionTimeInSeconds],
            ['summary' => $this->getSummary()],
            $context,
            $this->getImportStats(),
        );

        if ($this->importStats['fatal_error'] !== null) {
            Log::channel('imports')->error('🔴 {importName} failed in {executionTimeInSeconds}s', $stats);
            // Also broadcasted to main channel.
            Log::error('🔴 Import failed; check import logs for details');
        } else {
            Log::channel('imports')->info('🏁 {importName} completed in {executionTimeInSeconds}s', $stats);
            // Also broadcasted to main channel.
            Log::info('🏁 Import completed; check import logs for details');
        }

        // Logs summary of any issues
        if (! empty($this->importStats['errors'])) {
            $errorCount = count($this->importStats['errors']);
            Log::channel('imports')->warning('{importName}: Completed with {errorCount} errors', [
                'importName' => $this->importName,
                'errorCount' => $errorCount,
            ]);
        }

        if (! empty($this->importStats['warnings'])) {
            $warningCount = count($this->importStats['warnings']);
            Log::channel('imports')->info('{importName}: Completed with {warningCount} warnings', [
                'importName' => $this->importName,
                'warningCount' => $warningCount,
            ]);
        }

        // 🎉 Fires the completion event:
        OrganisationsImportCompletedEvent::dispatch(
            basename($this->importFilePath),
            $this
        );

    }

    /**
     * Record a newly created model - entry in database.
     *
     * @param  string  $recordName  The record's name.
     */
    public function recordCreated(string $recordName = ''): void
    {
        $this->importStats['created']++;
        Log::channel('imports')->debug("✅ Created new organisation: '$recordName'");

    }

    /**
     * Log a debug notice on the log files.
     *
     * @param  int  $index  Row index (offset will be applied automatically).
     * @param  string  $debug  Debug message.
     */
    public function recordDebug(int $index, string $debug): void
    {
        $rowNumber = $index + $this->importRowOffset;
        Log::channel('imports')->debug('{importName}: Row {rowNumber} - {debug}', [
            'importName' => $this->importName,
            'rowNumber' => $rowNumber,
            'debug' => $debug,
        ]);
    }

    /**
     * Record an error (i.e. failure).
     *
     * @param  int  $index  Row index (offset will be applied automatically).
     * @param  string  $error  Error message.
     */
    public function recordError(int $index, string $error): void
    {
        $rowNumber = $index + $this->importRowOffset;
        $this->importStats['errors'][] = "Row $rowNumber: $error";
        Log::channel('imports')->error('{importName}: Row {rowNumber} - {error}', [
            'importName' => $this->importName,
            'rowNumber' => $rowNumber,
            'error' => $error,
        ]);
    }

    /**
     * Record a fatal error that **stops the entire process**.
     *
     * @param  string  $error  Error message
     */
    public function recordFatalError(string $error): void
    {
        $this->importStats['fatal_error'] = $error;
        $this->importStats['finish_time'] = microtime(true);

        Log::channel('imports')->error('{importName}: FATAL ERROR: {fatalError}', [
            'importName' => $this->importName,
            'fatalError' => $error,
        ]);

        // Immediately logs completion with fatal error context:
        $this->logImportCompletion([
            'importName' => $this->importName,
            'fatalError' => $error,
        ]);
    }

    /**
     * Record a processed row.
     *
     * @param  int  $index  Row index (offset will be applied automatically)
     */
    public function recordProcessed(int $index): void
    {
        $this->importStats['processed']++;
        Log::channel('imports')->debug('{importName}: Processing row {rowNumber}', [
            'importName' => $this->importName,
            'rowNumber' => $index + $this->importRowOffset,
        ]);
    }

    /**
     * Record a skipped row.
     *
     * @param  int  $index  Row index (offset will be applied automatically).
     * @param  string  $reason  Reason for skipping.
     * @param  bool  $silent  Whether to skip silently (without logging). Default is true.
     */
    public function recordSkip(int $index, string $reason, bool $silent = true): void
    {
        $this->importStats['skipped']++;
        if (! $silent) {
            Log::channel('imports')->debug('{importName}: Skipped row {rowNumber} - {reason}', [
                'importName' => $this->importName,
                'rowNumber' => $index + $this->importRowOffset,
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Record an updated (i.e. existing, 'touched') model - entry.
     *
     * @param  string  $recordName  The record's name.
     */
    public function recordUpdated(string $recordName = ''): void
    {
        $this->importStats['updated']++;
        Log::channel('imports')->debug("🔄 Updated existing organisation: '$recordName'");

    }

    /**
     * Record a warning.
     *
     * @param  int  $index  Row index (offset will be applied automatically).
     * @param  string  $warning  Warning message.
     */
    public function recordWarning(int $index, string $warning): void
    {
        $rowNumber = $index + $this->importRowOffset;
        $this->importStats['warnings'][] = "Row $rowNumber: $warning";
        Log::channel('imports')->warning('{importName}: Row {rowNumber} - {warning}', [
            'importName' => $this->importName,
            'rowNumber' => $rowNumber,
            'warning' => $warning,
        ]);
    }
}
