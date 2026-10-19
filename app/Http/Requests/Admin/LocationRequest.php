<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\SeoRules;
use App\Support\MapEmbed;
use App\Support\UploadRules;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    use SeoRules;

    public function authorize(): bool
    {
        return $this->user()->can('locations');
    }

    protected function prepareForValidation(): void
    {
        $landmarks = collect((array) $this->input('landmarks', []))
            ->filter(fn ($row) => filled($row['name'] ?? null))
            ->values()->all();

        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name', '')),
            'landmarks' => $landmarks,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('locations', 'slug')->ignore($this->route('location')?->id)],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:100000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps_url' => ['nullable', 'url', 'max:1000'],
            'map_embed' => ['nullable', 'string', 'max:3000', function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && ! MapEmbed::extract($value)) {
                    $fail('Paste the Google Maps “Embed a map” iframe code or its https://www.google.com/maps… link.');
                }
            }],
            'landmarks' => ['array', 'max:30'],
            'landmarks.*.name' => ['required', 'string', 'max:150'],
            'landmarks.*.distance' => ['nullable', 'string', 'max:60'],
            'distance_airport' => ['nullable', 'string', 'max:60'],
            'distance_motorway' => ['nullable', 'string', 'max:60'],
            'distance_main_road' => ['nullable', 'string', 'max:60'],
            'image' => UploadRules::image(),
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ] + $this->seoRules();
    }
}
