<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for public website forms: shared phone rule and a hidden honeypot field
 * ("website") that real visitors never fill in.
 */
abstract class PublicFormRequest extends FormRequest
{
    public const PHONE_RULE = 'regex:/^\+?[0-9][0-9\s\-()]{6,19}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function honeypot(): array
    {
        return ['website' => ['nullable', 'max:0']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only(['name', 'phone', 'email', 'city', 'cnic', 'subject']))
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number, e.g. +92 300 1234567.',
            'website.max' => 'Your submission could not be processed.',
        ];
    }
}
