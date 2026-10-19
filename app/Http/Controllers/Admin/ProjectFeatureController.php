<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProjectFeature;

class ProjectFeatureController extends ContentItemController
{
    protected string $model = ProjectFeature::class;

    protected string $route = 'features';

    protected string $view = 'features';

    protected string $singular = 'Feature';

    protected bool $hasImage = true;
}
