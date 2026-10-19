<?php

namespace App\Http\Requests\Front;

class NewsletterRequest extends PublicFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:150'],
        ] + $this->honeypot();
    }
}
