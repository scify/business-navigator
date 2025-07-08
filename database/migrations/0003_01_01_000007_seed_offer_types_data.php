<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * List of Offer Types with set ids & names, custom order, label and description.
     */
    private const OFFER_TYPES = [
        ['id' => 1, 'name' => 'Platform'],
        ['id' => 2, 'name' => 'Services'],
        ['id' => 3, 'name' => 'Not Specified', 'order' => 100],
    ];

    /**
     * Populates the database with the initial data for Offer Types.
     */
    public function up(): void
    {
        $entries = [];
        $sortedEntries = collect(self::OFFER_TYPES)->sortBy('name')->values();
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

        // Insert all offer types at once.
        DB::table('offer_types')->insert($entries);
    }

    /**
     * Reverse the population of database with Offer Types data.
     *
     * Unlike countries which are directly referenced by organisations (with restrictOnDelete),
     * offer types use pivot tables with cascadeOnDelete, so they can be safely removed.
     */
    public function down(): void
    {
        // Extract the IDs from the constant array
        $ids = array_column(self::OFFER_TYPES, 'id');

        // Delete all records with these IDs
        DB::table('offer_types')->whereIn('id', $ids)->delete();
    }
};
