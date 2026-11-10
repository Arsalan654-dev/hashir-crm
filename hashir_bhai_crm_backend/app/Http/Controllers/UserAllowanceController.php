<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAllowance;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAllowanceController extends Controller
{
    public function index(Request $request): View
    {
        $allowances = UserAllowance::query()
            ->with(['user:id,name,avatar', 'creator:id,name'])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->orderByDesc('is_active')
            ->orderBy('user_id')
            ->paginate(25)
            ->withQueryString();

        $activeMonthly = UserAllowance::query()->where('is_active', true)->where('recurrence', 'monthly');

        return view('pages.payroll.allowances', [
            'allowances' => $allowances,
            'employees' => User::query()->whereNotNull('role_id')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'types' => UserAllowance::TYPE_LABELS,
            'recurrences' => UserAllowance::RECURRENCES,
            'summary' => [
                'allowances' => (float) (clone $activeMonthly)->where('type', 'allowance')->sum('amount'),
                'deductions' => (float) (clone $activeMonthly)->where('type', 'deduction')->sum('amount'),
                'employees' => (int) UserAllowance::query()->where('is_active', true)->distinct()->count('user_id'),
            ],
            'filters' => $request->only(['user_id', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $allowance = UserAllowance::query()->create($data + [
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        Activity::log('created', 'Allowance added for user #'.$allowance->user_id, $allowance, [], 'payroll');

        return back()->with('status', 'Allowance added.');
    }

    public function update(Request $request, UserAllowance $allowance): RedirectResponse
    {
        $data = $this->validated($request);

        $allowance->update($data + ['is_active' => $request->boolean('is_active', true)]);

        Activity::log('updated', 'Allowance updated for user #'.$allowance->user_id, $allowance, [], 'payroll');

        return back()->with('status', 'Allowance updated.');
    }

    public function destroy(UserAllowance $allowance): RedirectResponse
    {
        Activity::log('deleted', 'Allowance removed for user #'.$allowance->user_id, null, ['allowance_id' => $allowance->id], 'payroll');

        $allowance->delete();

        return back()->with('status', 'Allowance removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'label' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(UserAllowance::TYPES)],
            'amount' => ['required', 'numeric', 'min:0'],
            'recurrence' => ['required', Rule::in(UserAllowance::RECURRENCES)],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
