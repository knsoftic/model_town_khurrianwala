<?php

namespace Tests\Unit;

use App\Models\PaymentScheduleItem;
use App\Services\InstallmentCalculator;
use Tests\TestCase;

class InstallmentCalculatorTest extends TestCase
{
    protected function item(string $type, float $amount, int $occurrences = 1, ?int $due = null): PaymentScheduleItem
    {
        return new PaymentScheduleItem(['type' => $type, 'title' => $type, 'amount' => $amount, 'occurrences' => $occurrences, 'due_after_months' => $due]);
    }

    public function test_it_totals_a_balanced_schedule(): void
    {
        $summary = (new InstallmentCalculator)->calculate(2950000, [
            $this->item('booking', 150000, 1, 0),
            $this->item('advance', 450000, 1, 1),
            $this->item('monthly', 45000, 36, 1),
            $this->item('after_6_months', 200000, 1, 6),
            $this->item('after_12_months', 200000, 1, 12),
            $this->item('end_payment', 330000, 1, 36),
        ]);

        $this->assertSame(2950000.0, $summary->scheduledTotal);
        $this->assertTrue($summary->isBalanced());
        $this->assertSame(100.0, $summary->allocatedPercent);
        $this->assertSame(36, $summary->installmentCount);
        $this->assertSame(45000.0, $summary->installmentAmount);
        $this->assertSame(36, $summary->durationMonths);
        $this->assertSame(54.92, $summary->stages[2]['percent']);
    }

    public function test_it_reports_remaining_amount_and_quarterly_duration(): void
    {
        $summary = (new InstallmentCalculator)->calculate(1000000, [
            $this->item('booking', 100000, 1, 0),
            $this->item('quarterly', 100000, 4, 3),
        ]);

        $this->assertSame(500000.0, $summary->remaining);
        $this->assertSame(50.0, $summary->remainingPercent);
        $this->assertSame(12, $summary->durationMonths);
        $this->assertSame('quarterly', $summary->installmentType);
    }
}
