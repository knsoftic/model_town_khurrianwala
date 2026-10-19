<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'slug', 'city', 'area', 'address', 'short_description', 'description',
    'latitude', 'longitude', 'google_maps_url', 'map_embed_url', 'landmarks',
    'distance_airport', 'distance_motorway', 'distance_main_road', 'image',
    'is_featured', 'is_active', 'sort_order',
])]
class Location extends Model
{
    use HasFactory, HasSeo, HasVisibilityAndOrder, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'landmarks' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    /**
     * Embed URL for the map iframe: the admin-supplied one, or one built from the coordinates/address.
     */
    public function mapEmbedSrc(): ?string
    {
        if ($this->map_embed_url) {
            return $this->map_embed_url;
        }

        if ($this->latitude && $this->longitude) {
            return 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude.'&z=14&output=embed';
        }

        $query = collect([$this->address, $this->area, $this->city])->filter()->implode(', ');

        return $query !== '' ? 'https://www.google.com/maps?q='.urlencode($query).'&z=14&output=embed' : null;
    }

    public function directionsUrl(): ?string
    {
        if ($this->google_maps_url) {
            return $this->google_maps_url;
        }

        if ($this->latitude && $this->longitude) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
        }

        return null;
    }

    /**
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    public function distances(): array
    {
        return array_values(array_filter([
            $this->distance_main_road ? ['label' => 'Main Road', 'value' => $this->distance_main_road, 'icon' => 'road'] : null,
            $this->distance_motorway ? ['label' => 'Motorway', 'value' => $this->distance_motorway, 'icon' => 'car-front'] : null,
            $this->distance_airport ? ['label' => 'Airport', 'value' => $this->distance_airport, 'icon' => 'plane'] : null,
        ]));
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->area, $this->city])->filter()->unique()->implode(', ');
    }
}
