<?php

namespace App\Support;

/**
 * Computed view of an installment plan's payment schedule.
 */
final readonly class ScheduleSummary
{
    /**
     * @param  array<int, array{item: mixed, line_total: float, percent: float, cumulative: float, cumulative_percent: float, due_month: int|null}>  $stages
     * @param  array<string, float>  $totalsByType
     */
    public function __construct(
        public float $totalPrice,
        public float $scheduledTotal,
        public float $remaining,
        public float $allocatedPercent,
        public float $remainingPercent,
        public int $installmentCount,
        public float $installmentAmount,
        public ?string $installmentType,
        public int $durationMonths,
        public array $stages,
        public array $totalsByType,
    ) {}

    public function isBalanced(): bool
    {
        return abs($this->remaining) < 1;
    }

    public function amountFor(string ...$types): float
    {
        return array_sum(array_map(fn (string $type) => $this->totalsByType[$type] ?? 0.0, $types));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_price' => $this->totalPrice,
            'scheduled_total' => $this->scheduledTotal,
            'remaining' => $this->remaining,
            'allocated_percent' => $this->allocatedPercent,
            'remaining_percent' => $this->remainingPercent,
            'installment_count' => $this->installmentCount,
            'installment_amount' => $this->installmentAmount,
            'installment_type' => $this->installmentType,
            'duration_months' => $this->durationMonths,
            'is_balanced' => $this->isBalanced(),
            'totals_by_type' => $this->totalsByType,
        ];
    }
}
