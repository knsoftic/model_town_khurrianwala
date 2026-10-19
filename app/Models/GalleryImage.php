<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['gallery_album_id', 'title', 'path', 'caption', 'sort_order', 'is_active'])]
class GalleryImage extends Model
{
    use HasVisibilityAndOrder;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }
}
