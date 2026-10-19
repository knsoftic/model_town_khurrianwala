<?php

namespace App\Http\Resources;

use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plot
 */
class PlotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = fn ($value) => $value !== null ? (float) $value : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'size_label' => $this->size_label,
            'title' => $this->title,
            'category' => $this->category,
            'dimensions' => $this->dimensions,
            'area_sqft' => $this->area_sqft,
            'short_description' => $this->short_description,
            'status' => ['value' => $this->status->value, 'label' => $this->status->label(), 'bookable' => $this->isBookable()],
            'is_featured' => $this->is_featured,
            'is_demo_price' => $this->is_demo_price,
            'price_note' => $this->price_note,
            'prices' => [
                'starting_price' => $amount($this->starting_price),
                'total_price' => $amount($this->total_price),
                'booking_amount' => $amount($this->booking_amount),
                'down_payment' => $amount($this->down_payment),
                'installment_amount' => $amount($this->installment_amount),
                'installment_count' => $this->installment_count,
                'payment_frequency' => $this->payment_frequency,
                'after_6_months_payment' => $amount($this->after_6_months_payment),
                'after_12_months_payment' => $amount($this->after_12_months_payment),
                'end_payment' => $amount($this->end_payment),
                'development_charges' => $amount($this->development_charges),
                'possession_charges' => $amount($this->possession_charges),
            ],
            'image' => $this->image ? url(media_url($this->image)) : null,
            'url' => route('plots.show', $this->resource),
            'book_url' => route('book-plot', ['plot' => $this->slug]),
            'location' => $this->whenLoaded('location', fn () => $this->location ? ['name' => $this->location->name, 'slug' => $this->location->slug] : null),
            'plans' => InstallmentPlanResource::collection($this->whenLoaded('activePlans')),
        ];
    }
}
