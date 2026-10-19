<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Records create/update/delete actions performed by signed-in staff.
 * Only the names of changed attributes are stored, never their values,
 * so sensitive fields (passwords, CNIC, SMTP secrets) never reach the log.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $model) use ($event) {
                if (! auth()->check()) {
                    return;
                }

                $changed = [];

                if ($event === 'updated') {
                    $changed = array_values(array_diff(array_keys($model->getChanges()), ['updated_at']));

                    if ($changed === []) {
                        return;
                    }
                }

                app(ActivityLogger::class)->log(
                    $event,
                    sprintf('%s %s "%s"', ucfirst($event), Str::lower(Str::headline(class_basename($model))), $model->activityName()),
                    $model,
                    $changed === [] ? [] : ['changed' => $changed],
                );
            });
        }
    }

    public function activityName(): string
    {
        foreach (['title', 'name', 'client_name', 'question', 'label', 'reference', 'key'] as $attribute) {
            if (filled($this->getAttribute($attribute))) {
                return Str::limit((string) $this->getAttribute($attribute), 80);
            }
        }

        return '#'.$this->getKey();
    }
}
