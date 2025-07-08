<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @internal Please note that the name should not be unique. There might be
     *   cases of organisations which have the exact same name, yet they are in
     *   completely different countries. This should be taken under
     *   consideration on the creation of the slug.
     */
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            // Primary identification.
            $table->id();
            $table->string('slug')->unique();

            // Basic information.
            $table->string('name'); // See internal note.
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();

            // Address data.
            $table->foreignId('country_id')->nullable()->comment('Country ID')
                ->constrained('countries')->restrictOnDelete(); // default behavior (for clarity)
            $table->string('region')->nullable()->comment('Region/State');
            $table->string('city')->nullable()->comment('Locality/City');
            $table->string('postal_code')->nullable();
            $table->text('address_1')->nullable()->comment('User-entered address: Street name and number');
            $table->text('address_2')->nullable()->comment('User-entered address: Extra details');
            $table->text('formatted_address')->nullable()->comment('Formatted, full address');

            // Location data.
            $table->decimal('lat', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('lng', 11, 8)->nullable()->comment('Longitude');
            $table->unsignedTinyInteger('location_confidence')->nullable()->comment('Confidence level of the location data (less is worse)');
            $table->enum('location_source', ['manual', 'opencage', 'google', 'mapbox', 'osm', 'import_xls', 'unknown'])->nullable()->comment('Source of location data');
            $table->json('location_data')->nullable();

            // Contact information.
            $table->string('website_url')->nullable();
            $table->string('social_bluesky')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_x')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('marketplace_slug')->nullable()->comment('Marketplace vendor slug');

            // Descriptive data & enums (@see app/Enums)
            $table->unsignedSmallInteger('founding_year')->nullable();
            $table->unsignedSmallInteger('number_of_employees')->nullable()
                ->comment('Enum backing values: 10, 50, 100, 250, 251');
            $table->decimal('turnover', 10)->nullable()
                ->comment('Enum backing values: 1, 3, 5, 6');

            // Status and Source.
            $table->enum('source', [
                'import_xls',
                'import_api',
                'import_legacy',
                'user_manual',
                'user_admin',
                'partner_portal',
                'data_aggregator',
                'unknown',
            ])->default('unknown');
            $table->boolean('is_active')->default(true);
            $table->string('match_hash')->unique()->comment('Hash of name + country for import matching and deduplication');

            // Timestamps and Soft deletes.
            $table->timestamps();
            $table->softDeletes();

            // Indexes and constraints
            $table->index(['lat', 'lng'], 'organisations_lat_lng_index');
            $table->unique(['name', 'country_id'], 'organisations_name_country_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
