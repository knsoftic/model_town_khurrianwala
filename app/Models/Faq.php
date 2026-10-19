<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'category', 'sort_order', 'is_active'])]
class Faq extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
