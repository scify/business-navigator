<?php

namespace App\Models;

use App\Enums\LogoSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Logo extends Model
{
    protected $fillable = [
        'uuid',
        'organisation_id',
        'filename',
        'original_filename',
        'file_extension',
        'mime_type',
        'alt',
        'width',
        'height',
        'size',
        'has_transparency',
        'background_color',
        'source',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'integer',
        'has_transparency' => 'boolean',
        'source' => LogoSource::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (Logo $logo) {
            // Ensure the file is deleted from the 'media' disk:
            if ($logo->filename && Storage::disk('media')->exists('logos/' . $logo->filename)) {
                Storage::disk('media')->delete('logos/' . $logo->filename);
            }
        });
    }

    /**
     * @return BelongsTo<Organisation, $this>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->filename
            ? Storage::disk('media')->url('/logos/' . $this->filename)
            : null;
    }

    /*
    public static function detectBackgroundColor(string $filePath): ?string
    {
        $image = Image::make(Storage::disk('public')->path($filePath));

        $topLeft = $image->pickColor(0, 0, 'hex');
        $topRight = $image->pickColor($image->width() - 1, 0, 'hex');
        $bottomLeft = $image->pickColor(0, $image->height() - 1, 'hex');
        $bottomRight = $image->pickColor($image->width() - 1, $image->height() - 1, 'hex');

        $corners = [$topLeft, $topRight, $bottomLeft, $bottomRight];

        function isWhiteOrBlack($hex)
        {
            [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
            $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

            return $luminance > 0.9 ? 'white' : ($luminance < 0.1 ? 'black' : 'mixed');
        }

        return isWhiteOrBlack($corners[0]);
    }
    */

}
