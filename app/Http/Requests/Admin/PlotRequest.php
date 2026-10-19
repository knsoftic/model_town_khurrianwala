<?php

namespace App\Http\Requests\Admin;

use App\Enums\PlotStatus;
use App\Http\Requests\Admin\Concerns\SeoRules;
use App\Models\Plot;
use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlotRequest extends FormRequest
{
    use SeoRules;

    public function authorize(): bool
    {
        return $this->user()->can('plots');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('size_label', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $money = ['nullable', 'numeric', 'min:0', 'max:99999999999'];
        $plot = $this->route('plot');

        return [
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'size_label' => ['required', 'string', 'max:60'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('plots', 'slug')->ignore($plot?->id)],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Plot::CATEGORIES))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'full_description' => ['nullable', 'string', 'max:100000'],
            'dimensions' => ['nullable', 'string', 'max:60'],
            'area_sqft' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'starting_price' => $money,
            'total_price' => $money,
            'booking_amount' => $money,
            'down_payment' => $money,
            'installment_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'installment_amount' => $money,
            'payment_frequency' => ['required', Rule::in(array_keys(Plot::FREQUENCIES))],
            'after_6_months_payment' => $money,
            'after_12_months_payment' => $money,
            'end_payment' => $money,
            'development_charges' => $money,
            'possession_charges' => $money,
            'total_units' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'sold_units' => ['nullable', 'integer', 'min:0', 'max:1000000', 'lte:total_units'],
            'status' => ['required', Rule::enum(PlotStatus::class)],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'is_demo_price' => ['boolean'],
            'price_note' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => UploadRules::image(),
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => UploadRules::image(),
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:255'],
        ] + $this->seoRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sold_units.lte' => 'Sold plots cannot be more than total plots.',
            'slug.unique' => 'Another plot size already uses this URL. Change the URL slug.',
        ];
    }
}
