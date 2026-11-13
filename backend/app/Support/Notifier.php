<?php

namespace App\Support;

use App\Models\SalesTeam;
use App\Models\SalesTeamMember;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Collection;

class Notifier
{
    /**
     * Send an in-app (database) notification to one or many users.
     *
     * @param  User|Collection<int, User>|array<int, User>|null  $users
     */
    public static function send(User|Collection|array|null $users, string $title, ?string $message = null, ?string $url = null, string $icon = 'bx bx-bell'): void
    {
        $recipients = ($users instanceof User ? collect([$users]) : collect($users))
            ->filter(fn (mixed $user): bool => $user instanceof User && $user->is_active)
            ->unique('id');

        foreach ($recipients as $user) {
            $user->notify(new AppNotification($title, $message, $url, $icon));
        }
    }

    /**
     * Managers of the user's sales teams plus their reporting manager.
     *
     * @return Collection<int, User>
     */
    public static function managersOf(User $user): Collection
    {
        $teamIds = SalesTeamMember::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('sales_team_id');

        $managerIds = SalesTeam::query()
            ->whereIn('id', $teamIds)
            ->whereNotNull('manager_id')
            ->pluck('manager_id');

        if ($user->reports_to !== null) {
            $managerIds->push($user->reports_to);
        }

        $managerIds = $managerIds->unique()->filter(fn (int $id): bool => $id !== $user->id);

        return User::query()->whereIn('id', $managerIds)->get();
    }

    /**
     * Administrators visible to the given user (super admins + org admins).
     *
     * @return Collection<int, User>
     */
    public static function admins(?User $except = null): Collection
    {
        return User::query()
            ->where(function ($query): void {
                $query->where('is_super_admin', true)
                    ->orWhereHas('assignedRole', fn ($query) => $query->whereIn('slug', ['super-admin', 'admin', 'organization-admin']));
            })
            ->where('is_active', true)
            ->when($except !== null, fn ($query) => $query->whereKeyNot($except->id))
            ->get();
    }

    /**
     * Active users holding any of the given role slugs.
     *
     * @return Collection<int, User>
     */
    public static function withRole(string ...$slugs): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('assignedRole', fn ($query) => $query->whereIn('slug', $slugs))
            ->get();
    }

    /**
     * Managers + admins for the given user, deduplicated.
     *
     * @return Collection<int, User>
     */
    public static function escalateTo(User $user): Collection
    {
        return static::managersOf($user)
            ->merge(static::admins($user))
            ->unique('id')
            ->values();
    }
}
