<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'label', 'value', 'prefix', 'suffix', 'icon', 'sort_order', 'is_active'])]
class Statistic extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    /**
     * @var array<string, string>
     */
    public const GROUPS = [
        'intro' => 'Homepage — Project Introduction',
        'about' => 'About Page',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Numeric part used by the animated counter (e.g. "5,000" -> 5000).
     */
    public function numericValue(): ?float
    {
        $clean = str_replace(',', '', $this->value);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
