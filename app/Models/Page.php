<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

#[Fillable(['slug', 'name', 'eyebrow', 'heading', 'subheading', 'content', 'banner_image', 'image', 'images', 'video_url', 'cta_text', 'cta_url', 'settings', 'is_active'])]
class Page extends Model
{
    use HasSeo, LogsActivity;

    /**
     * Per-page on/off options shown in the admin page editor.
     *
     * @var array<string, array<string, string>>
     */
    public const SETTINGS = [
        'about' => [
            'show_statistics' => 'Show statistics band (CMS → Statistics → About)',
            'show_features' => 'Show project features',
            'show_video' => 'Show project video',
        ],
        'contact' => [
            'show_map' => 'Show map',
            'show_callback' => 'Show “Request a callback” box',
        ],
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $value = Arr::get($this->settings ?? [], $key);

        return blank($value) ? $default : $value;
    }
}
