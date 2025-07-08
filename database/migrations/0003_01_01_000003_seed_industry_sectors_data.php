<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Industry Sectors with set ids & names, custom order, label and description.
     */
    private const INDUSTRIES = [
        ['id' => 1, 'name' => 'Aerospace & Defence'],
        ['id' => 2, 'name' => 'Agriculture & Forestry'],
        ['id' => 3, 'name' => 'Construction'],
        ['id' => 4, 'name' => 'Education & Training'],
        ['id' => 5, 'name' => 'Energy & Utilities'],
        ['id' => 6, 'name' => 'Fashion & Luxury'],
        ['id' => 7, 'name' => 'Finance & Insurance'],
        ['id' => 8, 'name' => 'Health & Well-being'],
        ['id' => 9, 'name' => 'Manufacturing'],
        ['id' => 10, 'name' => 'Media & Entertainment'],
        ['id' => 11, 'name' => 'Public Administration'],
        ['id' => 12, 'name' => 'Retail & Electronics'],
        ['id' => 13, 'name' => 'Software'],
        ['id' => 14, 'name' => 'Transport & Mobility'],
        ['id' => 15, 'name' => 'All Sectors', 'order' => 100],
        ['id' => 16, 'name' => 'Other Industries', 'order' => 101],
    ];

    /**
     * Populates the database with the initial data for Industry Sectors.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::INDUSTRIES)->sortBy('name')->values();
        $orderIndex = 1;

        foreach ($sortedEntries as $industry) {
            $entries[] = [
                'id' => $industry['id'],
                'slug' => Str::slug($industry['name']),
                'name' => $industry['name'],
                'label' => $industry['label'] ?? null,
                'order' => $industry['order'] ?? $orderIndex++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all industry sectors at once.
        DB::table('industry_sectors')->insert($entries);
    }

    /**
     * Reverse the population of database with Industry Sectors data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * industry sectors use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::INDUSTRIES, 'id');

        // Delete all records with these IDs
        DB::table('industry_sectors')->whereIn('id', $ids)->delete();
    }
};
