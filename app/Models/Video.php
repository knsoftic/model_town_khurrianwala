<?php

namespace App\Models;

use App\Enums\VideoProvider;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'provider', 'url', 'file_path', 'thumbnail', 'description', 'is_featured', 'is_active', 'sort_order'])]
class Video extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    protected function casts(): array
    {
        return [
            'provider' => VideoProvider::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function embedUrl(): ?string
    {
        return $this->provider === VideoProvider::SelfHosted ? null : VideoEmbed::embedUrl($this->url, autoplay: true);
    }

    public function fileUrl(): ?string
    {
        return $this->provider === VideoProvider::SelfHosted ? media_url($this->file_path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        return media_url($this->thumbnail) ?? VideoEmbed::thumbnailUrl($this->url);
    }
}
