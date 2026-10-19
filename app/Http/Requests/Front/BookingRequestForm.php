<?php

namespace App\Http\Requests\Front;

use App\Enums\PlotStatus;
use App\Models\Lead;
use Illuminate\Validation\Rule;

class BookingRequestForm extends PublicFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $showCnic = setting_enabled('booking_show_cnic');

        return [
            'plot_id' => ['required', 'integer', Rule::exists('plots', 'id')
                ->where('is_active', true)->whereNull('deleted_at')
                ->whereIn('status', [PlotStatus::Available->value, PlotStatus::Limited->value])],
            'installment_plan_id' => ['nullable', 'integer', Rule::exists('installment_plans', 'id')
                ->where('plot_id', (int) $this->input('plot_id'))->where('is_active', true)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', self::PHONE_RULE],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'cnic' => $showCnic
                ? [setting_enabled('booking_require_cnic') ? 'required' : 'nullable', 'string', 'regex:/^\d{5}-?\d{7}-?\d$/']
                : ['prohibited'],
            'city' => ['nullable', 'string', 'max:100'],
            'preferred_contact_method' => ['nullable', Rule::in(array_keys(Lead::CONTACT_METHODS))],
            'message' => ['nullable', 'string', 'max:2000'],
            'agree' => ['accepted'],
        ] + $this->honeypot();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'plot_id.required' => 'Please choose a plot size.',
            'plot_id.exists' => 'The selected plot size is not available for booking.',
            'installment_plan_id.exists' => 'Please choose an installment plan for the selected plot size.',
            'cnic.regex' => 'Please enter the CNIC as 12345-1234567-1.',
            'agree.accepted' => 'Please confirm that you understand this is a booking request.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['cnic' => 'CNIC', 'installment_plan_id' => 'installment plan'];
    }
}
