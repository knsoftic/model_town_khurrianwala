<?php

namespace App\Http\Requests\Front;

class ContactRequest extends PublicFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'required_without:email', 'string', self::PHONE_RULE],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
        ] + $this->honeypot();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'email.required_without' => 'Please give us an email address or a phone number.',
            'phone.required_without' => 'Please give us a phone number or an email address.',
        ];
    }
}
