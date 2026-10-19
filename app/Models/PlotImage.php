<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plot_id', 'path', 'caption', 'sort_order'])]
class PlotImage extends Model
{
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
