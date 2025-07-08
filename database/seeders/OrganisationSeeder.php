<?php

namespace Database\Seeders;

use App\Models\Organisation;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the Organisation seeder (dev/local only).
     *
     * @internal This should never run on production.
     */
    public function run(): void
    {
        if (app()->environment(['production', 'staging'])) {
            $this->command->info('OrganisationSeeder is not meant to run in production or staging environments!');

            return;

        }

        Organisation::factory()->count(300)->create();

    }
}
