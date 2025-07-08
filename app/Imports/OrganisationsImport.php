<?php

declare(strict_types=1);

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Class OrganisationsImport
 * Handles the import of organisation data from Excel (XLSX) files.
 */
class OrganisationsImport implements WithMultipleSheets
{
    /**
     * The full path of the imported Excel file (will be used for logo import).
     */
    protected string $filePath;

    /**
     * Create a new import instance.
     *
     * @param  string  $filePath  The full path of the Excel file being imported.
     *                            This is required to determine the location of
     *                            any logos which might also need to be imported.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Define which sheets to import and how to handle them.
     *
     * @return array<int, OrganisationsSheetImport> Array of sheet configurations
     */
    public function sheets(): array
    {
        return [
            0 => new OrganisationsSheetImport($this->filePath), // Only process the first sheet (index 0)
            // More sheets could be added if needed, for example:
            // 'Data' => new OrganisationsSheetImport($this->filePath), // Process 'Data' sheet
            // 1 => new SomeOtherSheetImport(), // Process another sheet with different logic
        ];

    }
}
