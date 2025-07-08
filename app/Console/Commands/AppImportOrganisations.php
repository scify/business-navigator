<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\OrganisationsImportCompletedEvent;
use App\Imports\Helpers\ImportLoggerHelper;
use App\Imports\OrganisationsImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function Laravel\Prompts\select;

class AppImportOrganisations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-organisations {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Organisation data from Excel files';

    /**
     * The console command version.
     */
    protected string $version = '2.4 - 2025-07-04';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Set-up.
        $storageDisk = Storage::disk('import');
        $reportedPath = str_replace(base_path(), '', $storageDisk->path(''));

        // ─── Usage banner ────────────────────────────────────────────────────
        $this->newLine();
        $this->info(
            sprintf(
                '<fg=magenta;options=bold,underscore>%s (v%s)</>',
                $this->description,
                $this->version
            )
        );
        $this->newLine();

        $this->line('Imports organisation data from an Excel file.');
        $this->line(sprintf('Scan path: <info>%s</info>', $reportedPath));
        $this->line('Only <info>.xlsx</info> files are shown for selection.');

        $this->newLine();
        $this->line('📂  Folder structure');
        $this->line('   • Put each Excel file in its own sub-folder.');
        $this->line('   • If the organisations have logo images, drop them in the same folder.');
        $this->line('     The importer looks in that folder first for matching logos.');
        $this->newLine();

        // Check if file is provided as argument
        $fileArgument = $this->argument('file');
        $selectedFile = is_string($fileArgument) ? $fileArgument : null;

        if ($selectedFile) {
            // Validate the provided file exists
            if (! $storageDisk->exists($selectedFile)) {
                $this->error("File not found: $selectedFile");

                return 2;
            }

            // Validate it's an xlsx file
            if (! str_ends_with($selectedFile, '.xlsx')) {
                $this->error("File must be an Excel (.xlsx) file: $selectedFile");

                return 2;
            }
        } else {
            // Collects XLSX files.
            $files = collect($storageDisk->allFiles())
                ->filter(fn ($file) => is_string($file) && str_ends_with($file, '.xlsx'))
                ->values()
                ->toArray();

            // Aborts if no suitable XLSX files have been found.
            if (empty($files)) {
                $this->info('No importable .xlsx files found.');

                return 2;
            }

            // Presents the selection prompt to the user (even if only 1 file has been found):
            $stringFiles = array_filter($files, 'is_string');
            $selectedFile = (string) select(
                label: 'Select the file you want to import:',
                options: $stringFiles
            );
        }

        // Confirms the user's choice:
        $this->info("You selected: $selectedFile");
        $this->newLine();

        // Performs the Import:
        $this->info('🚀 Starting import...');
        $importLogger = null;
        Event::listen(
            OrganisationsImportCompletedEvent::class,
            function (OrganisationsImportCompletedEvent $event) use (&$importLogger) {
                $importLogger = $event->logger;
            });

        $import = new OrganisationsImport($selectedFile);
        Excel::import($import, $selectedFile, 'import');

        // Now use the captured logger
        if ($importLogger) {
            return $this->displayImportResults($importLogger);
        } else {
            $this->error('No import statistics available');
        }

        return 0;

    }

    /**
     * Reports import stats on CLI.
     *
     * @param  ImportLoggerHelper  $logger  Import logger with statistics
     *
     * @see ImportLoggerHelper
     */
    private function displayImportResults(ImportLoggerHelper $logger): int
    {
        if ($logger->hasFatalError()) {
            $fatalError = $logger->getFatalError();
            $this->components->error($fatalError ?? 'An unknown fatal error occurred');

            return 1;
        }


        // Get execution time from the logger object
        $importName = $logger->getImportName();
        $executionTime = $logger->calculateExecutionTimeInSeconds();
        $this->newLine();

        $this->components->info("$importName completed in $executionTime seconds.");

        // Statistics using the logger object methods:
        $this->components->twoColumnDetail('Total rows read', (string) $logger->getProcessed());
        $this->components->twoColumnDetail('├─ Succeeded', (string) $logger->getSucceeded());
        $this->components->twoColumnDetail('│  • Created', (string) $logger->getCreated());
        $this->components->twoColumnDetail('│  • Updated', (string) $logger->getUpdated());
        $this->components->twoColumnDetail('├─ Warnings', (string) count($logger->getWarnings()));
        $this->components->twoColumnDetail('└─ Failed (Errors)', (string) count($logger->getErrors()));

        // Errors section:
        if ($logger->hasErrors()) {
            $this->newLine();
            $this->components->error('Some rows failed to be imported:');
            foreach ($logger->getErrors() as $error) {
                $this->components->bulletList([$error]);
            }
        }

        // Warnings section:
        if ($logger->hasWarnings()) {
            $this->newLine();
            $this->components->warn('Data quality warnings:');
            $warnings = $logger->getWarnings();
            foreach (array_slice($warnings, 0, 10) as $warning) { // Show max 10
                $this->components->bulletList([$warning]);
            }

            if (count($warnings) > 10) {
                $this->line('... and ' . (count($warnings) - 10) . ' more warnings');
            }
        }

        $this->newLine();

        return 0;

    }
}
