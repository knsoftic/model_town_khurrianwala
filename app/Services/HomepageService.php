<?php

namespace App\Services;

use App\Models\DevelopmentMilestone;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\HomepageSection;
use App\Models\Location;
use App\Models\Plot;
use App\Models\ProjectFeature;
use App\Models\Statistic;
use App\Models\Testimonial;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Loads the enabled homepage sections (in admin-defined order) and only the
 * data those sections need.
 */
class HomepageService
{
    /**
     * @return array{sections: Collection<int, HomepageSection>, data: array<string, mixed>}
     */
    public function build(): array
    {
        $sections = HomepageSection::query()->active()->ordered()->get();
        $byType = $sections->keyBy('type');
        $has = fn (string ...$types) => collect($types)->contains(fn ($type) => $byType->has($type));
        $limit = fn (string $type, int $default) => max(1, (int) ($byType->get($type)?->setting('limit', $default) ?? $default));

        $data = [];

        if ($has('hero', 'plots', 'installment_plans', 'calculator')) {
            $data['plots'] = Plot::query()->active()->ordered()->with(['location', 'activePlans.items'])->get();
        }

        if ($has('intro')) {
            $data['statistics'] = Statistic::query()->active()->where('group', 'intro')->ordered()->get();
        }

        if ($has('features')) {
            $data['features'] = ProjectFeature::query()->active()->ordered()->limit($limit('features', 12))->get();
        }

        if ($has('location')) {
            $data['location'] = Location::query()->active()->orderByDesc('is_featured')->ordered()->first();
        }

        if ($has('development')) {
            $data['milestones'] = DevelopmentMilestone::query()->active()->ordered()->get();
        }

        if ($has('testimonials')) {
            $data['testimonials'] = Testimonial::query()->active()->with('plot')
                ->orderByDesc('is_featured')->ordered()->limit($limit('testimonials', 6))->get();
        }

        if ($has('gallery')) {
            $data['galleryImages'] = GalleryImage::query()->active()
                ->whereHas('album', fn (Builder $query) => $query->where('is_active', true))
                ->with('album')->ordered()->limit($limit('gallery', 8))->get();
        }

        if ($has('video')) {
            $data['video'] = Video::query()->active()->orderByDesc('is_featured')->ordered()->first();
        }

        if ($has('faq')) {
            $data['faqs'] = Faq::query()->active()->ordered()->limit($limit('faq', 6))->get();
        }

        return ['sections' => $sections, 'data' => $data];
    }
}
