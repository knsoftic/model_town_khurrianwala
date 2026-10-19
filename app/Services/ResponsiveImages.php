<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Serves uploaded photos at the size they are shown. Resized WebP copies are
 * written once to the public disk (variants/<width>/<original path>.webp) and
 * reused on every later request; the originals are never touched. SVGs,
 * external URLs and anything GD cannot read fall back to the original file.
 */
class ResponsiveImages
{
    /**
     * The widths the templates ask for; media:optimize prepares all of them ahead of
     * the first visitor. Keep views on these sizes so no copy is made on a page view.
     */
    public const WIDTHS = [96, 192, 320, 480, 640, 768, 960, 1280, 1600, 1920];

    protected const DISK = 'public';

    protected const ROOT = 'variants';

    protected const RESIZABLE = ['jpg', 'jpeg', 'png', 'webp'];

    /** Images above this many pixels are skipped so decoding cannot exhaust memory. */
    protected const MAX_PIXELS = 30_000_000;

    protected const CACHE_KEY = 'media.catalog';

    /**
     * Pixel sizes of the originals, keyed by path — one cache read per request.
     *
     * @var array<string, array<string, int|bool>>|null
     */
    protected ?array $catalog = null;

    /**
     * <img> attributes (src, srcset, sizes, width, height) for an uploaded image.
     * Pass $height to size a fixed-height image such as the logo: the widths are
     * then worked out from its aspect ratio for 1x, 2x and 3x screens.
     *
     * @param  array<int, int>  $widths
     * @return array<string, string|int>
     */
    public function attributes(?string $path, string $sizes = '100vw', array $widths = [480, 768, 960, 1600], ?int $height = null): array
    {
        if (blank($path)) {
            return [];
        }

        if ($this->isExternal($path)) {
            return ['src' => $path];
        }

        $info = $this->info($path);
        $original = Storage::disk(self::DISK)->url($path);

        if (! $info) {
            return ['src' => $original];
        }

        $dimensions = ['width' => $info['width'], 'height' => $info['height']];

        if ($height && $info['height'] > 0) {
            $cssWidth = (int) ceil($height * $info['width'] / $info['height']);
            $widths = [$cssWidth, $cssWidth * 2, $cssWidth * 3];
            $sizes = $cssWidth.'px';
            $dimensions = ['width' => $cssWidth, 'height' => $height];
        }

        $variants = $info['resizable'] ? $this->variants($path, $info, $widths) : [];

        if (! $variants) {
            return ['src' => $original] + $dimensions;
        }

        return [
            'src' => end($variants),
            'srcset' => collect($variants)->map(fn (string $url, int $width) => "{$url} {$width}w")->implode(', '),
            'sizes' => $sizes,
        ] + $dimensions;
    }

    /**
     * URL of a single resized copy (e.g. for a lightbox, a video poster or a preload hint).
     */
    public function url(?string $path, int $width = 1600): ?string
    {
        if (blank($path)) {
            return null;
        }

        if ($this->isExternal($path)) {
            return $path;
        }

        $info = $this->info($path);
        $variants = $info && $info['resizable'] ? $this->variants($path, $info, [$width]) : [];

        return $variants ? end($variants) : Storage::disk(self::DISK)->url($path);
    }

    /**
     * Create every missing copy of $path at the given widths. Returns how many were written.
     *
     * @param  array<int, int>  $widths
     */
    public function prepare(string $path, array $widths = self::WIDTHS): int
    {
        $info = $this->info($path);

        if (! $info || ! $info['resizable']) {
            return 0;
        }

        $missing = array_filter($this->targets($info, $widths), fn (int $width) => ! $this->exists($path, $width));

        return count($this->generate($path, $missing));
    }

    /**
     * Remove every resized copy of an original (called when the original is deleted).
     */
    public function forget(?string $path): void
    {
        if (blank($path) || $this->isExternal($path)) {
            return;
        }

        $disk = Storage::disk(self::DISK);

        foreach ($disk->directories(self::ROOT) as $directory) {
            $disk->delete($directory.'/'.$this->variantName($path));
        }

        $this->catalog ??= Cache::get(self::CACHE_KEY, []);
        unset($this->catalog[$path]);
        Cache::forever(self::CACHE_KEY, $this->catalog);
    }

    /**
     * @param  array{width: int, height: int, resizable: bool}  $info
     * @param  array<int, int>  $widths
     * @return array<int, string> width => URL, smallest first
     */
    protected function variants(string $path, array $info, array $widths): array
    {
        $targets = $this->targets($info, $widths);
        $missing = array_values(array_filter($targets, fn (int $width) => ! $this->exists($path, $width)));

        if ($missing) {
            $written = $this->generate($path, $missing);
            $targets = array_values(array_filter($targets, fn (int $width) => ! in_array($width, $missing, true) || in_array($width, $written, true)));
        }

        $disk = Storage::disk(self::DISK);

        return collect($targets)->mapWithKeys(fn (int $width) => [$width => $disk->url($this->variantPath($path, $width))])->all();
    }

    /**
     * Requested widths that are smaller than the original, plus the original
     * width itself when a larger size was asked for — never upscaled.
     *
     * @param  array{width: int, height: int, resizable: bool}  $info
     * @param  array<int, int>  $widths
     * @return array<int, int>
     */
    protected function targets(array $info, array $widths): array
    {
        $targets = [];

        foreach ($widths as $width) {
            $targets[] = min(max(16, (int) $width), $info['width']);
        }

        $targets = array_values(array_unique($targets));
        sort($targets);

        return $targets;
    }

    /**
     * Decode the original once and write each missing width. Returns the widths written.
     *
     * @param  array<int, int>  $widths
     * @return array<int, int>
     */
    protected function generate(string $path, array $widths): array
    {
        if (! $widths || ! function_exists('imagecreatefromstring')) {
            return [];
        }

        $disk = Storage::disk(self::DISK);
        $source = null;
        $written = [];

        try {
            $source = @imagecreatefromstring((string) $disk->get($path));

            if (! $source) {
                return [];
            }

            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            foreach ($widths as $width) {
                $height = max(1, (int) round($sourceHeight * $width / $sourceWidth));
                $canvas = imagecreatetruecolor($width, $height);
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

                if ($this->write($this->variantPath($path, $width), $canvas)) {
                    $written[] = $width;
                }

                imagedestroy($canvas);
            }
        } catch (Throwable $e) {
            report($e);
        } finally {
            if ($source) {
                imagedestroy($source);
            }
        }

        return $written;
    }

    /**
     * Encode to a temporary file and move it into place, so a visitor never
     * receives a half-written image while another request is still saving it.
     */
    protected function write(string $variant, \GdImage $image): bool
    {
        $target = Storage::disk(self::DISK)->path($variant);
        $directory = dirname($target);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return false;
        }

        $temporary = $target.'.'.bin2hex(random_bytes(4)).'.tmp';
        $saved = $this->supportsWebp() ? imagewebp($image, $temporary, 78) : imagepng($image, $temporary, 8);

        if (! $saved || ! @rename($temporary, $target)) {
            @unlink($temporary);

            return false;
        }

        return true;
    }

    /**
     * Pixel size of the original, cached forever (uploads get a new random name
     * when replaced, so a path always points at the same picture).
     *
     * @return array{width: int, height: int, resizable: bool}|null
     */
    protected function info(string $path): ?array
    {
        $this->catalog ??= Cache::get(self::CACHE_KEY, []);

        if (! array_key_exists($path, $this->catalog)) {
            $this->catalog[$path] = $this->inspect($path);
            Cache::forever(self::CACHE_KEY, $this->catalog);
        }

        $info = $this->catalog[$path];

        return isset($info['width'], $info['height']) ? $info : null;
    }

    /**
     * @return array<string, int|bool>
     */
    protected function inspect(string $path): array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($path)) {
            return ['missing' => true];
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            return $this->svgSize((string) $disk->get($path)) + ['resizable' => false];
        }

        $size = @getimagesize($disk->path($path));

        if (! $size || ! $size[0] || ! $size[1]) {
            return ['missing' => true];
        }

        return [
            'width' => (int) $size[0],
            'height' => (int) $size[1],
            'resizable' => in_array($extension, self::RESIZABLE, true) && $size[0] * $size[1] <= self::MAX_PIXELS,
        ];
    }

    /**
     * @return array{width?: int, height?: int}
     */
    protected function svgSize(string $svg): array
    {
        $head = substr($svg, 0, 2000);

        if (preg_match('/viewBox=["\']\s*[\d.-]+[\s,]+[\d.-]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $head, $box) && (float) $box[1] > 0) {
            return ['width' => (int) round((float) $box[1]), 'height' => (int) round((float) $box[2])];
        }

        if (preg_match('/\swidth=["\']([\d.]+)(px)?["\']/i', $head, $w) && preg_match('/\sheight=["\']([\d.]+)(px)?["\']/i', $head, $h)) {
            return ['width' => (int) round((float) $w[1]), 'height' => (int) round((float) $h[1])];
        }

        return [];
    }

    protected function exists(string $path, int $width): bool
    {
        return is_file(Storage::disk(self::DISK)->path($this->variantPath($path, $width)));
    }

    protected function variantPath(string $path, int $width): string
    {
        return self::ROOT.'/'.$width.'/'.$this->variantName($path);
    }

    protected function variantName(string $path): string
    {
        $extension = $this->supportsWebp() ? 'webp' : 'png';

        return preg_replace('/\.[^.\/]+$/', '', ltrim($path, '/')).'.'.$extension;
    }

    protected function supportsWebp(): bool
    {
        return function_exists('imagewebp') && (imagetypes() & IMG_WEBP);
    }

    protected function isExternal(string $path): bool
    {
        return (bool) preg_match('~^(https?:)?//~i', $path);
    }
}
