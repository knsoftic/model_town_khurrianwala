<?php

namespace App\Services;

use App\Models\InstallmentPlan;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlotService
{
    public function __construct(protected MediaUploader $media) {}

    /**
     * Copy a plot size with its plans, schedules and images. The copy starts hidden.
     */
    public function duplicate(Plot $plot): Plot
    {
        return DB::transaction(function () use ($plot) {
            $plot->load(['images', 'installmentPlans.items', 'installmentPlans.plot', 'seo']);

            $copy = $plot->replicate(['created_at', 'updated_at', 'deleted_at']);
            $copy->slug = $this->uniqueSlug(Plot::class, $plot->slug);
            $copy->title = $plot->title.' (Copy)';
            $copy->is_active = false;
            $copy->is_featured = false;
            $copy->image = $this->media->duplicate($plot->image);
            $copy->sort_order = Plot::nextSortOrder();
            $copy->save();

            foreach ($plot->images as $image) {
                $copy->images()->create(['path' => $this->media->duplicate($image->path), 'caption' => $image->caption, 'sort_order' => $image->sort_order]);
            }

            foreach ($plot->installmentPlans as $plan) {
                $this->copyPlan($plan, $copy);
            }

            return $copy;
        });
    }

    public function copyPlan(InstallmentPlan $plan, ?Plot $target = null): InstallmentPlan
    {
        $plan->loadMissing('items');

        $copy = $plan->replicate(['created_at', 'updated_at', 'deleted_at']);
        $copy->plot_id = $target?->id ?? $plan->plot_id;
        $copy->slug = $this->uniqueSlug(InstallmentPlan::class, $target ? $target->slug.'-'.Str::after($plan->slug, $plan->plot?->slug.'-') : $plan->slug);
        $copy->name = $target ? $plan->name : $plan->name.' (Copy)';
        $copy->is_default = $target ? $plan->is_default : false;
        $copy->is_active = $target ? $plan->is_active : false;
        $copy->save();

        foreach ($plan->items as $item) {
            $copy->items()->create($item->only(['type', 'title', 'amount', 'occurrences', 'percentage', 'due_after_months', 'due_label', 'description', 'sort_order']));
        }

        return $copy;
    }

    /**
     * @param  class-string<Plot|InstallmentPlan>  $model
     */
    public function uniqueSlug(string $model, string $base): string
    {
        $base = Str::limit(Str::slug($base), 200, '');
        $slug = $base.'-copy';
        $i = 2;

        while ($model::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-copy-'.$i++;
        }

        return $slug;
    }
}
