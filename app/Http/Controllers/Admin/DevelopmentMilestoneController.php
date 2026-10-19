<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MilestoneStatus;
use App\Models\DevelopmentMilestone;

class DevelopmentMilestoneController extends ContentItemController
{
    protected string $model = DevelopmentMilestone::class;

    protected string $route = 'development';

    protected string $view = 'development';

    protected string $singular = 'Milestone';

    protected bool $hasImage = true;

    protected function defaults(): array
    {
        return ['status' => MilestoneStatus::InProgress, 'percentage' => 0];
    }
}
