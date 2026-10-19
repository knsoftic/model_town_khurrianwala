<?php

namespace App\Http\Requests\Front;

use App\Models\Lead;
use Illuminate\Validation\Rule;

class LeadRequest extends PublicFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', self::PHONE_RULE],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'plot_id' => ['nullable', 'integer', Rule::exists('plots', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'budget' => ['nullable', 'string', 'max:100'],
            'payment_preference' => ['nullable', Rule::in(array_keys(Lead::PAYMENT_PREFERENCES))],
            'preferred_contact_method' => ['nullable', Rule::in(array_keys(Lead::CONTACT_METHODS))],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', Rule::in(['inquiry', 'plot_page', 'calculator'])],
            'page_url' => ['nullable', 'string', 'max:500'],
        ] + $this->honeypot();
    }
}
