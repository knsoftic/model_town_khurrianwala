<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

#[Fillable(['location', 'label', 'route_name', 'url', 'target', 'parent_id', 'sort_order', 'is_active', 'is_system'])]
class NavigationItem extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    /**
     * @var array<string, string>
     */
    public const LOCATIONS = [
        'header' => 'Header Menu',
        'footer_quick' => 'Footer — Quick Links',
        'footer_useful' => 'Footer — Useful Links',
    ];

    /**
     * Public pages a menu item can point at, keyed by route name.
     * The value is the page slug used to hide links to disabled pages.
     *
     * @var array<string, array{0: string, 1: string|null}>
     */
    public const ROUTES = [
        'home' => ['Home', null],
        'about' => ['About Project', 'about'],
        'plots.index' => ['Plot Sizes', 'plots'],
        'installment-plans' => ['Installment Plans', 'installment-plans'],
        'locations.index' => ['Locations', 'locations'],
        'development' => ['Development', 'development'],
        'gallery' => ['Gallery', 'gallery'],
        'testimonials' => ['Testimonials', 'testimonials'],
        'faq' => ['FAQ', 'faq'],
        'contact' => ['Contact', 'contact'],
        'book-plot' => ['Book Your Plot', 'book-plot'],
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function href(): string
    {
        if ($this->route_name && Route::has($this->route_name)) {
            return route($this->route_name);
        }

        return $this->url ?: '#';
    }

    public function pageSlug(): ?string
    {
        return self::ROUTES[$this->route_name][1] ?? null;
    }

    public function isCurrent(): bool
    {
        if ($this->route_name) {
            return request()->routeIs($this->route_name) || request()->routeIs(str_replace('.index', '.*', $this->route_name));
        }

        return $this->url && rtrim(url($this->url), '/') === rtrim(request()->url(), '/');
    }
}
