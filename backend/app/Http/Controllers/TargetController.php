<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Target;
use App\Models\User;
use App\Support\Activity;
use App\Support\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TargetController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $seesEveryone = $user->isSuperAdmin() || $user->can('targets.view_all');
        $scopeIds = $seesEveryone ? null : $user->teamMemberIds();

        $targets = Target::query()
            ->with('user:id,name,avatar')
            ->when($scopeIds !== null, fn ($query) => $query->whereIn('user_id', $scopeIds))
            ->when(
                $request->filled('user_id') && ($seesEveryone || $scopeIds?->contains($request->integer('user_id'))),
                fn ($query) => $query->where('user_id', $request->integer('user_id')),
            )
            ->orderByDesc('period_start')
            ->paginate(20)
            ->withQueryString();

        return view('pages.hr.targets', [
            'targets' => $targets,
            'employees' => $this->assignableUsers($user),
            'leaderboard' => $seesEveryone ? $this->leaderboard() : collect(),
        ]);
    }

    public function my(Request $request): View
    {
        $targets = Target::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('period_start')
            ->get();

        return view('pages.hr.targets-my', [
            'targets' => $targets,
            'achieved' => $this->achievement($request->user()->id),
        ]);
    }

    public function create(Request $request): View
    {
        return view('pages.hr.targets-form', [
            'employees' => $this->assignableUsers($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTarget($request);

        if (! $this->canAssignTargetTo($request->user(), (int) $data['user_id'])) {
            return back()->withErrors(['user_id' => 'You can only set targets for your own team members.'])->withInput();
        }

        $target = Target::query()->create($data + ['created_by' => $request->user()->id]);

        Activity::log('created', 'Target set for user #'.$target->user_id, $target, [], 'targets');

        $member = User::query()->find($target->user_id);

        if ($member !== null) {
            Notifier::send(
                $member,
                'New sales target assigned',
                ($target->title ? $target->title.': ' : '').number_format((float) $target->target_amount, 2).' target for '.$target->period_start->format('d M').' — '.$target->period_end->format('d M'),
                route('targets.my'),
                'bx bx-target-lock',
            );
        }

        return redirect()->route('targets.index')->with('status', 'Target created.');
    }

    public function update(Request $request, Target $target): RedirectResponse
    {
        $data = $this->validateTarget($request);

        if (! $this->canAssignTargetTo($request->user(), (int) $data['user_id'])) {
            return back()->withErrors(['user_id' => 'You can only set targets for your own team members.']);
        }

        $target->update($data);

        return back()->with('status', 'Target updated.');
    }

    public function destroy(Target $target): RedirectResponse
    {
        if (! $this->canAssignTargetTo(request()->user(), (int) $target->user_id)) {
            return back()->withErrors(['target' => 'You can only manage targets for your own team members.']);
        }

        $target->delete();

        return back()->with('status', 'Target deleted.');
    }

    /**
     * Users a manager may assign targets to: everyone with view-all, otherwise own team.
     *
     * @return Collection<int, User>
     */
    private function assignableUsers(User $user): Collection
    {
        if ($user->isSuperAdmin() || $user->can('targets.view_all')) {
            return User::query()->whereNotNull('role_id')->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        }

        return User::query()
            ->whereIn('id', $user->teamMemberIds())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function canAssignTargetTo(User $user, int $userId): bool
    {
        if ($user->isSuperAdmin() || $user->can('targets.view_all')) {
            return true;
        }

        return $user->teamMemberIds()->contains($userId);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTarget(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'period_type' => ['required', 'in:'.implode(',', Target::PERIODS)],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'target_orders' => ['nullable', 'integer', 'min:0'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function leaderboard(): Collection
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        return Target::query()
            ->with('user:id,name,avatar')
            ->whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(fn (Target $target) => [
                'user' => $target->user,
                'target' => (float) $target->target_amount,
                'achieved' => $target->achievedAmount(),
                'progress' => $target->progressPercent(),
            ])
            ->sortByDesc('achieved')
            ->values();
    }

    private function achievement(int $userId): float
    {
        return (float) Sale::query()
            ->where('salesperson_id', $userId)
            ->completed()
            ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');
    }
}
