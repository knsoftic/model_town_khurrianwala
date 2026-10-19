<?php

namespace App\Support;

/**
 * Site-ownership meta tags (Google Search Console, Bing, Facebook, Pinterest…).
 *
 * Admins may paste a whole <meta> tag or only its content value. Pasted tags are
 * rebuilt from their name/property and content alone, so nothing else — scripts,
 * http-equiv redirects, extra attributes — can reach the page <head>.
 */
final class VerificationTags
{
    /** Names the website already manages, which a pasted tag may not override. */
    public const RESERVED = [
        'viewport', 'csrf-token', 'robots', 'googlebot', 'bingbot', 'description', 'keywords',
        'referrer', 'theme-color', 'author', 'generator',
    ];

    protected const NAME = '/^[A-Za-z0-9_.:-]{1,100}$/';

    protected const CODE = '/^[A-Za-z0-9_.:=+\/-]{1,250}$/';

    /**
     * The verification code from a pasted meta tag or a bare code, or null when it is not valid.
     */
    public static function code(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (str_contains($input, '<') || stripos($input, 'content=') !== false) {
            $input = self::attributes($input)['content'] ?? '';
        }

        $input = trim($input, " \t\n\r\"'");

        return preg_match(self::CODE, $input) ? $input : null;
    }

    /**
     * Every acceptable <meta name|property="…" content="…"> tag found in pasted HTML.
     *
     * @return array<int, array{attribute: string, name: string, content: string}>
     */
    public static function parse(?string $html): array
    {
        preg_match_all('/<meta\b[^>]*>/i', (string) $html, $matches);

        $tags = [];

        foreach ($matches[0] as $tag) {
            $attributes = self::attributes($tag);
            $attribute = isset($attributes['name']) ? 'name' : (isset($attributes['property']) ? 'property' : null);

            if (! $attribute || isset($attributes['http-equiv']) || ! isset($attributes['content'])) {
                continue;
            }

            $name = trim($attributes[$attribute]);
            $content = trim($attributes['content']);

            if (! preg_match(self::NAME, $name) || $content === '' || mb_strlen($content) > 500 || self::reserved($name)) {
                continue;
            }

            $tags[] = ['attribute' => $attribute, 'name' => $name, 'content' => $content];
        }

        return $tags;
    }

    /**
     * Pasted HTML reduced to clean meta tags, one per line, for storing.
     */
    public static function normalise(?string $html): string
    {
        return collect(self::parse($html))
            ->map(fn (array $tag) => '<meta '.$tag['attribute'].'="'.e($tag['name']).'" content="'.e($tag['content']).'">')
            ->unique()
            ->implode("\n");
    }

    /**
     * Tags to print in <head>: the named fields (config "meta" key) plus the custom list.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @param  callable(string): mixed  $value
     * @return array<int, array{attribute: string, name: string, content: string}>
     */
    public static function forSite(array $fields, callable $value): array
    {
        $tags = [];

        foreach ($fields as $key => $field) {
            if (isset($field['meta']) && ($code = self::code((string) $value($key)))) {
                $tags[] = ['attribute' => 'name', 'name' => $field['meta'], 'content' => $code];
            }
        }

        $tags = array_merge($tags, self::parse((string) $value('custom_meta_tags')));

        return collect($tags)->unique(fn (array $tag) => strtolower($tag['name']).'|'.$tag['content'])->values()->all();
    }

    protected static function reserved(string $name): bool
    {
        $name = strtolower($name);

        return in_array($name, self::RESERVED, true) || str_starts_with($name, 'og:') || str_starts_with($name, 'twitter:');
    }

    /**
     * @return array<string, string>
     */
    protected static function attributes(string $tag): array
    {
        preg_match_all('/([a-z][a-z0-9:_-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/i', $tag, $pairs, PREG_SET_ORDER);

        $attributes = [];

        foreach ($pairs as $pair) {
            $raw = ($pair[4] ?? '') !== '' ? $pair[4] : (($pair[3] ?? '') !== '' ? $pair[3] : ($pair[2] ?? ''));
            $attributes[strtolower($pair[1])] ??= html_entity_decode($raw, ENT_QUOTES | ENT_HTML5);
        }

        return $attributes;
    }
}
