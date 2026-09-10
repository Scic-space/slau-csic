<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageOptimizer
{
    private const MAX_SOURCE_PIXELS = 40_000_000;

    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ];

    public function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $maxHeight = 1920,
        int $quality = 82,
    ): string {
        $imageInfo = @getimagesize($file->getRealPath());

        if ($imageInfo === false || ! in_array($imageInfo['mime'], self::SUPPORTED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'image' => 'The uploaded file must be a valid JPEG, PNG, WebP, or AVIF image.',
            ]);
        }

        if ($imageInfo[0] * $imageInfo[1] > self::MAX_SOURCE_PIXELS) {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image dimensions are too large.',
            ]);
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents === false ? false : @imagecreatefromstring($contents);

        if ($source === false) {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be processed.',
            ]);
        }

        [$sourceWidth, $sourceHeight] = $imageInfo;
        $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $optimized = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($optimized === false) {
            imagedestroy($source);

            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be optimized.',
            ]);
        }

        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        $transparent = imagecolorallocatealpha($optimized, 0, 0, 0, 127);
        imagefilledrectangle($optimized, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($optimized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        ob_start();
        $encoded = imagewebp($optimized, null, $quality);
        $webp = ob_get_clean();

        imagedestroy($optimized);
        imagedestroy($source);

        if (! $encoded || ! is_string($webp) || $webp === '') {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be encoded for the web.',
            ]);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }
}
