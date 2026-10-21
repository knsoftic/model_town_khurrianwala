<?php

namespace App\Models;

use Database\Factories\LeadFollowUpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A planned call-back for a lead: when it is due, whether it was done, and what came of it.
 * The lead's follow_up_date always shows its next follow-up that is not done yet.
 */
#[Fillable(['lead_id', 'user_id', 'due_date', 'due_time', 'note', 'done_at', 'done_by', 'outcome'])]
class LeadFollowUp extends Model
{
    /** @use HasFactory<LeadFollowUpFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (self $followUp) => $followUp->lead?->syncNextFollowUp());
        static::deleted(fn (self $followUp) => $followUp->lead?->syncNextFollowUp());
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'done_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by')->withTrashed();
    }

    public function isDone(): bool
    {
        return $this->done_at !== null;
    }

    /**
     * Due date and time ("21 Oct 2026, 03:00 PM", or the date alone when no time was set).
     */
    public function dueLabel(): string
    {
        return $this->due_date->format('d M Y').($this->due_time ? ', '.Carbon::parse($this->due_time)->format('h:i A') : '');
    }

    /**
     * overdue, today or upcoming — for a follow-up that is not done yet.
     */
    public function state(): string
    {
        return match (true) {
            $this->isDone() => 'done',
            $this->due_date->isToday() => $this->due_time && Carbon::parse($this->due_time)->isPast() ? 'overdue' : 'today',
            $this->due_date->isPast() => 'overdue',
            default => 'upcoming',
        };
    }
}
