<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Enterprise Functions with set ids & names, custom order, label and description.
     */
    private const ENTERPRISE_FUNCTIONS = [
        ['id' => 1, 'name' => 'Accounting & Finance'],
        ['id' => 2, 'name' => 'Acquisition & Purchasing'],
        ['id' => 3, 'name' => 'Customer Service & Support'],
        ['id' => 4, 'name' => 'Facilities Management'],
        ['id' => 5, 'name' => 'Human Resources'],
        ['id' => 6, 'name' => 'Legal & Compliance'],
        ['id' => 7, 'name' => 'Marketing & Advertising'],
        ['id' => 8, 'name' => 'Operations'],
        ['id' => 9, 'name' => 'Safety & Security'],
        ['id' => 10, 'name' => 'Sales'],
        ['id' => 11, 'name' => 'Supply Chain Management & Logistics'],
        ['id' => 12, 'name' => 'Any Type of Function', 'order' => 100],
        ['id' => 13, 'name' => 'Other Corporate Functions', 'order' => 101],
    ];

    /**
     * Populates the database with the initial data for Enterprise Functions.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::ENTERPRISE_FUNCTIONS)->sortBy('name')->values();
        $orderIndex = 1;

        foreach ($sortedEntries as $function) {
            $entries[] = [
                'id' => $function['id'],
                'slug' => Str::slug($function['name']),
                'name' => $function['name'],
                'label' => $function['label'] ?? null,
                'order' => $function['order'] ?? $orderIndex++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all enterprise functions at once.
        DB::table('enterprise_functions')->insert($entries);
    }

    /**
     * Reverse the population of database with Enterprise Functions data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * enterprise functions use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::ENTERPRISE_FUNCTIONS, 'id');

        // Delete all records with these IDs
        DB::table('enterprise_functions')->whereIn('id', $ids)->delete();
    }
};
