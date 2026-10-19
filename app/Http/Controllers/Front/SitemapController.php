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

    /**
     * llms.txt (llmstxt.org): a plain Markdown summary that AI assistants and search agents can read.
     */
    public function llms(): Response
    {
        $line = fn (mixed $text) => trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $text)));
        $disabled = Page::query()->where('is_active', false)->pluck('slug')->all();

        $lines = ['# '.$line(setting('site_name')), '', '> '.$line(setting('seo_description') ?: setting('site_tagline')), ''];

        $contact = array_filter([
            setting('office_address') ? 'Office: '.$line(setting('office_address')) : null,
            setting('phone') ? 'Phone: '.$line(setting('phone')) : null,
            setting('whatsapp_number') ? 'WhatsApp: '.$line(setting('whatsapp_number')) : null,
            setting('email') ? 'Email: '.$line(setting('email')) : null,
            setting('office_hours') ? 'Office hours: '.$line(setting('office_hours')) : null,
        ]);

        if ($contact) {
            array_push($lines, ...array_map(fn (string $item) => '- '.$item, $contact));
            $lines[] = '';
        }

        $plots = Plot::query()->active()->ordered()->get();

        if ($plots->isNotEmpty()) {
            array_push($lines, '## Plots', '');

            foreach ($plots as $plot) {
                $facts = array_filter([
                    $plot->displayPrice() ? 'from '.money($plot->displayPrice()) : null,
                    $plot->booking_amount ? 'booking '.money($plot->booking_amount) : null,
                    $plot->installment_amount ? 'installment '.money($plot->installment_amount).' '.$plot->frequencySuffix() : null,
                    $plot->status->label(),
                    $plot->is_demo_price ? 'demo price, confirm with the sales office' : null,
                ]);
                $lines[] = '- ['.$line($plot->title).']('.route('plots.show', $plot).'): '.implode(', ', $facts);
            }

            $lines[] = '';
        }

        array_push($lines, '## Pages', '');

        foreach (NavigationItem::ROUTES as $route => [$label, $page]) {
            if (! $page || ! in_array($page, $disabled, true)) {
                $lines[] = '- ['.$label.']('.route($route).')';
            }
        }

        array_push($lines, '', '## Optional', '', '- [Sitemap]('.route('sitemap').')', '- [Plot catalogue (JSON)]('.route('api.plots.index').')');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
