<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'cover_image', 'sort_order', 'is_active'])]
class GalleryAlbum extends Model
{
    use HasVisibilityAndOrder, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeImages(): HasMany
    {
        return $this->images()->where('is_active', true);
    }
}
