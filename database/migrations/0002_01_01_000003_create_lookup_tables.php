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
        // Organisation Types
        Schema::create('organisation_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        // IndustrySector Sectors
        Schema::create('industry_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        // Enterprise Functions
        Schema::create('enterprise_functions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        // (AI) Solution Types
        Schema::create('solution_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        // Technology Types
        Schema::create('technology_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

        // Offer Types
        Schema::create('offer_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('label')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_types');
        Schema::dropIfExists('technology_types');
        Schema::dropIfExists('solution_types');
        Schema::dropIfExists('enterprise_functions');
        Schema::dropIfExists('industry_sectors');
        Schema::dropIfExists('organisation_types');
    }
};
