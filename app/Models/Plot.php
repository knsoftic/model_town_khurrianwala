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

    /**
     * Title, description and keywords of the plot page when its SEO box is left empty
     * (needs location loaded).
     *
     * @return array{title: string, description: string|null, keywords: string}
     */
    public function seoDefaults(): array
    {
        return [
            'title' => $this->location?->city ? "{$this->title} in {$this->location->city}" : (string) $this->title,
            'description' => $this->short_description,
            'keywords' => implode(', ', $this->seoKeywords()),
        ];
    }

    /**
     * Search phrases for this plot size ("5 marla plot for sale in Faisalabad"), used as the
     * plot page's keywords when none are entered in its SEO box.
     *
     * @return array<int, string>
     */
    public function seoKeywords(): array
    {
        $size = mb_strtolower((string) $this->size_label);
        $in = ($city = $this->location?->city) ? " in {$city}" : '';
        $area = $this->location?->area;

        return array_values(array_filter([
            "{$size} plot for sale{$in}",
            "{$size} plot on installments{$in}",
            "{$size} plot price{$in}",
            $area && $area !== $city ? "{$size} plot in {$area}" : null,
            "{$size} plot ".setting('site_name'),
        ]));
    }

    /**
     * The default plan's stage of one of these types (needs activePlans.items loaded),
     * so cards and tables use the plan's own wording — "Down Payment", "On Registry".
     *
     * @param  array<int, string>|string  $types
     */
    public function stage(array|string $types): ?PaymentScheduleItem
    {
        if (! $this->relationLoaded('activePlans') || ! ($plan = $this->activePlans->first())?->relationLoaded('items')) {
            return null;
        }

        return $plan->items->first(fn (PaymentScheduleItem $item) => in_array($item->type?->value, (array) $types, true));
    }

    /**
     * @param  array<int, string>|string  $types
     */
    public function stageTitle(array|string $types, string $fallback): string
    {
        return $this->stage($types)?->title ?: $fallback;
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
