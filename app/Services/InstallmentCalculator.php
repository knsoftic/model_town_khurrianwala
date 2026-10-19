<?php

namespace App\Services;

use App\Enums\PaymentStageType;
use App\Models\InstallmentPlan;
use App\Models\PaymentScheduleItem;
use App\Support\ScheduleSummary;

/**
 * Works out totals for a payment schedule. The admin schedule builder (resources/js/admin/schedule-builder.js)
 * mirrors these rules so the live preview matches what is saved.
 */
class InstallmentCalculator
{
    public function summarize(InstallmentPlan $plan): ScheduleSummary
    {
        $items = $plan->relationLoaded('items') ? $plan->items : $plan->items()->get();

        return $this->calculate((float) $plan->total_price, $items->all(), $plan->duration_months);
    }

    /**
     * @param  array<int, PaymentScheduleItem>  $items
     */
    public function calculate(float $totalPrice, array $items, ?int $durationOverride = null): ScheduleSummary
    {
        $scheduled = 0.0;
        $stages = [];
        $totalsByType = [];
        $installmentCount = 0;
        $installmentAmount = 0.0;
        $installmentType = null;
        $duration = 0;

        foreach ($items as $item) {
            $type = $item->type instanceof PaymentStageType ? $item->type : PaymentStageType::tryFrom((string) $item->type) ?? PaymentStageType::Custom;
            $occurrences = max(1, (int) $item->occurrences);
            $lineTotal = round((float) $item->amount * $occurrences, 2);
            $scheduled += $lineTotal;
            $totalsByType[$type->value] = ($totalsByType[$type->value] ?? 0) + $lineTotal;

            // The first repeating series (usually the monthly one) is "the installment";
            // a second series such as 6-month installments is shown as its own stage.
            if ($type->isRecurring()) {
                if ($installmentType === null) {
                    $installmentAmount = (float) $item->amount;
                    $installmentType = $type->value;
                }
                if ($type->value === $installmentType) {
                    $installmentCount += $occurrences;
                }
            }

            $start = $item->due_after_months ?? $type->defaultDueAfterMonths();
            $lastMonth = $start === null ? null : $start + ($type->isRecurring() ? ($occurrences - 1) * $type->intervalMonths() : 0);
            $duration = max($duration, (int) $lastMonth);

            $stages[] = [
                'item' => $item,
                'line_total' => $lineTotal,
                'percent' => $this->percent($lineTotal, $totalPrice),
                'cumulative' => round($scheduled, 2),
                'cumulative_percent' => $this->percent($scheduled, $totalPrice),
                'due_month' => $start,
            ];
        }

        $remaining = round($totalPrice - $scheduled, 2);
        $allocated = $this->percent($scheduled, $totalPrice);

        return new ScheduleSummary(
            totalPrice: $totalPrice,
            scheduledTotal: round($scheduled, 2),
            remaining: $remaining,
            allocatedPercent: $allocated,
            remainingPercent: round(100 - $allocated, 2),
            installmentCount: $installmentCount,
            installmentAmount: $installmentAmount,
            installmentType: $installmentType,
            durationMonths: $durationOverride ?: $duration,
            stages: $stages,
            totalsByType: $totalsByType,
        );
    }

    protected function percent(float $part, float $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 2) : 0.0;
    }
}
