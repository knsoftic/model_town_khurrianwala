<?php

namespace App\Enums;

enum PlotStatus: string
{
    case Available = 'available';
    case Limited = 'limited';
    case SoldOut = 'sold_out';
    case ComingSoon = 'coming_soon';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Limited => 'Limited',
            self::SoldOut => 'Sold Out',
            self::ComingSoon => 'Coming Soon',
        };
    }

    /**
     * Tailwind classes for the status badge (used on both frontend and admin).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-emerald-500/15 text-emerald-700 ring-emerald-600/30',
            self::Limited => 'bg-amber-500/15 text-amber-700 ring-amber-600/30',
            self::SoldOut => 'bg-rose-500/15 text-rose-700 ring-rose-600/30',
            self::ComingSoon => 'bg-sky-500/15 text-sky-700 ring-sky-600/30',
        };
    }

    public function isBookable(): bool
    {
        return in_array($this, [self::Available, self::Limited], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
