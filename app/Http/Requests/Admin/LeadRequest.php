<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "Add Lead" / "Edit details": the fields come from Sales & CRM → Lead Form Fields.
 */
class LeadRequest extends FormRequest
{
    /**
     * @var Collection<int, LeadField>|null
     */
    protected ?Collection $fields = null;

    public function authorize(): bool
    {
        return $this->user()->can('leads.access');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'source' => ['required', Rule::enum(LeadSource::class)],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'extra' => ['nullable', 'array'],
            'next_follow_up_date' => ['nullable', 'date'],
            'next_follow_up_time' => ['nullable', 'date_format:H:i'],
            'next_follow_up_note' => ['nullable', 'string', 'max:500'],
        ];

        foreach ($this->fields() as $field) {
            $rules[$field->ruleKey()] = $field->rules();
        }

        return $rules;
    }

    /**
     * Error messages use the labels from the lead form.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->fields()->mapWithKeys(fn (LeadField $field) => [$field->ruleKey() => $field->label])->all() + [
            'next_follow_up_date' => 'follow-up date',
            'next_follow_up_time' => 'follow-up time',
        ];
    }

    /**
     * @return Collection<int, LeadField>
     */
    public function fields(): Collection
    {
        return $this->fields ??= LeadField::form();
    }

    /**
     * Values to save on the lead: built-in columns, source/status, the owner (only staff with
     * "leads" choose it) and the custom fields, merged into what the lead already has.
     *
     * @return array<string, mixed>
     */
    public function leadAttributes(?Lead $lead = null): array
    {
        $validated = $this->validated();
        $system = $this->fields()->where('is_system', true)->pluck('key')->all();
        $data = array_intersect_key($validated, array_flip([...$system, 'source', 'status']));

        if ($this->user()->can('leads')) {
            $data['assigned_to'] = $validated['assigned_to'] ?? null;
        }

        $extra = $lead?->extra ?? [];
        foreach ($this->fields()->where('is_system', false) as $field) {
            $value = $validated['extra'][$field->key] ?? null;

            if (filled($value)) {
                $extra[$field->key] = $value;
            } else {
                unset($extra[$field->key]);
            }
        }
        $data['extra'] = $extra ?: null;

        return $data;
    }
}
