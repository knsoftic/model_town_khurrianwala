<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Plot;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $disabled = Page::query()->where('is_active', false)->pluck('slug')->all();

        $urls = collect(NavigationItem::ROUTES)
            ->reject(fn ($meta) => $meta[1] && in_array($meta[1], $disabled, true))
            ->keys()
            ->map(fn (string $route) => ['loc' => route($route), 'lastmod' => null, 'priority' => $route === 'home' ? '1.0' : '0.8']);

        Plot::query()->active()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Plot $plot) => $urls->push(['loc' => route('plots.show', $plot), 'lastmod' => $plot->updated_at, 'priority' => '0.9']));

        Location::query()->active()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Location $location) => $urls->push(['loc' => route('locations.show', $location), 'lastmod' => $location->updated_at, 'priority' => '0.7']));

        return response()->view('front.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *', 'Disallow:', '', 'Sitemap: '.route('sitemap')];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
