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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')
                ->comment('Country name in English');
            $table->string('alpha2', 2)
                ->unique()
                ->comment('ISO 3166-1 alpha-2 country code');
            $table->string('alpha3', 3)
                ->unique()
                ->comment('ISO 3166-1 alpha-3 country code');
            $table->string('demonym')->nullable()
                ->comment('What people from this country are called in English');
            $table->decimal('lat', 10, 8)->nullable()
                ->comment('Latitude');
            $table->decimal('lng', 11, 8)->nullable()
                ->comment('Longitude');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
