<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.social-links.index', ['links' => SocialLink::query()->ordered()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        SocialLink::query()->create($this->validated($request) + ['sort_order' => SocialLink::nextSortOrder()]);

        return back()->with('success', 'Social link added.');
    }

    public function update(Request $request, SocialLink $socialLink): RedirectResponse
    {
        $socialLink->update($this->validated($request));

        return back()->with('success', $socialLink->displayLabel().' link saved.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return back()->with('success', 'Social link removed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'platform' => ['required', Rule::in(array_keys(SocialLink::PLATFORMS))],
            'label' => ['nullable', 'string', 'max:60'],
            'url' => ['required', 'url', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
