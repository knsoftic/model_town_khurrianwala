<?php

namespace App\Http\Requests\Admin;

use App\Models\HomepageSection;
use App\Support\SectionFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomepageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cms');
    }

    public function sectionType(): string
    {
        return $this->route('section')?->type ?? (string) $this->input('type');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => [$this->route('section') ? 'nullable' : 'required', Rule::in(array_keys(HomepageSection::TYPES))],
            'name' => ['required', 'string', 'max:100'],
            'eyebrow' => ['nullable', 'string', 'max:150'],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
        ] + SectionFields::rules($this->sectionType());
    }
}
