<?php

namespace App\Http\Controllers\Front;

use App\Models\Location;
use App\Models\ProjectFeature;
use Illuminate\View\View;

class LocationController extends FrontController
{
    public function index(): View
    {
        $page = $this->page('locations');

        $locations = Location::query()->active()->orderByDesc('is_featured')->ordered()
            ->withCount(['plots' => fn ($query) => $query->where('is_active', true)])
            ->get();

        return view('front.locations.index', [
            'page' => $page,
            'locations' => $locations,
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Locations']),
        ]);
    }

    public function show(Location $location): View
    {
        abort_unless($location->is_active, 404);

        $location->load(['seo', 'plots' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->with('activePlans.items')]);

        return view('front.locations.show', [
            'location' => $location,
            'features' => ProjectFeature::query()->active()->ordered()->limit(8)->get(),
            'seo' => $this->seoFor($location, [
                'title' => $location->name,
                'description' => $location->short_description,
                'image' => $location->image,
                'schemas' => [array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Place',
                    'name' => $location->name,
                    'address' => $location->fullAddress(),
                    'geo' => $location->latitude ? ['@type' => 'GeoCoordinates', 'latitude' => $location->latitude, 'longitude' => $location->longitude] : null,
                ])],
            ]),
        ]);
    }
}
