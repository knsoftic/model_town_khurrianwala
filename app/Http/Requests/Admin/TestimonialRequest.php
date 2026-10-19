<?php

namespace App\Http\Requests\Admin;

use App\Support\UploadRules;
use App\Support\VideoEmbed;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('testimonials');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:150'],
            'client_title' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'plot_id' => ['nullable', 'integer', Rule::exists('plots', 'id')],
            'review' => ['required', 'string', 'max:3000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'video_url' => ['nullable', 'url', 'max:1000', function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && ! VideoEmbed::isSupported($value)) {
                    $fail('Use a YouTube or Vimeo link.');
                }
            }],
            'client_image' => UploadRules::image(),
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
