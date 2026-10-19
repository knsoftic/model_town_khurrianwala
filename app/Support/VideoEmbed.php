<?php

namespace App\Support;

/**
 * Converts YouTube / Vimeo page URLs into privacy-friendly embed URLs.
 */
final class VideoEmbed
{
    public static function youtubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $url, $matches) ? $matches[1] : null;
    }

    public static function vimeoId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d+)~', $url, $matches) ? $matches[1] : null;
    }

    public static function embedUrl(?string $url, bool $autoplay = false): ?string
    {
        if ($id = self::youtubeId($url)) {
            return "https://www.youtube-nocookie.com/embed/{$id}?rel=0&modestbranding=1".($autoplay ? '&autoplay=1' : '');
        }

        if ($id = self::vimeoId($url)) {
            return "https://player.vimeo.com/video/{$id}?dnt=1".($autoplay ? '&autoplay=1' : '');
        }

        return null;
    }

    public static function thumbnailUrl(?string $url): ?string
    {
        $id = self::youtubeId($url);

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : null;
    }

    public static function isSupported(?string $url): bool
    {
        return self::youtubeId($url) !== null || self::vimeoId($url) !== null;
    }
}
