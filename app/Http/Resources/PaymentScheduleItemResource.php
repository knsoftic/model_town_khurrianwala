<?php

namespace App\Http\Resources;

use App\Models\PaymentScheduleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentScheduleItem
 */
class PaymentScheduleItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'is_recurring' => $this->type->isRecurring(),
            'icon' => $this->type->icon(),
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'occurrences' => (int) $this->occurrences,
            'line_total' => $this->lineTotal(),
            'repeat_text' => $this->type->isRecurring() && $this->occurrences > 1 ? $this->type->repeatText((int) $this->occurrences) : null,
            'due_after_months' => $this->due_after_months,
            'due_label' => $this->due_label,
            'description' => $this->description,
        ];
    }
}
