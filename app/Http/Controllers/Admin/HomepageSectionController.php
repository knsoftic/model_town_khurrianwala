<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSectionRequest;
use App\Models\HomepageSection;
use App\Services\MediaUploader;
use App\Support\SectionFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    use HandlesMedia;

    public function index(): View
    {
        return view('admin.sections.index', [
            'sections' => HomepageSection::query()->ordered()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $type = array_key_exists($request->query('type'), HomepageSection::TYPES) ? $request->query('type') : 'custom';

        return view('admin.sections.form', [
            'section' => new HomepageSection(['type' => $type, 'name' => HomepageSection::TYPES[$type], 'is_active' => true, 'settings' => []]),
        ]);
    }

    public function store(HomepageSectionRequest $request): RedirectResponse
    {
        $type = $request->sectionType();
        $section = new HomepageSection([
            'type' => $type,
            'key' => HomepageSection::query()->where('key', $type)->exists() ? $type.'-'.Str::lower(Str::random(6)) : $type,
            'is_system' => false,
            'sort_order' => HomepageSection::nextSortOrder(),
        ]);

        $this->persist($request, $section);

        return redirect()->route('admin.sections.edit', $section)->with('success', 'Section added to the homepage.');
    }

    public function edit(HomepageSection $section): View
    {
        return view('admin.sections.form', ['section' => $section]);
    }

    public function update(HomepageSectionRequest $request, HomepageSection $section): RedirectResponse
    {
        $this->persist($request, $section);

        return redirect()->route('admin.sections.edit', $section)->with('success', "“{$section->name}” saved.");
    }

    public function destroy(HomepageSection $section, MediaUploader $media): RedirectResponse
    {
        foreach (SectionFields::for($section->type) as $key => $field) {
            if (in_array($field['type'], ['image', 'video'], true)) {
                $media->delete($section->settings[$key] ?? null);
            }
        }

        $section->delete();

        return redirect()->route('admin.sections.index')->with('success', "“{$section->name}” removed from the homepage.");
    }

    protected function persist(HomepageSectionRequest $request, HomepageSection $section): void
    {
        $settings = $section->settings ?? [];

        foreach (SectionFields::for($section->type) as $key => $field) {
            $value = $request->input("settings.{$key}");

            $settings[$key] = match ($field['type']) {
                'image', 'video' => $this->media($request, "media.{$key}", $settings[$key] ?? null, 'sections'),
                'boolean' => $request->boolean("settings.{$key}"),
                'number' => $value === null || $value === '' ? null : (int) $value,
                'rich' => $this->cleanHtml($value),
                default => $value,
            };
        }

        $section->fill($request->safe()->only(['name', 'eyebrow', 'heading', 'subheading', 'description', 'is_active']));
        $section->settings = $settings;
        $section->save();
    }
}
