<?php

namespace App\Http\Controllers\Admin;

use App\Models\Statistic;

class StatisticController extends ContentItemController
{
    protected string $model = Statistic::class;

    protected string $route = 'statistics';

    protected string $view = 'statistics';

    protected string $singular = 'Statistic';

    protected bool $hasImage = false;

    protected function defaults(): array
    {
        return ['group' => 'intro'];
    }
}
