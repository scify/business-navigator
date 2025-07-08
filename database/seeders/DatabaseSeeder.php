<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // All reference data is now handled by migrations:
            // - 0003_01_01_000001_seed_countries_data.php
            // - 0003_01_01_000002_seed_organisation_types_data.php
            // - 0003_01_01_000003_seed_industry_sectors_data.php
            // - 0003_01_01_000004_seed_enterprise_functions_data.php
            // - 0003_01_01_000005_seed_solution_types_data.php
            // - 0003_01_01_000006_seed_technology_types_data.php
            // - 0003_01_01_000007_seed_offer_types_data.php

            // Only non-reference data remains in seeders:
            UserSeeder::class,
        ]);
    }
}
