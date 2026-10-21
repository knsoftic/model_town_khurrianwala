<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Plot;
use App\Services\SeoService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::query()->get(['slug', 'is_active', 'updated_at'])->keyBy('slug');

        $urls = collect(NavigationItem::ROUTES)
            ->reject(fn ($meta) => $meta[1] && $pages->get($meta[1])?->is_active === false)
            ->map(fn ($meta, string $route) => [
                'loc' => route($route),
                'lastmod' => $pages->get($meta[1] ?? 'home')?->updated_at,
                'priority' => $route === 'home' ? '1.0' : '0.8',
            ])
            ->values();

        Plot::query()->active()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Plot $plot) => $urls->push(['loc' => route('plots.show', $plot), 'lastmod' => $plot->updated_at, 'priority' => '0.9']));

        Location::query()->active()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Location $location) => $urls->push(['loc' => route('locations.show', $location), 'lastmod' => $location->updated_at, 'priority' => '0.7']));

        return response()->view('front.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * robots.txt: everything open to crawlers plus any extra rules from Settings → SEO, or
     * everything blocked while "Show this website in search engines" is off.
     */
    public function robots(SeoService $seo): Response
    {
        $extra = trim(str_replace("\r", '', (string) setting('robots_txt_extra')));

        $lines = $seo->indexable()
            ? ['User-agent: *', 'Disallow:', ...($extra !== '' ? explode("\n", $extra) : []), '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

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
                    $plot->booking_amount ? 'booking '.money($plot->booking_amount) : ($plot->down_payment ? 'down payment '.money($plot->down_payment) : null),
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
