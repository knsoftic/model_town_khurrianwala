<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'action', 'subject_type', 'subject_id', 'description', 'properties', 'ip_address', 'user_agent'])]
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionClasses(): string
    {
        return match ($this->action) {
            'created' => 'bg-emerald-100 text-emerald-800',
            'updated' => 'bg-sky-100 text-sky-800',
            'deleted' => 'bg-rose-100 text-rose-800',
            'login' => 'bg-indigo-100 text-indigo-800',
            'login_failed' => 'bg-amber-100 text-amber-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
