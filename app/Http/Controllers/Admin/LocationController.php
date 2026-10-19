<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Models\Location;
use App\Support\MapEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    use HandlesMedia;

    public function index(): View
    {
        return view('admin.locations.index', [
            'locations' => Location::query()->withCount('plots')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.locations.form', ['location' => new Location(['is_active' => true, 'sort_order' => Location::nextSortOrder(), 'landmarks' => []])]);
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $location = new Location;
        $this->save($request, $location);

        return redirect()->route('admin.locations.edit', $location)->with('success', 'Location added.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.form', ['location' => $location->load('seo')]);
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $this->save($request, $location);

        return redirect()->route('admin.locations.edit', $location)->with('success', "{$location->name} saved.");
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', "{$location->name} deleted.");
    }

    protected function save(LocationRequest $request, Location $location): void
    {
        $data = $request->safe()->except(['image', 'map_embed', 'seo', 'seo_og_image']);
        $data['description'] = $this->cleanHtml($data['description'] ?? null);
        $data['map_embed_url'] = MapEmbed::extract($request->input('map_embed'));
        $data['image'] = $this->media($request, 'image', $location->image, 'locations');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $location->fill($data)->save();
        $this->saveSeo($request, $location);
    }
}
