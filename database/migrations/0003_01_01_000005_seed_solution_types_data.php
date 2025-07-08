<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Solution Types with set ids & names, custom order, label and description.
     */
    private const SOLUTION_TYPES = [
        ['id' => 1, 'name' => 'Chatbot'],
        ['id' => 3, 'name' => 'Forecasting'],
        ['id' => 2, 'name' => 'Generative AI'],
        ['id' => 6, 'name' => 'Image Processing'],
        ['id' => 5, 'name' => 'Recommender Systems'],
        ['id' => 4, 'name' => 'Robotics & Interactions'],
        ['id' => 7, 'name' => 'Speech Processing'],
        ['id' => 8, 'name' => 'Text Processing'],
        ['id' => 9, 'name' => 'Other Solution Types', 'order' => 100],
    ];

    /**
     * Populates the database with the initial data for Solution Types.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::SOLUTION_TYPES)->sortBy('name')->values();
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

        // Insert all solution types at once.
        DB::table('solution_types')->insert($entries);
    }

    /**
     * Reverse the population of database with Solution Types data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * solution types use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::SOLUTION_TYPES, 'id');

        // Delete all records with these IDs
        DB::table('solution_types')->whereIn('id', $ids)->delete();
    }
};
