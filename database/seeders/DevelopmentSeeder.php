<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database with development data.
     *
     * @internal This should never run on production.
     */
    public function run(): void
    {
        if (app()->environment(['production', 'staging'])) {
            $this->command->error('DevelopmentSeeder should not run in production or staging!');

            return;

        }

        $this->call([
            OrganisationSeeder::class,
            UserSeeder::class,
        ]);
    }
}
