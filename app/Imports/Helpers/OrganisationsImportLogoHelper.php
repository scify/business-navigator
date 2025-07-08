<?php

declare(strict_types=1);

namespace App\Imports\Helpers;

use App\Enums\LogoSource;
use App\Models\Logo;
use App\Models\Organisation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganisationsImportLogoHelper
{
    /**
     * Find the logo file in the import folder based on the organisation name.
     */
    public static function findLogoInImportFolder(
        string $name,
        string $folderPath,
        ?ImportLoggerHelper $logger = null,
        ?int $index = null
    ): ?string {
        $filename = Str::slug($name, '_');
        $extensions = ['png', 'webp', 'jpg'];

        foreach ($extensions as $extension) {
            $file = "$folderPath/$filename.$extension";
            if (Storage::disk('import')->exists($file)) {
                $message = "Logo '$filename.$extension' found for '$name'";
                if ($logger && $index !== null) {
                    $logger->recordDebug($index, $message);
                } else {
                    Log::debug($message);
                }

                return $file;
            }

        }

        $warning = "Logo '$filename.$extension' not found for '$name' in /$folderPath.";
        if ($logger && $index !== null) {
            $logger->recordWarning($index, $warning);
        } else {
            Log::warning($warning);

        }

        return null;

    }

    /**
     * Import a logo and associate it with an organisation.
     *
     * Copies the file only if the file size differs. UUID handling:
     * - Reuses existing UUID if file size unchanged
     * - Generates new UUID if file size changed (for new filename)
     *
     * Uses atomic operations:
     * 1. Copy new file first
     * 2. Update database second
     * 3. Delete old file only after successful database update
     *
     * Uses source field to protect non-import logos - only overwrites logos with source IMPORT_XLS.
     */
    public static function importLogo(
        Organisation $organisation,
        string $folderPath,
        string $filename,
        ?ImportLoggerHelper $logger = null,
        ?int $index = null
    ): void {
        $sourcePath = "$folderPath/$filename";

        // Gets existing logo (if any) - there's only one per organisation!
        /** @var Logo|null $existingLogo */
        $existingLogo = $organisation->logo;

        // Skips import if existing logo is NOT from import (protect user uploads and unknown sources)
        if ($existingLogo && $existingLogo->source !== LogoSource::IMPORT_XLS) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Skipping logo import - existing logo source: {$existingLogo->source->value}");
            }

            return;
        }

        // Reuses UUID if logo already exists, otherwise generates a new one:
        $uuid = $existingLogo ? $existingLogo->uuid : Str::uuid();

        // Ensures extension and mime type are determined from actual file data:
        $mimeType = Storage::disk('import')->mimeType($sourcePath);
        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg', 'image/pjpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => pathinfo($filename, PATHINFO_EXTENSION),
        };

        $destinationPath = "logos/$uuid.$extension";

        // Checks if the source file exists on the 'import' disk:
        if (! Storage::disk('import')->exists($sourcePath)) {
            if ($logger && $index !== null) {
                $logger->recordDebug($index, "Source logo not found: $sourcePath");
            }

            return;

        }

        // Fetches the source file size for comparison:
        $sourceFileSize = Storage::disk('import')->size($sourcePath);

        // Determines if a copy is needed:
        $needsCopy = true;
        $isNewFile = ! $existingLogo;
        $oldFilePath = null;

        if ($existingLogo && Storage::disk('media')->exists($destinationPath)) {
            $existingFileSize = Storage::disk('media')->size($destinationPath);

            // If the file size matches, skip the copy:
            if ($existingFileSize === $sourceFileSize) {
                $needsCopy = false;
            } else {
                // Store old file path for deletion AFTER successful database update:
                $oldFilePath = $destinationPath;
                // Generate a new UUID for the new file:
                $uuid = Str::uuid();
                $destinationPath = "logos/$uuid.$extension";
            }
        }

        // If copying is needed, copy the file and update UUID:
        if ($needsCopy) {
            $sourceFileContents = Storage::disk('import')->get($sourcePath);

            if ($sourceFileContents === null) {
                if ($logger && $index !== null) {
                    $logger->recordWarning($index, "Failed to read source file: $sourcePath");
                }

                return;

            }

            Storage::disk('media')->put($destinationPath, $sourceFileContents);
        }

        // Fetches metadata using native PHP functions (instead of Intervention):
        $imagePath = Storage::disk('media')->path($destinationPath);
        // Fetches image dimensions:
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            if ($logger && $index !== null) {
                $logger->recordWarning($index, "Failed to get image dimensions for: $imagePath");
            }

            return;

        }
        [$width, $height] = $imageInfo;
        // Fetches image size:
        $fileSize = Storage::disk('media')->size($destinationPath);

        // Updates or creates the logo entry:
        $organisation->logo()->updateOrCreate(
            ['organisation_id' => $organisation->id],
            [
                'uuid' => $uuid,
                'filename' => "$uuid.$extension",
                'original_filename' => $filename,
                'file_extension' => $extension,
                'mime_type' => $mimeType,
                'alt' => $organisation->name . ' logo',
                'width' => $width,
                'height' => $height,
                'size' => $fileSize,
                'has_transparency' => false,
                'background_color' => null,
                'source' => LogoSource::IMPORT_XLS,
            ]
        );

        // Delete old file ONLY after successful database update:
        if ($oldFilePath && Storage::disk('media')->exists($oldFilePath)) {
            Storage::disk('media')->delete($oldFilePath);
        }

        // Logs the result:
        if ($isNewFile) {
            $message = "Logo imported for the first time for '$organisation->name'";
        } elseif ($needsCopy) {
            $message = "Logo copied and UUID updated for '$organisation->name' (file size changed)";
        } else {
            $message = "Logo skipped for '$organisation->name' - already exists & file size unchanged";
        }
        if ($logger && $index !== null) {
            $logger->recordDebug($index, $message);
        } else {
            Log::debug($message);
        }

    }
}
