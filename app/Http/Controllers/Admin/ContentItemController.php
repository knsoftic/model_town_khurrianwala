<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentItemRequest;
use App\Services\MediaUploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Shared list / form / save logic for the small CMS collections
 * (features, development milestones, statistics, FAQs). Each subclass
 * names its model, route prefix, view folder and labels.
 */
abstract class ContentItemController extends Controller
{
    use HandlesMedia;

    /** @var class-string<Model> */
    protected string $model;

    protected string $route;

    protected string $view;

    protected string $singular;

    protected bool $hasImage = false;

    public function index(): View
    {
        return view("admin.{$this->view}.index", [
            'items' => $this->model::query()->ordered()->get(),
            'route' => $this->route,
        ]);
    }

    public function create(): View
    {
        return $this->form(new $this->model(['is_active' => true, 'sort_order' => $this->model::nextSortOrder()] + $this->defaults()));
    }

    public function store(ContentItemRequest $request): RedirectResponse
    {
        $item = new $this->model;
        $this->save($request, $item);

        return redirect()->route("admin.{$this->route}.index")->with('success', "{$this->singular} added.");
    }

    public function edit(string $id): View
    {
        return $this->form($this->model::query()->findOrFail($id));
    }

    public function update(ContentItemRequest $request, string $id): RedirectResponse
    {
        $this->save($request, $this->model::query()->findOrFail($id));

        return redirect()->route("admin.{$this->route}.index")->with('success', "{$this->singular} saved.");
    }

    public function destroy(string $id, MediaUploader $media): RedirectResponse
    {
        $item = $this->model::query()->findOrFail($id);

        if ($this->hasImage) {
            $media->delete($item->image);
        }

        $item->delete();

        return redirect()->route("admin.{$this->route}.index")->with('success', "{$this->singular} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [];
    }

    protected function form(Model $item): View
    {
        return view("admin.{$this->view}.form", ['item' => $item, 'route' => $this->route, 'singular' => $this->singular]);
    }

    protected function save(ContentItemRequest $request, Model $item): void
    {
        $data = $request->safe()->except('image');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($this->hasImage) {
            $data['image'] = $this->media($request, 'image', $item->image, $this->route);
        }

        $item->fill($data)->save();
    }
}
