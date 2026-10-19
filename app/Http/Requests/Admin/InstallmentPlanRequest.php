<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentStageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InstallmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('installments');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name', '')),
            'items' => array_values((array) $this->input('items', [])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $plan = $this->route('installment_plan');

        return [
            'plot_id' => ['required', 'integer', Rule::exists('plots', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('installment_plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'total_price' => ['required', 'numeric', 'min:0', 'max:99999999999'],
            'full_payment_price' => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
            'duration_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'is_default' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'is_demo' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.type' => ['required', Rule::enum(PaymentStageType::class)],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0', 'max:99999999999'],
            'items.*.occurrences' => ['required', 'integer', 'min:1', 'max:600'],
            'items.*.due_after_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'items.*.due_label' => ['nullable', 'string', 'max:120'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one payment stage.',
            'items.min' => 'Add at least one payment stage.',
            'items.*.title.required' => 'Every payment stage needs a title.',
            'items.*.amount.required' => 'Every payment stage needs an amount.',
        ];
    }
}
