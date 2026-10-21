<?php

namespace App\Support;

/**
 * Comma-separated keyword lists for the keywords meta tag (Settings → SEO and each page's SEO box).
 */
final class Keywords
{
    /**
     * Keywords from text separated by commas or new lines: trimmed, blanks removed and
     * repeats dropped (ignoring case), in the order they were typed.
     *
     * @return array<int, string>
     */
    public static function list(?string $value): array
    {
        $keywords = [];

        foreach (preg_split('/[,\r\n]+/', (string) $value) as $keyword) {
            $keyword = trim((string) preg_replace('/\s+/', ' ', $keyword));

            if ($keyword !== '' && ! isset($keywords[mb_strtolower($keyword)])) {
                $keywords[mb_strtolower($keyword)] = $keyword;
            }
        }

        return array_values($keywords);
    }

    public static function normalise(?string $value): string
    {
        return implode(', ', self::list($value));
    }

    /**
     * The keywords of $value followed by any of $extra it does not have yet.
     *
     * @param  array<int, string>  $extra
     */
    public static function merge(?string $value, array $extra): string
    {
        return self::normalise(implode(',', [...self::list($value), ...$extra]));
    }
}
