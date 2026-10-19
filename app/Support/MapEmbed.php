<?php

namespace App\Support;

/**
 * Accepts a Google Maps iframe snippet or URL and returns a safe embed URL.
 * Anything that is not an https google.com/maps URL is rejected, so admins
 * can paste the iframe code without being able to inject markup.
 */
final class MapEmbed
{
    public static function extract(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (stripos($input, '<iframe') !== false) {
            if (! preg_match('~\bsrc\s*=\s*["\']([^"\']+)["\']~i', $input, $matches)) {
                return null;
            }
            $input = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        return self::isAllowed($input) ? $input : null;
    }

    public static function isAllowed(string $url): bool
    {
        $parts = parse_url($url);

        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        $isGoogle = (bool) preg_match('~(^|\.)google\.[a-z.]{2,6}$~', $host);

        return $isGoogle && str_starts_with($parts['path'] ?? '', '/maps');
    }
}
