<?php

namespace App\Models;

use App\Enums\PlotStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'location_id', 'size_label', 'slug', 'title', 'category', 'short_description', 'full_description',
    'dimensions', 'area_sqft', 'starting_price', 'total_price', 'booking_amount', 'down_payment',
    'installment_count', 'installment_amount', 'payment_frequency', 'after_6_months_payment',
    'after_12_months_payment', 'end_payment', 'development_charges', 'possession_charges',
    'total_units', 'sold_units', 'status', 'is_featured', 'is_active', 'is_demo_price',
    'price_note', 'image', 'sort_order',
])]
class Plot extends Model
{
    use HasFactory, HasSeo, HasVisibilityAndOrder, LogsActivity, SoftDeletes;

    /**
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'residential' => 'Residential',
        'commercial' => 'Commercial',
    ];

    /**
     * @var array<string, string>
     */
    public const FREQUENCIES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half_yearly' => 'Half-yearly',
        'yearly' => 'Yearly',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlotStatus::class,
            'starting_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'booking_amount' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'after_6_months_payment' => 'decimal:2',
            'after_12_months_payment' => 'decimal:2',
            'end_payment' => 'decimal:2',
            'development_charges' => 'decimal:2',
            'possession_charges' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_demo_price' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PlotImage::class)->orderBy('sort_order');
    }

    public function installmentPlans(): HasMany
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    public function activePlans(): HasMany
    {
        return $this->installmentPlans()->where('is_active', true)
            ->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id');
    }

    public function defaultPlan(): HasOne
    {
        return $this->hasOne(InstallmentPlan::class)->where('is_active', true)
            ->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function availableUnits(): int
    {
        return max(0, (int) $this->total_units - (int) $this->sold_units);
    }

    public function isBookable(): bool
    {
        return $this->status->isBookable();
    }

    public function displayPrice(): ?string
    {
        return $this->starting_price ?? $this->total_price;
    }

    public function frequencyLabel(): string
    {
        return self::FREQUENCIES[$this->payment_frequency] ?? ucfirst((string) $this->payment_frequency);
    }

    /**
     * Short unit suffix for the installment amount ("/ Month", "/ Quarter").
     */
    public function frequencySuffix(): string
    {
        return match ($this->payment_frequency) {
            'quarterly' => '/ Quarter',
            'half_yearly' => '/ 6 Months',
            'yearly' => '/ Year',
            default => '/ Month',
        };
    }
}
