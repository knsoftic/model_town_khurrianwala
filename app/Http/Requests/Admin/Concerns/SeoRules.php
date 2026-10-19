<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Support\UploadRules;
use Illuminate\Validation\Rule;

trait SeoRules
{
    /**
     * @return array<string, mixed>
     */
    protected function seoRules(): array
    {
        return [
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.keywords' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:500'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.robots' => ['nullable', Rule::in(['noindex, follow', 'noindex, nofollow'])],
            'seo.schema_json' => ['nullable', 'string', 'json', 'max:20000'],
            'seo_og_image' => UploadRules::image(),
        ];
    }
}
