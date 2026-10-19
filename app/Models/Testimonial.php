<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'plot_id', 'client_name', 'client_title', 'client_image', 'location', 'review', 'rating',
    'video_url', 'is_featured', 'is_active', 'sort_order',
])]
class Testimonial extends Model
{
    use HasFactory, HasVisibilityAndOrder, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function initials(): string
    {
        return Str::of($this->client_name)->explode(' ')->filter()->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }

    public function videoEmbedUrl(): ?string
    {
        return VideoEmbed::embedUrl($this->video_url);
    }
}
