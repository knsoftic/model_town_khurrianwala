<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['lead_id', 'name', 'email', 'phone', 'subject', 'message', 'read_at', 'ip_address', 'user_agent'])]
class ContactMessage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
