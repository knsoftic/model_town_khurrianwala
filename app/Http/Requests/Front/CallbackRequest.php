<?php

namespace App\Http\Requests\Front;

use Illuminate\Validation\Rule;

class CallbackRequest extends PublicFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', self::PHONE_RULE],
            'preferred_time' => ['nullable', 'string', 'max:60'],
            'plot_id' => ['nullable', 'integer', Rule::exists('plots', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'page_url' => ['nullable', 'string', 'max:500'],
        ] + $this->honeypot();
    }
}
