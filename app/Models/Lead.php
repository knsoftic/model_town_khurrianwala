<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'plot_id', 'name', 'phone', 'email', 'budget', 'payment_preference', 'preferred_contact_method',
    'preferred_time', 'message', 'source', 'status', 'assigned_to', 'follow_up_date', 'last_contacted_at',
    'page_url', 'ip_address', 'user_agent',
])]
class Lead extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @var array<string, string>
     */
    public const PAYMENT_PREFERENCES = [
        'installment' => 'Installment Plan',
        'full_payment' => 'Full / Lump-sum Payment',
        'undecided' => 'Not decided yet',
    ];

    /**
     * @var array<string, string>
     */
    public const CONTACT_METHODS = [
        'phone' => 'Phone Call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'follow_up_date' => 'date',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class)->withTrashed();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function bookingRequest(): HasOne
    {
        return $this->hasOne(BookingRequest::class);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function paymentPreferenceLabel(): ?string
    {
        return self::PAYMENT_PREFERENCES[$this->payment_preference] ?? $this->payment_preference;
    }

    public function contactMethodLabel(): ?string
    {
        return self::CONTACT_METHODS[$this->preferred_contact_method] ?? $this->preferred_contact_method;
    }

    public function isFollowUpDue(): bool
    {
        return $this->follow_up_date !== null && $this->follow_up_date->isPast()
            && ! in_array($this->status, [LeadStatus::Converted, LeadStatus::Cancelled], true);
    }
}
