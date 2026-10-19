<?php

namespace App\Http\Controllers\Front;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Plot;
use App\Models\ProjectFeature;
use App\Models\Testimonial;
use Illuminate\View\View;

class PlotController extends FrontController
{
    public function index(): View
    {
        $page = $this->page('plots');

        $plots = Plot::query()->active()->ordered()->with(['location', 'activePlans.items'])->get();

        return view('front.plots.index', [
            'page' => $page,
            'plots' => $plots,
            'categories' => $plots->pluck('category')->unique()->values(),
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Plot Sizes']),
        ]);
    }

    public function show(Plot $plot): View
    {
        abort_unless($plot->is_active, 404);

        $plot->load(['location', 'images', 'activePlans.items', 'seo']);

        $related = Plot::query()->active()->ordered()->whereKeyNot($plot->id)->with('activePlans.items')->limit(3)->get();

        return view('front.plots.show', [
            'plot' => $plot,
            'related' => $related,
            'banner' => Page::query()->where('slug', 'plots')->value('banner_image'),
            'features' => ProjectFeature::query()->active()->ordered()->limit(8)->get(),
            'testimonials' => Testimonial::query()->active()->with('plot')->where('plot_id', $plot->id)->ordered()->limit(3)->get(),
            'faqs' => Faq::query()->active()->ordered()->limit(5)->get(),
            'seo' => $this->seoFor($plot, [
                'title' => $plot->title,
                'description' => $plot->short_description,
                'image' => $plot->image,
                'type' => 'product',
                'schemas' => [$this->productSchema($plot)],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function productSchema(Plot $plot): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $plot->title,
            'description' => $plot->short_description,
            'image' => $plot->image ? url(media_url($plot->image)) : null,
            'category' => 'Residential Plot',
            'offers' => $plot->displayPrice() ? [
                '@type' => 'Offer',
                'priceCurrency' => 'PKR',
                'price' => (float) $plot->displayPrice(),
                'availability' => $plot->isBookable() ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
                'url' => route('plots.show', $plot),
            ] : null,
        ]);
    }
}
