<?php

namespace App\Http\Controllers;

use App\Models\Compensation;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompensationController extends Controller
{
    public function index(Request $request): View
    {
        $compensations = Compensation::query()
            ->with(['user:id,name,avatar,department_id', 'creator:id,name'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('month'), fn ($query) => $query->where('period_month', $request->string('month')))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return view('pages.hr.compensations', [
            'compensations' => $compensations,
            'employees' => User::query()->whereNotNull('role_id')->orderBy('name')->get(['id', 'name']),
            'totals' => [
                'bonus' => (float) Compensation::query()->whereIn('type', ['bonus', 'appreciation'])->sum('amount'),
                'incentive' => (float) Compensation::query()->where('type', 'incentive')->sum('amount'),
                'penalty' => (float) Compensation::query()->whereIn('type', ['penalty', 'deduction', 'advance'])->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:'.implode(',', Compensation::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'period_month' => ['nullable', 'string', 'max:7'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:pending,approved'],
        ]);

        $compensation = Compensation::query()->create($data + [
            'status' => $data['status'] ?? 'approved',
            'created_by' => $request->user()->id,
        ]);

        Activity::log('created', ucfirst($compensation->type).' recorded for user #'.$compensation->user_id, $compensation, [], 'payroll');

        return back()->with('status', 'Compensation recorded.');
    }

    public function update(Request $request, Compensation $compensation): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', Compensation::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'period_month' => ['nullable', 'string', 'max:7'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:pending,approved'],
        ]);

        $compensation->update($data);

        return back()->with('status', 'Compensation updated.');
    }

    public function destroy(Compensation $compensation): RedirectResponse
    {
        if ($compensation->payroll_id !== null) {
            return back()->withErrors(['compensation' => 'This compensation is already attached to a payroll run.']);
        }

        $compensation->delete();

        return back()->with('status', 'Compensation deleted.');
    }
}
