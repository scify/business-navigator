<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Technology Types with set ids & names, custom order, label and description.
     */
    private const TECHNOLOGY_TYPES = [
        ['id' => 1, 'name' => 'Audit', 'label' => null],
        ['id' => 2, 'name' => 'AutoML', 'label' => 'Automated Machine Learning'],
        ['id' => 3, 'name' => 'Cloud & Hardware Processing', 'label' => null],
        ['id' => 4, 'name' => 'Data Preparation', 'label' => null],
        ['id' => 5, 'name' => 'Foundation Model', 'label' => null],
        ['id' => 6, 'name' => 'ML/DL', 'label' => 'Machine Learning / Deep Learning'],
        ['id' => 7, 'name' => 'MLOps', 'label' => 'Machine Learning Operations'],
        ['id' => 8, 'name' => 'No Code/Low Code', 'label' => 'No code or low code'],
        ['id' => 9, 'name' => 'Other Type of Technology', 'label' => null, 'order' => 100],
    ];

    /**
     * Populates the database with the initial data for Technology Types.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::TECHNOLOGY_TYPES)->sortBy('name')->values();
        $orderIndex = 1;

        foreach ($sortedEntries as $type) {
            $entries[] = [
                'id' => $type['id'],
                'slug' => Str::slug($type['name']),
                'name' => $type['name'],
                'label' => $type['label'] ?? null,
                'order' => $type['order'] ?? $orderIndex++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all technology types at once.
        DB::table('technology_types')->insert($entries);
    }

    /**
     * Reverse the population of database with Technology Types data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * technology types use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::TECHNOLOGY_TYPES, 'id');

        // Delete all records with these IDs
        DB::table('technology_types')->whereIn('id', $ids)->delete();
    }
};
