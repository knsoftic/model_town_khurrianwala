<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'icon', 'image', 'sort_order', 'is_active'])]
class ProjectFeature extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
