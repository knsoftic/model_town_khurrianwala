<?php

namespace App\Services;

use App\Enums\PaymentStageType;
use App\Models\InstallmentPlan;
use Illuminate\Support\Facades\DB;

class InstallmentPlanService
{
    /**
     * Create or update a plan and replace its schedule with the submitted stages (in order).
     *
     * @param  array<string, mixed>  $data
     */
    public function save(InstallmentPlan $plan, array $data): InstallmentPlan
    {
        return DB::transaction(function () use ($plan, $data) {
            $items = $data['items'];
            unset($data['items']);

            $plan->fill($data)->save();

            $total = (float) $plan->total_price;
            $plan->items()->delete();

            foreach (array_values($items) as $position => $item) {
                $occurrences = max(1, (int) $item['occurrences']);
                $plan->items()->create([
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'amount' => $item['amount'],
                    'occurrences' => PaymentStageType::from($item['type'])->isRecurring() ? $occurrences : 1,
                    'percentage' => $total > 0 ? round((float) $item['amount'] * $occurrences / $total * 100, 2) : null,
                    'due_after_months' => $item['due_after_months'] ?? null,
                    'due_label' => $item['due_label'] ?? null,
                    'description' => $item['description'] ?? null,
                    'sort_order' => $position,
                ]);
            }

            if ($plan->is_default) {
                InstallmentPlan::query()->where('plot_id', $plan->plot_id)->whereKeyNot($plan->id)->update(['is_default' => false]);
            }

            return $plan->load('items');
        });
    }

    /**
     * Copy the plan's schedule figures onto its plot's card fields.
     */
    public function applyToPlot(InstallmentPlan $plan): void
    {
        $plan->loadMissing(['items', 'plot']);
        $summary = $plan->summary();
        $first = fn (string $type) => optional($plan->items->first(fn ($item) => $item->type->value === $type))->amount;

        $plan->plot->update([
            'total_price' => $summary->totalPrice,
            'starting_price' => $plan->full_payment_price ?? $summary->totalPrice,
            'booking_amount' => $summary->amountFor('booking') ?: null,
            'down_payment' => $summary->amountFor('advance', 'full_payment') ?: null,
            'installment_amount' => $summary->installmentAmount ?: null,
            'installment_count' => $summary->installmentCount ?: null,
            'payment_frequency' => $summary->installmentType === 'quarterly' ? 'quarterly' : 'monthly',
            'after_6_months_payment' => $first('after_6_months'),
            'after_12_months_payment' => $first('after_12_months'),
            'end_payment' => $summary->amountFor('end_payment') ?: null,
        ]);
    }
}
