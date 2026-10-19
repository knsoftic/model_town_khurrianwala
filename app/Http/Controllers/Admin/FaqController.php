<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;

class FaqController extends ContentItemController
{
    protected string $model = Faq::class;

    protected string $route = 'faqs';

    protected string $view = 'faqs';

    protected string $singular = 'FAQ';

    protected bool $hasImage = false;
}
