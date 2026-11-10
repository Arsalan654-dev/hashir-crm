<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('roles')]
#[Fillable(['name', 'slug', 'description', 'color', 'is_system', 'is_active', 'organization_id'])]
class Role extends Model
{
    public const SUPER_ADMIN = 'super-admin';

    public const ORGANIZATION_ADMIN = 'organization-admin';

    public const SUPPORT_AGENT = 'support-agent';

    public const KITCHEN_STAFF = 'kitchen-staff';

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null || $user->isSuperAdmin() || $user->organization_id === null) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->whereNull('organization_id')->orWhere('organization_id', $user->organization_id);
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }
}
