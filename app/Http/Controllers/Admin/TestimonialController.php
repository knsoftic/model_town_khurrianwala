<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Plot;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use HandlesMedia;

    public function index(): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::query()->with('plot')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Testimonial(['rating' => 5, 'is_active' => true, 'client_title' => 'Happy Client', 'sort_order' => Testimonial::nextSortOrder()]));
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $this->save($request, new Testimonial);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return $this->form($testimonial);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->save($request, $testimonial);

        return redirect()->route('admin.testimonials.index')->with('success', "Testimonial from {$testimonial->client_name} saved.");
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    protected function form(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', [
            'testimonial' => $testimonial,
            'plots' => Plot::query()->ordered()->pluck('size_label', 'id'),
        ]);
    }

    protected function save(TestimonialRequest $request, Testimonial $testimonial): void
    {
        $data = $request->safe()->except('client_image');
        $data['client_image'] = $this->media($request, 'client_image', $testimonial->client_image, 'testimonials');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $testimonial->fill($data)->save();
    }
}
