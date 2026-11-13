<?php

namespace App\Http\Controllers;

use App\Models\SalesTeam;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class SalesTeamController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $teams = SalesTeam::query()
            ->visibleTo($user)
            ->with(['manager:id,name,avatar', 'members:id,name,avatar'])
            ->orderBy('name')
            ->get()
            ->map(fn (SalesTeam $team): array => [
                'team' => $team,
                'member_count' => $team->memberships()->where('is_active', true)->count(),
                'month_sales' => $team->salesTotal($monthStart, $monthEnd),
                'can_manage' => $this->canManageTeam($user, $team),
            ]);

        return view('pages.sales.teams', [
            'teams' => $teams,
            'employees' => $this->assignableEmployees(),
            'canCreate' => $user->isSuperAdmin() || $user->can('teams.manage'),
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $team = SalesTeam::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? 'primary',
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        $this->syncMembers($team, $data['members'] ?? []);

        Activity::log('created', 'Sales team created: '.$team->name, $team, [], 'teams');

        return back()->with('status', 'Sales team created.');
    }

    public function update(Request $request, SalesTeam $team): RedirectResponse
    {
        if (! $this->canManageTeam($request->user(), $team)) {
            return back()->withErrors(['team' => 'You cannot manage this team.']);
        }

        $data = $this->validated($request);

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? $team->color,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (array_key_exists('members', $data)) {
            $this->syncMembers($team, $data['members'] ?? []);
        }

        Activity::log('updated', 'Sales team updated: '.$team->name, $team, [], 'teams');

        return back()->with('status', 'Sales team updated.');
    }

    public function destroy(Request $request, SalesTeam $team): RedirectResponse
    {
        if (! $this->canManageTeam($request->user(), $team)) {
            return back()->withErrors(['team' => 'You cannot delete this team.']);
        }

        Activity::log('deleted', 'Sales team deleted: '.$team->name, null, ['team_id' => $team->id], 'teams');

        $team->delete();

        return back()->with('status', 'Sales team deleted.');
    }

    public function addMembers(Request $request, SalesTeam $team): RedirectResponse
    {
        if (! $this->canManageTeam($request->user(), $team)) {
            return back()->withErrors(['team' => 'You cannot manage this team.']);
        }

        $data = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', Rule::exists('users', 'id')],
        ]);

        $this->syncMembers($team, $data['user_ids']);

        Activity::log('members_updated', 'Team members updated for '.$team->name, $team, [], 'teams');

        return back()->with('status', 'Team members updated.');
    }

    public function removeMember(Request $request, SalesTeam $team, User $user): RedirectResponse
    {
        if (! $this->canManageTeam($request->user(), $team)) {
            return back()->withErrors(['team' => 'You cannot manage this team.']);
        }

        $team->memberships()->where('user_id', $user->id)->delete();

        Activity::log('member_removed', $user->name.' removed from '.$team->name, $team, [], 'teams');

        return back()->with('status', $user->name.' removed from '.$team->name.'.');
    }

    private function canManageTeam(User $user, SalesTeam $team): bool
    {
        if ($user->isSuperAdmin() || $user->can('teams.manage')) {
            return true;
        }

        if (! $user->can('teams.manage_members')) {
            return false;
        }

        return $team->isManagedBy($user)
            || $team->memberships()->where('user_id', $user->id)->exists();
    }

    /**
     * @return Collection<int, User>
     */
    private function assignableEmployees(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar']);
    }

    /**
     * @param  array<int, int|string>  $userIds
     */
    private function syncMembers(SalesTeam $team, array $userIds): void
    {
        $userIds = collect($userIds)->map(fn (mixed $id): int => (int) $id)->unique()->values();

        $existing = $team->memberships()->pluck('user_id');

        $team->memberships()->whereIn('user_id', $existing->diff($userIds)->all())->delete();

        foreach ($userIds->diff($existing) as $userId) {
            $team->memberships()->create([
                'user_id' => $userId,
                'role' => 'member',
                'joined_at' => now(),
                'is_active' => true,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:30'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', 'exists:users,id'],
        ]);
    }
}
