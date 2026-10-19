<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentMilestone;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\HomepageSection;
use App\Models\InstallmentPlan;
use App\Models\Location;
use App\Models\NavigationItem;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Plot;
use App\Models\PlotImage;
use App\Models\ProjectFeature;
use App\Models\SocialLink;
use App\Models\Statistic;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Video;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Generic drag-and-drop ordering and on/off switches for admin lists.
 * Each resource maps to a model, the permission required and the fields that may be toggled.
 */
class ReorderController extends Controller
{
    /**
     * @var array<string, array{0: class-string<Model>, 1: string, 2: array<int, string>, 3: bool}>
     */
    protected const RESOURCES = [
        'plots' => [Plot::class, 'plots', ['is_active', 'is_featured'], true],
        'plot-images' => [PlotImage::class, 'plots', [], true],
        'installment-plans' => [InstallmentPlan::class, 'installments', ['is_active', 'is_featured'], true],
        'locations' => [Location::class, 'locations', ['is_active', 'is_featured'], true],
        'testimonials' => [Testimonial::class, 'testimonials', ['is_active', 'is_featured'], true],
        'albums' => [GalleryAlbum::class, 'gallery', ['is_active'], true],
        'gallery-images' => [GalleryImage::class, 'gallery', ['is_active'], true],
        'videos' => [Video::class, 'gallery', ['is_active', 'is_featured'], true],
        'sections' => [HomepageSection::class, 'cms', ['is_active'], true],
        'pages' => [Page::class, 'cms', ['is_active'], false],
        'menus' => [NavigationItem::class, 'cms', ['is_active'], true],
        'features' => [ProjectFeature::class, 'cms', ['is_active'], true],
        'development' => [DevelopmentMilestone::class, 'cms', ['is_active'], true],
        'statistics' => [Statistic::class, 'cms', ['is_active'], true],
        'faqs' => [Faq::class, 'cms', ['is_active'], true],
        'social-links' => [SocialLink::class, 'settings', ['is_active'], true],
        'users' => [User::class, 'users', ['is_active'], false],
        'subscribers' => [NewsletterSubscriber::class, 'leads', ['is_active'], false],
    ];

    public function reorder(Request $request, string $resource, ActivityLogger $logger): JsonResponse
    {
        [$model, , , $sortable] = $this->resolve($resource);
        abort_unless($sortable, 404);

        $ids = $request->validate(['ids' => ['required', 'array', 'max:500'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(function () use ($model, $ids) {
            foreach (array_values($ids) as $position => $id) {
                $model::query()->whereKey($id)->update(['sort_order' => $position]);
            }
        });

        $logger->log('reordered', 'Reordered '.str_replace('-', ' ', $resource));

        return response()->json(['message' => 'Order saved']);
    }

    public function toggle(Request $request, string $resource, int $id): JsonResponse
    {
        [$model, , $fields] = $this->resolve($resource);

        $data = $request->validate([
            'field' => ['required', 'in:'.implode(',', $fields)],
            'value' => ['required', 'boolean'],
        ]);

        $record = $model::query()->findOrFail($id);

        if ($record instanceof User && $record->is($request->user())) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $record->update([$data['field'] => $data['value']]);

        $state = $data['field'] === 'is_featured'
            ? ($data['value'] ? 'Marked as featured' : 'Removed from featured')
            : ($data['value'] ? 'Enabled' : 'Disabled');

        return response()->json(['message' => $state]);
    }

    /**
     * @return array{0: class-string<Model>, 1: string, 2: array<int, string>, 3: bool}
     */
    protected function resolve(string $resource): array
    {
        $config = self::RESOURCES[$resource] ?? abort(404);

        Gate::authorize($config[1]);

        return $config;
    }
}
