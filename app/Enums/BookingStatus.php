<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Contacted = 'contacted';
    case OnHold = 'on_hold';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Contacted => 'Customer Contacted',
            self::OnHold => 'On Hold',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-sky-100 text-sky-800 ring-sky-600/20',
            self::Contacted => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20',
            self::OnHold => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Approved => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            self::Rejected => 'bg-rose-100 text-rose-800 ring-rose-600/20',
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
