<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Planned => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            self::InProgress => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Completed => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
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
