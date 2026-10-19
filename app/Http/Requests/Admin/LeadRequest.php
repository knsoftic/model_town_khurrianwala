<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'plot_id' => ['nullable', 'integer', Rule::exists('plots', 'id')],
            'budget' => ['nullable', 'string', 'max:100'],
            'payment_preference' => ['nullable', Rule::in(array_keys(Lead::PAYMENT_PREFERENCES))],
            'preferred_contact_method' => ['nullable', Rule::in(array_keys(Lead::CONTACT_METHODS))],
            'preferred_time' => ['nullable', 'string', 'max:60'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}
