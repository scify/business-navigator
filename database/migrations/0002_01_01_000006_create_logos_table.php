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
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organisation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('alt')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('size')->nullable();
            $table->boolean('has_transparency')->default(false);
            $table->string('background_color')->nullable();
            $table->enum('source', [
                'import_xls',
                'user_upload',
                'user_admin',
                'unknown',
            ])->default('unknown');
            $table->timestamps();
        });

        // Clean logos folder.
        $files = Storage::disk('media')->files('logos');
        foreach ($files as $file) {
            if (! str_ends_with($file, '.gitignore')) {
                Storage::disk('media')->delete($file);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logos');
    }
};
