<?php

namespace App\Models;

use App\Enums\PaymentStageType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'installment_plan_id', 'type', 'title', 'amount', 'occurrences', 'percentage',
    'due_after_months', 'due_label', 'description', 'sort_order',
])]
class PaymentScheduleItem extends Model
{
    protected function casts(): array
    {
        return [
            'type' => PaymentStageType::class,
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
            'occurrences' => 'integer',
            'due_after_months' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }

    /**
     * Amount multiplied by how many times the stage occurs (e.g. 24 monthly installments).
     */
    public function lineTotal(): float
    {
        return round((float) $this->amount * max(1, (int) $this->occurrences), 2);
    }
}
