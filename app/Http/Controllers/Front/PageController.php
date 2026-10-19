<?php

namespace App\Http\Controllers\Front;

use App\Models\DevelopmentMilestone;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\Location;
use App\Models\Plot;
use App\Models\ProjectFeature;
use App\Models\Statistic;
use App\Models\Testimonial;
use App\Models\Video;
use Illuminate\View\View;

class PageController extends FrontController
{
    public function about(): View
    {
        $page = $this->page('about');

        return view('front.about', [
            'page' => $page,
            'statistics' => Statistic::query()->active()->where('group', 'about')->ordered()->get(),
            'features' => $page->setting('show_features', true) ? ProjectFeature::query()->active()->ordered()->get() : collect(),
            'video' => $page->setting('show_video', true) ? Video::query()->active()->orderByDesc('is_featured')->ordered()->first() : null,
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'About', 'image' => $page->image]),
        ]);
    }

    public function development(): View
    {
        $page = $this->page('development');
        $milestones = DevelopmentMilestone::query()->active()->ordered()->get();
        $album = GalleryAlbum::query()->active()->where('slug', 'development')
            ->with(['activeImages' => fn ($query) => $query->limit(6)])->first();

        return view('front.development', [
            'page' => $page,
            'milestones' => $milestones,
            'overall' => $milestones->isEmpty() ? 0 : (int) round($milestones->avg('percentage')),
            'album' => $album,
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Development']),
        ]);
    }

    public function gallery(): View
    {
        $page = $this->page('gallery');

        $albums = GalleryAlbum::query()->active()->ordered()
            ->whereHas('images', fn ($query) => $query->where('is_active', true))
            ->with('activeImages')->get();

        return view('front.gallery', [
            'page' => $page,
            'albums' => $albums,
            'videos' => Video::query()->active()->orderByDesc('is_featured')->ordered()->get(),
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Gallery']),
        ]);
    }

    public function testimonials(): View
    {
        $page = $this->page('testimonials');

        return view('front.testimonials', [
            'page' => $page,
            'testimonials' => Testimonial::query()->active()->with('plot')->orderByDesc('is_featured')->ordered()->get(),
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Testimonials']),
        ]);
    }

    public function faq(): View
    {
        $page = $this->page('faq');
        $faqs = Faq::query()->active()->ordered()->get();

        return view('front.faq', [
            'page' => $page,
            'groups' => $faqs->groupBy(fn (Faq $faq) => $faq->category ?: 'General'),
            'seo' => $this->seoFor($page, [
                'title' => $page->heading ?? 'FAQ',
                'schemas' => $faqs->isEmpty() ? [] : [[
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqs->map(fn (Faq $faq) => [
                        '@type' => 'Question',
                        'name' => $faq->question,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->answer)],
                    ])->all(),
                ]],
            ]),
        ]);
    }

    public function contact(): View
    {
        $page = $this->page('contact');

        return view('front.contact', [
            'page' => $page,
            'plots' => Plot::query()->active()->ordered()->get(['id', 'size_label', 'slug']),
            'location' => Location::query()->active()->orderByDesc('is_featured')->ordered()->first(),
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Contact']),
        ]);
    }
}
