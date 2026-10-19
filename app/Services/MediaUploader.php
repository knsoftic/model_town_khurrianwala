<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads on the public disk under random names. Raster images are
 * re-encoded with GD, which strips metadata and any non-image payload and
 * caps the longest side at cms.uploads.max_dimension.
 */
class MediaUploader
{
    protected const DISK = 'public';

    public function store(UploadedFile $file, string $folder): string
    {
        $folder = 'uploads/'.trim($folder, '/');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) && $encoded = $this->reencode($file->getRealPath(), $extension)) {
            $path = $folder.'/'.Str::random(40).'.'.$extension;
            Storage::disk(self::DISK)->put($path, $encoded);

            return $path;
        }

        return $file->store($folder, self::DISK);
    }

    /**
     * Handle a single-file form field: upload a replacement, remove on request, or keep the current file.
     */
    public function replace(?UploadedFile $file, ?string $current, string $folder, bool $remove = false): ?string
    {
        if ($file) {
            $this->delete($current);

            return $this->store($file, $folder);
        }

        if ($remove) {
            $this->delete($current);

            return null;
        }

        return $current;
    }

    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    protected function reencode(string $source, string $extension): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($source));

        if (! $image) {
            return null;
        }

        $max = (int) config('cms.uploads.max_dimension', 2400);
        $width = imagesx($image);
        $height = imagesy($image);

        if (max($width, $height) > $max) {
            $ratio = $max / max($width, $height);
            $image = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio)) ?: $image;
        }

        ob_start();

        if ($extension === 'png') {
            imagesavealpha($image, true);
            imagepng($image, null, 8);
        } elseif ($extension === 'webp') {
            imagesavealpha($image, true);
            imagewebp($image, null, 85);
        } else {
            imagejpeg($image, null, 85);
        }

        $output = ob_get_clean();

        return $output ?: null;
    }
}
