<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\SeoService;
use App\Support\SeoData;
use Illuminate\Database\Eloquent\Model;

abstract class FrontController extends Controller
{
    public function __construct(protected SeoService $seo) {}

    /**
     * Admin-managed page record (heading, banner, SEO). Disabled pages return 404.
     */
    protected function page(string $slug): Page
    {
        $page = Page::query()->with('seo')->where('slug', $slug)->first();

        abort_if($page && ! $page->is_active, 404);

        return $page ?? new Page(['slug' => $slug, 'name' => str($slug)->headline()->toString(), 'is_active' => true]);
    }

    /**
     * @param  array<string, mixed>  $fallback
     */
    protected function seoFor(?Model $model, array $fallback = []): SeoData
    {
        return $this->seo->make($model, $fallback);
    }
}
