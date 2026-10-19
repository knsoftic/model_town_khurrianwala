<?php

namespace App\Support;

/**
 * Validation rules shared by every admin upload field.
 */
final class UploadRules
{
    /**
     * @return array<int, string>
     */
    public static function image(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:'.config('cms.uploads.image_mimes', 'jpg,jpeg,png,webp'),
            'max:'.config('cms.uploads.image_max_kb', 5120),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function favicon(): array
    {
        return ['nullable', 'file', 'mimes:png,ico', 'max:512'];
    }

    /**
     * @return array<int, string>
     */
    public static function video(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimetypes:video/mp4,video/webm',
            'max:'.config('cms.uploads.video_max_kb', 51200),
        ];
    }
}
