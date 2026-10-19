<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\SeoRules;
use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    use SeoRules;

    public function authorize(): bool
    {
        return $this->user()->can('cms');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'eyebrow' => ['nullable', 'string', 'max:150'],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:200000'],
            'video_url' => ['nullable', 'url', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
            'settings.*' => ['boolean'],
            'banner_image' => UploadRules::image(),
            'image' => UploadRules::image(),
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => UploadRules::image(),
            'remove_gallery' => ['nullable', 'array'],
        ] + $this->seoRules();
    }
}
