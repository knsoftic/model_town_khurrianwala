<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'label', 'url', 'icon', 'sort_order', 'is_active'])]
class SocialLink extends Model
{
    use HasVisibilityAndOrder, LogsActivity;

    /**
     * Supported platforms => [label, icon].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const PLATFORMS = [
        'facebook' => ['Facebook', 'facebook'],
        'instagram' => ['Instagram', 'instagram'],
        'youtube' => ['YouTube', 'youtube'],
        'tiktok' => ['TikTok', 'tiktok'],
        'linkedin' => ['LinkedIn', 'linkedin'],
        'whatsapp' => ['WhatsApp', 'whatsapp'],
        'twitter' => ['X / Twitter', 'x-twitter'],
        'website' => ['Website', 'globe'],
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function displayLabel(): string
    {
        return $this->label ?: (self::PLATFORMS[$this->platform][0] ?? ucfirst($this->platform));
    }

    public function iconName(): string
    {
        return $this->icon ?: (self::PLATFORMS[$this->platform][1] ?? 'link');
    }
}
