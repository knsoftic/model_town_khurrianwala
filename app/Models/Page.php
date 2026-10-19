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
