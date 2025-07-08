<?php

declare(strict_types=1);

namespace App\Events;

use App\Imports\Helpers\ImportLoggerHelper;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationsImportCompletedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $fileName  The imported file name
     * @param  ImportLoggerHelper  $logger  Import logger with statistics
     */
    public function __construct(
        public readonly string $fileName,
        public readonly ImportLoggerHelper $logger,
    ) {
        //
    }
}
