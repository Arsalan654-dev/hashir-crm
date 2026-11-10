<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model): void {
            if ($model->organization_id === null) {
                $model->organization_id = static::currentOrganizationId();
            }
        });

        static::addGlobalScope('organization', function (Builder $builder): void {
            $organizationId = static::currentOrganizationId();

            if ($organizationId !== null) {
                $builder->where($builder->getModel()->getTable().'.organization_id', $organizationId);
            }
        });
    }

    public function initializeBelongsToOrganization(): void
    {
        $this->mergeFillable(['organization_id']);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForOrganization(Builder $query, Organization|int|null $organization): Builder
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;
        $column = $query->getModel()->getTable().'.organization_id';

        return $organizationId === null
            ? $query->whereNull($column)
            : $query->where($column, $organizationId);
    }

    public static function currentOrganizationId(): ?int
    {
        $container = app();

        // Resolving the authenticated user runs an Eloquent query on the User model,
        // which triggers this scope again. While that resolution is in progress we
        // must not scope the query, otherwise we recurse forever.
        if ($container->bound('organization_scope_resolving')) {
            return null;
        }

        $container->instance('organization_scope_resolving', true);

        try {
            $user = auth()->user();
        } finally {
            $container->forgetInstance('organization_scope_resolving');
        }

        if ($user === null || $user->isSuperAdmin()) {
            return null;
        }

        return $user->organization_id;
    }

    public static function currentOrganization(): ?Organization
    {
        $organizationId = static::currentOrganizationId();

        return $organizationId === null ? null : Organization::query()->find($organizationId);
    }
}
