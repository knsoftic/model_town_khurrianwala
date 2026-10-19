<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use App\Services\InstallmentCalculator;
use App\Support\ScheduleSummary;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'plot_id', 'name', 'slug', 'description', 'total_price', 'full_payment_price', 'duration_months',
    'terms', 'is_default', 'is_featured', 'is_active', 'is_demo', 'sort_order',
])]
class InstallmentPlan extends Model
{
    use HasFactory, HasVisibilityAndOrder, LogsActivity, SoftDeletes;

    protected ?ScheduleSummary $summaryCache = null;

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'full_payment_price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentScheduleItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    /**
     * Totals, percentages, installment count and duration derived from the schedule items.
     */
    public function summary(): ScheduleSummary
    {
        return $this->summaryCache ??= app(InstallmentCalculator::class)->summarize($this);
    }
}
