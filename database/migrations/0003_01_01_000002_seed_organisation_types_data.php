<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Organisation Types with set ids & names, custom order, label and description.
     */
    private const ORGANISATION_TYPES = [
        ['id' => 1, 'name' => 'Start-up', 'order' => 1],
        ['id' => 2, 'name' => 'SME',
            // Fun fact: plural label <> singular label.
            'label' => 'Small and/or medium-sized enterprise(s)', 'order' => 2,
        ],
        ['id' => 3, 'name' => 'Public Organisation', 'order' => 3],
        ['id' => 4, 'name' => 'Technology Provider', 'order' => 4],
        ['id' => 5, 'name' => 'Service Provider', 'order' => 5],
        ['id' => 6, 'name' => 'Consultant', 'order' => 6],
        ['id' => 7, 'name' => 'Training Provider', 'order' => 7],
        ['id' => 8, 'name' => 'Innovation Hub', 'order' => 8],
        ['id' => 9, 'name' => 'Research Hub', 'order' => 9],
        ['id' => 10, 'name' => 'Other', 'order' => 100],
    ];

    /**
     * Populates the database with the initial data for Organisation Types.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::ORGANISATION_TYPES)->sortBy('name')->values();
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

        // Insert all organisation types at once.
        DB::table('organisation_types')->insert($entries);
    }

    /**
     * Reverse the population of database with Organisation Types data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * organisation types use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::ORGANISATION_TYPES, 'id');

        // Delete all records with these IDs
        DB::table('organisation_types')->whereIn('id', $ids)->delete();
    }
};
