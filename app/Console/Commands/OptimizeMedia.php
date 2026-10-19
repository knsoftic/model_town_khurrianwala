<?php

namespace App\Console\Commands;

use App\Services\ResponsiveImages;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('media:optimize')]
#[Description('Create the resized WebP copies of every uploaded photo, so the first visitor after a deploy does not wait for them')]
class OptimizeMedia extends Command
{
    public function handle(ResponsiveImages $images): int
    {
        $files = collect(Storage::disk('public')->allFiles('uploads'))
            ->filter(fn (string $path) => preg_match('/\.(jpe?g|png|webp)$/i', $path))
            ->values();

        if ($files->isEmpty()) {
            $this->components->info('No uploaded photos found.');

            return self::SUCCESS;
        }

        $written = 0;

        $this->withProgressBar($files, function (string $path) use ($images, &$written) {
            $written += $images->prepare($path);
        });

        $this->newLine(2);
        $this->components->info("{$files->count()} photos checked, {$written} resized copies created.");

        return self::SUCCESS;
    }
}
