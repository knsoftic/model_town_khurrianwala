<?php

namespace App\Http\Requests\Admin;

use App\Models\LeadField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A field of the "Add Lead" form. Built-in fields keep their type and choices;
 * only their label, hints and switches can change.
 */
class LeadFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $custom = ! $this->field()?->is_system;

        return [
            'label' => ['required', 'string', 'max:150'],
            'type' => $custom ? ['required', Rule::in(array_keys(LeadField::TYPES))] : ['prohibited'],
            'placeholder' => ['nullable', 'string', 'max:150'],
            'help' => ['nullable', 'string', 'max:255'],
            'options' => $custom ? ['nullable', 'required_if:type,select', 'string', 'max:5000'] : ['prohibited'],
            'is_required' => ['boolean'],
            'show_in_list' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['options.required_if' => 'Write the choices for the dropdown, one per line.'];
    }

    /**
     * Saved values; dropdown choices become a clean list (one per line, no repeats).
     *
     * @return array<string, mixed>
     */
    public function fieldAttributes(): array
    {
        $data = $this->safe()->only(['label', 'placeholder', 'help', 'is_required', 'show_in_list', 'is_active']);

        if (! $this->field()?->is_system) {
            $data['type'] = $this->validated('type');
            $data['options'] = $data['type'] === 'select'
                ? collect(preg_split('/\r\n|\r|\n/', (string) $this->validated('options')))->map(fn ($option) => trim($option))->filter()->unique()->take(50)->values()->all()
                : null;
        }

        return $data;
    }

    protected function field(): ?LeadField
    {
        $field = $this->route('lead_field');

        return $field instanceof LeadField ? $field : null;
    }
}
