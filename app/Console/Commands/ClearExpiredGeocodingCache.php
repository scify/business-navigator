<?php

namespace App\Console\Commands;

use App\Models\GeocodingCache;
use Illuminate\Console\Command;

class ClearExpiredGeocodingCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:expired-geocoding-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired geocoding cache entries';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $deleted = GeocodingCache::where('expires_at', '<', now())->delete();
        $this->info('Deleted ' . (is_numeric($deleted) ? (string) $deleted : 'some') . ' expired cache entries.');

    }
}
