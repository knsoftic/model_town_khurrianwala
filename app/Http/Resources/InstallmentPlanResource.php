<?php

namespace App\Http\Resources;

use App\Models\InstallmentPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InstallmentPlan
 */
class InstallmentPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'total_price' => (float) $this->total_price,
            'full_payment_price' => $this->full_payment_price !== null ? (float) $this->full_payment_price : null,
            'duration_months' => $this->summary()->durationMonths,
            'is_default' => $this->is_default,
            'is_demo' => $this->is_demo,
            'summary' => $this->when($this->relationLoaded('items'), fn () => $this->summary()->toArray()),
            'items' => PaymentScheduleItemResource::collection($this->whenLoaded('items')),
            'book_url' => $this->relationLoaded('plot') && $this->plot
                ? route('book-plot', ['plot' => $this->plot->slug, 'plan' => $this->slug])
                : null,
        ];
    }
}
