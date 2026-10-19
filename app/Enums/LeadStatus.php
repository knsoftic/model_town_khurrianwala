<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Interested = 'interested';
    case FollowUp = 'follow_up';
    case Booked = 'booked';
    case Converted = 'converted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Interested => 'Interested',
            self::FollowUp => 'Follow Up',
            self::Booked => 'Booked',
            self::Converted => 'Converted',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-sky-100 text-sky-800 ring-sky-600/20',
            self::Contacted => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20',
            self::Interested => 'bg-violet-100 text-violet-800 ring-violet-600/20',
            self::FollowUp => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Booked => 'bg-teal-100 text-teal-800 ring-teal-600/20',
            self::Converted => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            self::Cancelled => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
