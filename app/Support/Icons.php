<?php

namespace App\Support;

/**
 * Inline SVG icon registry for <x-icon name="..." />.
 * Names come from the Lucide set (resources/icons/lucide.php) plus brand glyphs (brands.php).
 */
final class Icons
{
    /**
     * Suggestions shown in the admin icon picker (any icon name from the set is accepted).
     */
    public const CURATED = [
        'shield-check', 'shield-user', 'cctv', 'fence', 'brick-wall', 'trees', 'tree-pine', 'mosque', 'zap', 'droplets',
        'waves-horizontal', 'road', 'store', 'lamp', 'house', 'houses', 'building', 'land-plot', 'map-pin', 'navigation',
        'car-front', 'plane', 'school', 'hospital', 'graduation-cap', 'shopping-bag', 'dumbbell', 'baby', 'sun', 'wifi',
        'flame', 'plug', 'hard-hat', 'construction', 'truck', 'key-round', 'wallet', 'banknote', 'hand-coins', 'calculator',
        'calendar-days', 'badge-check', 'award', 'smile', 'users-round', 'heart-handshake', 'star', 'sparkles', 'clock', 'layout-grid',
    ];

    /**
     * @var array<string, string>|null
     */
    protected static ?array $icons = null;

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$icons ??= array_merge(
            require resource_path('icons/lucide.php'),
            require resource_path('icons/brands.php'),
        );
    }

    public static function exists(?string $name): bool
    {
        return $name !== null && array_key_exists($name, self::all());
    }

    /**
     * Inner SVG markup for an icon (falls back to a neutral dot).
     */
    public static function svg(?string $name): string
    {
        return self::all()[$name ?? ''] ?? self::all()['circle-dot'] ?? '';
    }
}
