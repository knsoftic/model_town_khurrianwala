<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'avatar', 'password', 'role_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Permission slugs cached for the current request.
     *
     * @var array<int, string>|null
     */
    protected ?array $permissionCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->loadMissing('role')->role?->is_super;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->permissionCache ??= $this->loadMissing('role.permissions')->role?->permissions->pluck('slug')->all() ?? [];

        return in_array($permission, $this->permissionCache, true);
    }

    public function canAccessAdmin(): bool
    {
        return $this->is_active && $this->role_id !== null;
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }
}
