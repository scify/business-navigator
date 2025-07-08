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
        Schema::create('geocoding_cache', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->enum('source', ['opencage', 'google', 'mapbox']);
            $table->enum('type', ['forward', 'reverse'])->default('forward');
            $table->json('response');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geocoding_cache');
    }
};
