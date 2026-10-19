<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $action, string $description, ?Model $subject = null, array $properties = [], ?int $userId = null): void
    {
        try {
            ActivityLog::query()->create([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => Str::limit($description, 490),
                'properties' => $properties ?: null,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 490),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
