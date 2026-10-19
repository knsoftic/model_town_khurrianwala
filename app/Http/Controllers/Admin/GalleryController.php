<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Services\MediaUploader;
use App\Support\UploadRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryController extends Controller
{
    use HandlesMedia;

    public function index(): View
    {
        return view('admin.gallery.index', [
            'albums' => GalleryAlbum::query()->withCount('images')->ordered()->get(),
        ]);
    }

    public function storeAlbum(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $album = GalleryAlbum::query()->create($data + [
            'slug' => $this->uniqueSlug($data['name']),
            'is_active' => true,
            'sort_order' => GalleryAlbum::nextSortOrder(),
        ]);

        return redirect()->route('admin.gallery.albums.show', $album)->with('success', 'Album created. Now upload its photos.');
    }

    public function show(GalleryAlbum $album): View
    {
        return view('admin.gallery.show', [
            'album' => $album->load('images'),
            'albums' => GalleryAlbum::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function updateAlbum(Request $request, GalleryAlbum $album): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('gallery_albums', 'slug')->ignore($album->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'cover_image' => UploadRules::image(),
        ]);

        $data['cover_image'] = $this->media($request, 'cover_image', $album->cover_image, 'gallery');
        $album->update($data);

        return back()->with('success', 'Album saved.');
    }

    public function destroyAlbum(GalleryAlbum $album): RedirectResponse
    {
        $album->delete();

        return redirect()->route('admin.gallery.index')->with('success', "Album “{$album->name}” deleted.");
    }

    public function storeImages(Request $request, GalleryAlbum $album, MediaUploader $media): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:30'],
            'images.*' => UploadRules::image(true),
        ], ['images.required' => 'Choose at least one image to upload.']);

        $next = (int) $album->images()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $album->images()->create([
                'path' => $media->store($file, 'gallery'),
                'title' => Str::of($file->getClientOriginalName())->beforeLast('.')->replace(['-', '_'], ' ')->title()->limit(140, ''),
                'sort_order' => $next++,
                'is_active' => true,
            ]);
        }

        if (! $album->cover_image) {
            $album->update(['cover_image' => $album->images()->value('path')]);
        }

        return back()->with('success', count($request->file('images')).' '.str('image')->plural(count($request->file('images'))).' uploaded.');
    }

    public function updateImage(Request $request, GalleryImage $image): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:500'],
            'gallery_album_id' => ['required', 'integer', Rule::exists('gallery_albums', 'id')],
            'make_cover' => ['nullable', 'boolean'],
        ]);

        $image->update(collect($data)->except('make_cover')->all());

        if ($request->boolean('make_cover')) {
            $image->album()->update(['cover_image' => $image->path]);
        }

        return back()->with('success', 'Image updated.');
    }

    public function destroyImage(GalleryImage $image, MediaUploader $media): RedirectResponse
    {
        $album = $image->loadMissing('album')->album;

        if ($album && $album->cover_image === $image->path) {
            $album->update(['cover_image' => $album->images()->whereKeyNot($image->id)->value('path')]);
        }

        $media->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image deleted.');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'album';
        $slug = $base;
        $i = 2;

        while (GalleryAlbum::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
