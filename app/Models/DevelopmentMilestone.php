<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('development_progress')]
#[Fillable(['title', 'percentage', 'description', 'icon', 'image', 'status', 'sort_order', 'is_active'])]
class DevelopmentMilestone extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    protected function casts(): array
    {
        return [
            'status' => MilestoneStatus::class,
            'percentage' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
