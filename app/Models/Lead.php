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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'plot_id', 'name', 'phone', 'email', 'budget', 'payment_preference', 'preferred_contact_method',
    'preferred_time', 'message', 'extra', 'source', 'status', 'assigned_to', 'created_by', 'follow_up_date', 'last_contacted_at',
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
            'extra' => 'array',
        ];
    }

    /**
     * The staff member who added the lead by hand (empty for website leads).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    /**
     * The earliest follow-up that is not done yet.
     */
    public function nextFollowUp(): HasOne
    {
        return $this->hasOne(LeadFollowUp::class)->ofMany(['due_date' => 'min', 'id' => 'min'], fn (Builder $query) => $query->whereNull('done_at'));
    }

    /**
     * The follow-up that was done most recently.
     */
    public function lastFollowUp(): HasOne
    {
        return $this->hasOne(LeadFollowUp::class)->ofMany(['done_at' => 'max', 'id' => 'max'], fn (Builder $query) => $query->whereNotNull('done_at'));
    }

    /**
     * Leads this user may work on: every lead with the "leads" permission, otherwise
     * ("my_leads") only the ones they added or that are assigned to them.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->can('leads')) {
            return;
        }

        $query->where(fn (Builder $query) => $query->where('created_by', $user->id)->orWhere('assigned_to', $user->id));
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->can('leads')
            || ($user->can('my_leads') && in_array($user->id, array_map(intval(...), array_filter([$this->created_by, $this->assigned_to])), true));
    }

    /**
     * Keep follow_up_date on the earliest follow-up that is not done yet (used by lists and filters).
     */
    public function syncNextFollowUp(): void
    {
        $next = $this->followUps()->whereNull('done_at')->min('due_date');

        $this->forceFill(['follow_up_date' => $next])->saveQuietly();
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
