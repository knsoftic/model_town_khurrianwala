<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $location = array_key_exists($request->query('location'), NavigationItem::LOCATIONS) ? $request->query('location') : 'header';

        return view('admin.menus.index', [
            'location' => $location,
            'items' => NavigationItem::query()->where('location', $location)->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('sort_order')])->ordered()->get(),
            'counts' => NavigationItem::query()->selectRaw('location, COUNT(*) as total')->groupBy('location')->pluck('total', 'location'),
            'routes' => collect(NavigationItem::ROUTES)->map(fn ($meta) => $meta[0])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) NavigationItem::query()->where('location', $data['location'])->max('sort_order') + 1;

        NavigationItem::query()->create($data);

        return redirect()->route('admin.menus.index', ['location' => $data['location']])->with('success', "“{$data['label']}” added to the menu.");
    }

    public function update(Request $request, NavigationItem $item): RedirectResponse
    {
        $item->update($this->validated($request, $item));

        return redirect()->route('admin.menus.index', ['location' => $item->location])->with('success', "“{$item->label}” saved.");
    }

    public function destroy(NavigationItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('admin.menus.index', ['location' => $item->location])->with('success', "“{$item->label}” removed from the menu.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?NavigationItem $item = null): array
    {
        $data = $request->validate([
            'location' => [$item ? 'nullable' : 'required', Rule::in(array_keys(NavigationItem::LOCATIONS))],
            'label' => ['required', 'string', 'max:100'],
            'link_type' => ['required', Rule::in(['page', 'url'])],
            'route_name' => ['nullable', 'required_if:link_type,page', Rule::in(array_keys(NavigationItem::ROUTES))],
            'url' => ['nullable', 'required_if:link_type,url', 'string', 'max:500', 'regex:/^(https?:\/\/|\/|#|tel:|mailto:)/i'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'parent_id' => ['nullable', 'integer', Rule::exists('navigation_items', 'id')->whereNull('parent_id')],
            'is_active' => ['boolean'],
        ], ['url.regex' => 'Links must start with https://, http://, /, #, tel: or mailto:.']);

        $location = $item?->location ?? $data['location'];

        return [
            'location' => $location,
            'label' => $data['label'],
            'route_name' => $data['link_type'] === 'page' ? $data['route_name'] : null,
            'url' => $data['link_type'] === 'url' ? $data['url'] : null,
            'target' => $data['target'],
            'parent_id' => $location === 'header' && ($data['parent_id'] ?? null) !== $item?->id ? ($data['parent_id'] ?? null) : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
