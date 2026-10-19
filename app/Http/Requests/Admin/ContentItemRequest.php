<?php

namespace App\Http\Requests\Admin;

use App\Enums\MilestoneStatus;
use App\Models\Statistic;
use App\Support\Icons;
use App\Support\UploadRules;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for the small CMS lists: project features, development milestones,
 * statistics and FAQs. The route name tells which one is being saved.
 */
class ContentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cms');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $icon = ['nullable', 'string', 'max:60', function (string $attribute, mixed $value, Closure $fail) {
            if ($value && ! Icons::exists($value)) {
                $fail('Unknown icon name. Pick one from the list or from lucide.dev/icons.');
            }
        }];
        $common = ['is_active' => ['boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']];

        return $common + match (true) {
            $this->routeIs('admin.features.*') => [
                'title' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
                'icon' => $icon,
                'image' => UploadRules::image(),
            ],
            $this->routeIs('admin.development.*') => [
                'title' => ['required', 'string', 'max:150'],
                'percentage' => ['required', 'integer', 'min:0', 'max:100'],
                'status' => ['required', Rule::enum(MilestoneStatus::class)],
                'description' => ['nullable', 'string', 'max:1000'],
                'icon' => $icon,
                'image' => UploadRules::image(),
            ],
            $this->routeIs('admin.statistics.*') => [
                'group' => ['required', Rule::in(array_keys(Statistic::GROUPS))],
                'label' => ['required', 'string', 'max:100'],
                'value' => ['required', 'string', 'max:30'],
                'prefix' => ['nullable', 'string', 'max:10'],
                'suffix' => ['nullable', 'string', 'max:10'],
                'icon' => $icon,
            ],
            default => [
                'question' => ['required', 'string', 'max:500'],
                'answer' => ['required', 'string', 'max:5000'],
                'category' => ['nullable', 'string', 'max:60'],
            ],
        };
    }
}
