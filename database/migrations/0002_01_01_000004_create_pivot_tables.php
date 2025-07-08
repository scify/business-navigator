<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pivot for Organisation Types
        Schema::create('organisation_organisation_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('organisation_type_id')
                ->constrained('organisation_types')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index (with custom name as default is 'too long'):
            $table->unique(['organisation_id', 'organisation_type_id'],
                'org_type_unique');
        });

        // Pivot for Industries
        Schema::create('organisation_industry_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('industry_sector_id')
                ->constrained('industry_sectors')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index:
            $table->unique(['organisation_id', 'industry_sector_id'],
                'org_industry_sector_unique');
        });

        // Pivot for Enterprise Functions
        Schema::create('organisation_enterprise_function', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('enterprise_function_id')
                ->constrained('enterprise_functions')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index:
            $table->unique(
                ['organisation_id', 'enterprise_function_id'],
                'org_enterprise_function_unique');
        });

        // Pivot for Solution Types
        Schema::create('organisation_solution_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('solution_type_id')
                ->constrained('solution_types')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index:
            $table->unique(['organisation_id', 'solution_type_id'],
                'org_solution_type_unique');
        });

        // Pivot for Technology Types
        Schema::create('organisation_technology_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('technology_type_id')
                ->constrained('technology_types')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index:
            $table->unique(['organisation_id', 'technology_type_id'],
                'org_technology_type_unique');
        });

        // Pivot for Offer Types
        Schema::create('organisation_offer_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();
            $table->foreignId('offer_type_id')
                ->constrained('offer_types')
                ->cascadeOnDelete(); // Allow cascade delete for reference types
            $table->timestamps();
            // Composite unique index:
            $table->unique(['organisation_id', 'offer_type_id'],
                'org_offer_type_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_offer_type');
        Schema::dropIfExists('organisation_technology_type');
        Schema::dropIfExists('organisation_solution_type');
        Schema::dropIfExists('organisation_enterprise_function');
        Schema::dropIfExists('organisation_industry_sector');
        Schema::dropIfExists('organisation_organisation_type');
    }
};
