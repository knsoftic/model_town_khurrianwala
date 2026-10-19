<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['title', 'description', 'keywords', 'canonical_url', 'og_title', 'og_description', 'og_image', 'robots', 'schema_json'])]
class SeoSetting extends Model
{
    /**
     * Text fields that the shared SEO form partial submits.
     */
    public const EDITABLE = ['title', 'description', 'keywords', 'canonical_url', 'og_title', 'og_description', 'robots', 'schema_json'];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
