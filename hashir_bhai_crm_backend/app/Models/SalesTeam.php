<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Table('sales_teams')]
#[Fillable(['organization_id', 'name', 'description', 'color', 'manager_id', 'is_active', 'created_by'])]
class SalesTeam extends Model
{
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(SalesTeamMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sales_team_members')
            ->withPivot(['role', 'joined_at', 'is_active', 'organization_id'])
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null || $user->isSuperAdmin() || $user->can('teams.manage') || $user->can('sales.view_all')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->where('manager_id', $user->id)
                ->orWhereHas('memberships', fn (Builder $query) => $query->where('user_id', $user->id));
        });
    }

    public function isManagedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $this->manager_id === (int) $user->id;
    }

    /**
     * @return Collection<int, int>
     */
    public function memberIds(): Collection
    {
        $ids = $this->memberships()->where('is_active', true)->pluck('user_id');

        if ($this->manager_id !== null) {
            $ids->push($this->manager_id);
        }

        return $ids->unique()->values();
    }

    public function salesTotal(?Carbon $start = null, ?Carbon $end = null): float
    {
        $ids = $this->memberIds();

        if ($ids->isEmpty()) {
            return 0.0;
        }

        return (float) Sale::query()
            ->whereIn('salesperson_id', $ids)
            ->completed()
            ->when($start !== null && $end !== null, fn (Builder $query) => $query->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()]))
            ->sum('total');
    }
}
