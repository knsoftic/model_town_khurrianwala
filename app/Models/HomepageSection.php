<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

#[Fillable(['key', 'type', 'name', 'eyebrow', 'heading', 'subheading', 'description', 'settings', 'is_active', 'is_system', 'sort_order'])]
class HomepageSection extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    /**
     * Section types the homepage can render (each has a Blade partial in front/sections).
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'hero' => 'Hero Banner',
        'intro' => 'Project Introduction',
        'plots' => 'Plot Sizes',
        'installment_plans' => 'Installment Plans & Payment Schedule',
        'calculator' => 'Payment Plan Calculator',
        'features' => 'Project Features',
        'location' => 'Prime Location',
        'development' => 'Development Progress',
        'testimonials' => 'Happy Clients',
        'gallery' => 'Project Gallery',
        'video' => 'Video Section',
        'faq' => 'FAQs',
        'contact_cta' => 'Contact / Inquiry CTA',
        'custom' => 'Custom Content Block',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $value = Arr::get($this->settings ?? [], $key);

        return blank($value) ? $default : $value;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
