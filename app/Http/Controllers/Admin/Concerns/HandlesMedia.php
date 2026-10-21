<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\HtmlSanitizer;
use App\Services\MediaUploader;
use App\Support\Keywords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Upload / replace / remove helpers shared by admin controllers.
 * Every image field posts the file as "<field>" and a "remove_<field>" flag.
 */
trait HandlesMedia
{
    protected function media(Request $request, string $field, ?string $current, string $folder): ?string
    {
        $flag = 'remove_'.str_replace(['.', '[', ']'], ['_', '_', ''], $field);

        return app(MediaUploader::class)->replace($request->file($field), $current, $folder, $request->boolean($flag));
    }

    protected function cleanHtml(?string $html): ?string
    {
        return app(HtmlSanitizer::class)->clean($html);
    }

    /**
     * Save the shared SEO form (x-admin.seo-fields) for a model using HasSeo.
     */
    protected function saveSeo(Request $request, Model $model): void
    {
        $model->loadMissing('seo');
        $data = (array) $request->input('seo', []);
        $data['keywords'] = Keywords::normalise($data['keywords'] ?? null) ?: null;
        $data['og_image'] = $this->media($request, 'seo_og_image', $model->seo?->og_image, 'seo');

        $model->syncSeo($data);
    }
}
