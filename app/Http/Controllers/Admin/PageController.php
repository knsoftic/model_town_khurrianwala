<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    use HandlesMedia;

    /**
     * Public URL for each page slug.
     *
     * @var array<string, string>
     */
    public const ROUTES = [
        'home' => 'home', 'about' => 'about', 'plots' => 'plots.index', 'installment-plans' => 'installment-plans',
        'locations' => 'locations.index', 'development' => 'development', 'gallery' => 'gallery',
        'testimonials' => 'testimonials', 'faq' => 'faq', 'contact' => 'contact', 'book-plot' => 'book-plot',
    ];

    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::query()->orderBy('id')->get()]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page->load('seo')]);
    }

    public function update(PageRequest $request, Page $page, MediaUploader $media): RedirectResponse
    {
        $images = collect($page->images ?? [])
            ->reject(function ($path) use ($request, $media) {
                $remove = in_array($path, (array) $request->input('remove_gallery', []), true);
                if ($remove) {
                    $media->delete($path);
                }

                return $remove;
            })
            ->values();

        foreach ($request->file('gallery', []) as $file) {
            $images->push($media->store($file, 'pages'));
        }

        $page->fill($request->safe()->only(['name', 'eyebrow', 'heading', 'subheading', 'video_url', 'cta_text', 'cta_url']));
        $page->content = $this->cleanHtml($request->input('content'));
        $page->is_active = $page->slug === 'home' ? true : $request->boolean('is_active');
        $page->settings = collect(Page::SETTINGS[$page->slug] ?? [])->mapWithKeys(fn ($label, $key) => [$key => $request->boolean("settings.{$key}")])->all();
        $page->banner_image = $this->media($request, 'banner_image', $page->banner_image, 'pages');
        $page->image = $this->media($request, 'image', $page->image, 'pages');
        $page->images = $images->all();
        $page->save();

        $this->saveSeo($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', "{$page->name} page saved.");
    }
}
