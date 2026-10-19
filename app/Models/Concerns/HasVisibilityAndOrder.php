<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared "is_active" + "sort_order" behaviour for admin-managed content.
 */
trait HasVisibilityAndOrder
{
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_active'), true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('id'));
    }

    public static function nextSortOrder(): int
    {
        return ((int) static::query()->max('sort_order')) + 1;
    }
}
