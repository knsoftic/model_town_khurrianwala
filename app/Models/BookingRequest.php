<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference', 'plot_id', 'installment_plan_id', 'lead_id', 'name', 'phone', 'email', 'cnic', 'city',
    'preferred_contact_method', 'message', 'status', 'assigned_to', 'reviewed_by', 'reviewed_at',
    'ip_address', 'user_agent',
])]
#[Hidden(['cnic'])]
class BookingRequest extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'cnic' => 'encrypted',
            'reviewed_at' => 'datetime',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class)->withTrashed();
    }

    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class)->withTrashed();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query->where('reference', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * CNIC with the middle digits hidden, for list views.
     */
    public function maskedCnic(): ?string
    {
        if (blank($this->cnic)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->cnic);

        return strlen($digits) >= 6 ? substr($digits, 0, 3).str_repeat('•', strlen($digits) - 5).substr($digits, -2) : '•••••';
    }
}
