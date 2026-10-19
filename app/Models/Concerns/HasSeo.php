<?php

namespace App\Models\Concerns;

use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoSetting::class, 'seoable');
    }

    /**
     * Save SEO fields submitted with the parent form (all optional).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function syncSeo(array $attributes): void
    {
        $attributes = array_intersect_key($attributes, array_flip(SeoSetting::EDITABLE));

        if (array_filter($attributes, fn ($value) => filled($value)) === [] && ! $this->seo) {
            return;
        }

        $this->seo()->updateOrCreate([], $attributes);
    }
}
