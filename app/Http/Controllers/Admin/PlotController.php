<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlotStatus;
use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlotRequest;
use App\Models\Location;
use App\Models\Plot;
use App\Models\PlotImage;
use App\Services\MediaUploader;
use App\Services\PlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlotController extends Controller
{
    use HandlesMedia;

    protected const SORTS = ['sort_order', 'size_label', 'starting_price', 'total_price', 'created_at'];

    public function index(Request $request): View
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'sort_order';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $plots = Plot::query()
            ->with('location')
            ->withCount(['installmentPlans', 'leads', 'bookingRequests'])
            ->when($request->query('q'), fn ($query, $term) => $query->where(fn ($q) => $q->where('size_label', 'like', "%{$term}%")->orWhere('title', 'like', "%{$term}%")))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('location'), fn ($query, $location) => $query->where('location_id', $location))
            ->when($request->query('visibility') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('visibility') === 'hidden', fn ($query) => $query->where('is_active', false))
            ->when($request->query('visibility') === 'featured', fn ($query) => $query->where('is_featured', true))
            ->orderBy($sort, $direction)->orderBy('id')
            ->paginate(20)->withQueryString();

        return view('admin.plots.index', [
            'plots' => $plots,
            'locations' => Location::query()->orderBy('name')->pluck('name', 'id'),
            'filtered' => $request->hasAny(['q', 'status', 'location', 'visibility', 'sort']),
        ]);
    }

    public function create(): View
    {
        return view('admin.plots.form', [
            'plot' => new Plot([
                'status' => PlotStatus::Available,
                'category' => 'residential',
                'payment_frequency' => 'monthly',
                'is_active' => true,
                'sort_order' => Plot::nextSortOrder(),
            ]),
            'locations' => Location::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(PlotRequest $request): RedirectResponse
    {
        $plot = DB::transaction(function () use ($request) {
            $plot = Plot::query()->create($this->attributes($request) + [
                'image' => $this->media($request, 'image', null, 'plots'),
            ]);
            $this->storeGallery($request, $plot);
            $this->saveSeo($request, $plot);

            return $plot;
        });

        return redirect()->route('admin.plots.edit', $plot)
            ->with('success', "{$plot->size_label} created. Now add its installment plan.");
    }

    public function edit(Plot $plot): View
    {
        $plot->load(['images', 'seo', 'installmentPlans' => fn ($query) => $query->with('items')->orderByDesc('is_default')->orderBy('sort_order')]);

        return view('admin.plots.form', [
            'plot' => $plot,
            'locations' => Location::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(PlotRequest $request, Plot $plot): RedirectResponse
    {
        DB::transaction(function () use ($request, $plot) {
            $plot->update($this->attributes($request) + [
                'image' => $this->media($request, 'image', $plot->image, 'plots'),
            ]);
            $this->storeGallery($request, $plot);

            foreach ((array) $request->input('captions', []) as $id => $caption) {
                $plot->images()->whereKey($id)->update(['caption' => $caption]);
            }

            $this->saveSeo($request, $plot);
        });

        return redirect()->route('admin.plots.edit', $plot)->with('success', "{$plot->size_label} saved.");
    }

    public function destroy(Plot $plot): RedirectResponse
    {
        $plot->delete();

        return redirect()->route('admin.plots.index')->with('success', "{$plot->size_label} moved to trash. Its leads and bookings are kept.");
    }

    public function duplicate(Plot $plot, PlotService $plots): RedirectResponse
    {
        $copy = $plots->duplicate($plot);

        return redirect()->route('admin.plots.edit', $copy)
            ->with('success', 'Copy created (hidden from the website). Change its details and turn it on when ready.');
    }

    public function destroyImage(Plot $plot, PlotImage $image, MediaUploader $media): RedirectResponse
    {
        abort_unless($image->plot_id === $plot->id, 404);

        $media->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image removed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function attributes(PlotRequest $request): array
    {
        $data = $request->safe()->except(['image', 'gallery', 'captions', 'seo', 'seo_og_image']);
        $data['full_description'] = $this->cleanHtml($data['full_description'] ?? null);
        $data['total_units'] = (int) ($data['total_units'] ?? 0);
        $data['sold_units'] = (int) ($data['sold_units'] ?? 0);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    protected function storeGallery(PlotRequest $request, Plot $plot): void
    {
        $next = (int) $plot->images()->max('sort_order') + 1;

        foreach ($request->file('gallery', []) as $file) {
            $plot->images()->create(['path' => app(MediaUploader::class)->store($file, 'plots/gallery'), 'sort_order' => $next++]);
        }
    }
}
