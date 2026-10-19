<?php

namespace App\Support;

/**
 * Extra (type-specific) settings each homepage section type exposes in the admin.
 * The edit form and the validation rules are both generated from this list.
 */
final class SectionFields
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function for(string $type): array
    {
        $button = [
            'button_text' => ['type' => 'text', 'label' => 'Button text'],
            'button_url' => ['type' => 'text', 'label' => 'Button link', 'help' => 'e.g. /plots or https://…'],
        ];
        $limit = fn (int $default) => ['limit' => ['type' => 'number', 'label' => 'How many items to show', 'help' => "Default {$default}.", 'rules' => ['integer', 'min:1', 'max:50']]];

        return match ($type) {
            'hero' => [
                'background_type' => ['type' => 'select', 'label' => 'Background', 'options' => ['image' => 'Image', 'video' => 'Video', 'gradient' => 'Brand colour only']],
                'background_image' => ['type' => 'image', 'label' => 'Background image', 'help' => '1920×1080 or larger. Also used as the video poster.'],
                'background_video' => ['type' => 'video', 'label' => 'Background video (upload)'],
                'background_video_url' => ['type' => 'url', 'label' => 'Or background video URL (.mp4)', 'help' => 'Direct link to an MP4 file. Used when no video is uploaded.'],
                'overlay_opacity' => ['type' => 'number', 'label' => 'Dark overlay (%)', 'help' => '0 = none, 90 = very dark.', 'rules' => ['integer', 'min:0', 'max:95']],
                'height' => ['type' => 'number', 'label' => 'Height (% of screen)', 'rules' => ['integer', 'min:50', 'max:100']],
                'text_alignment' => ['type' => 'select', 'label' => 'Text alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right']],
                'badge_text' => ['type' => 'text', 'label' => 'Small badge above heading'],
                'primary_button_text' => ['type' => 'text', 'label' => 'Primary button text'],
                'primary_button_url' => ['type' => 'text', 'label' => 'Primary button link'],
                'secondary_button_text' => ['type' => 'text', 'label' => 'Secondary button text'],
                'secondary_button_url' => ['type' => 'text', 'label' => 'Secondary button link'],
                'show_quick_facts' => ['type' => 'boolean', 'label' => 'Show plot sizes card with monthly installments'],
            ],
            'intro' => [
                'image' => ['type' => 'image', 'label' => 'Image'],
                'show_statistics' => ['type' => 'boolean', 'label' => 'Show statistics (CMS → Statistics → Homepage)'],
            ] + $button,
            'plots' => $limit(8) + [
                'featured_only' => ['type' => 'boolean', 'label' => 'Show featured plots only'],
            ] + $button,
            'installment_plans', 'development' => $button,
            'features' => $limit(12),
            'testimonials' => $limit(6),
            'gallery', 'faq' => $limit(8) + $button,
            'contact_cta' => [
                'background_image' => ['type' => 'image', 'label' => 'Background image (optional)'],
                'show_form' => ['type' => 'boolean', 'label' => 'Show inquiry form'],
                'primary_button_text' => ['type' => 'text', 'label' => 'Button text'],
                'primary_button_url' => ['type' => 'text', 'label' => 'Button link'],
            ],
            'custom' => [
                'content' => ['type' => 'rich', 'label' => 'Content'],
                'image' => ['type' => 'image', 'label' => 'Image'],
                'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['image_right' => 'Text left, image right', 'image_left' => 'Image left, text right', 'text_only' => 'Text only']],
                'background' => ['type' => 'select', 'label' => 'Background', 'options' => ['light' => 'Light', 'white' => 'White', 'dark' => 'Dark (brand colour)']],
            ] + $button,
            default => [],
        };
    }

    /**
     * Validation rules for a section type's settings.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $type): array
    {
        $rules = [];

        foreach (self::for($type) as $key => $field) {
            $rules += match ($field['type']) {
                'image' => ["media.{$key}" => UploadRules::image()],
                'video' => ["media.{$key}" => UploadRules::video()],
                'boolean' => ["settings.{$key}" => ['nullable', 'boolean']],
                'number' => ["settings.{$key}" => ['nullable', 'integer', ...($field['rules'] ?? [])]],
                'select' => ["settings.{$key}" => ['nullable', 'in:'.implode(',', array_keys($field['options']))]],
                'url' => ["settings.{$key}" => ['nullable', 'url', 'max:1000']],
                'rich' => ["settings.{$key}" => ['nullable', 'string', 'max:100000']],
                default => ["settings.{$key}" => ['nullable', 'string', 'max:500']],
            };
        }

        return $rules;
    }
}
