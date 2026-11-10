<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Activity;
use App\Support\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $leaves = LeaveRequest::query()
            ->with(['user:id,name,avatar,department_id', 'approver:id,name'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('pages.hr.leaves', [
            'leaves' => $leaves,
            'counts' => [
                'pending' => LeaveRequest::query()->where('status', 'pending')->count(),
                'approved' => LeaveRequest::query()->where('status', 'approved')->count(),
                'rejected' => LeaveRequest::query()->where('status', 'rejected')->count(),
            ],
            'employees' => User::query()->whereNotNull('role_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function my(Request $request): View
    {
        return view('pages.hr.leaves-my', [
            'leaves' => LeaveRequest::query()
                ->where('user_id', $request->user()->id)
                ->orderByDesc('start_date')
                ->paginate(15),
            'balances' => [
                'annual' => 21 - (float) LeaveRequest::query()->where('user_id', $request->user()->id)->where('type', 'annual')->where('status', 'approved')->sum('days'),
                'sick' => 10 - (float) LeaveRequest::query()->where('user_id', $request->user()->id)->where('type', 'sick')->where('status', 'approved')->sum('days'),
                'used' => (float) LeaveRequest::query()->where('user_id', $request->user()->id)->where('status', 'approved')->sum('days'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', LeaveRequest::TYPES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $userId = $request->user()->can('leaves.approve') && filled($data['user_id'] ?? null)
            ? (int) $data['user_id']
            : $request->user()->id;

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

        $leave = LeaveRequest::query()->create([
            'user_id' => $userId,
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'is_paid' => $data['type'] !== 'unpaid',
        ]);

        Activity::log('requested', 'Leave requested by user #'.$userId, $leave, [], 'leaves');

        $applicant = User::query()->find($userId);

        if ($applicant !== null) {
            Notifier::send(
                Notifier::escalateTo($applicant),
                $applicant->name.' requested leave',
                ucfirst($leave->type).' leave from '.$leave->start_date->format('d M').' to '.$leave->end_date->format('d M'),
                route('leaves.index'),
                'bx bx-calendar-x',
            );
        }

        return back()->with('status', 'Leave request submitted.');
    }

    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        $this->markAttendance($leave);

        Activity::log('approved', 'Leave approved for user #'.$leave->user_id, $leave, [], 'leaves');

        return back()->with('status', 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        Activity::log('rejected', 'Leave rejected for user #'.$leave->user_id, $leave, [], 'leaves');

        return back()->with('status', 'Leave request rejected.');
    }

    public function destroy(Request $request, LeaveRequest $leave): RedirectResponse
    {
        if ($leave->user_id !== $request->user()->id && ! $request->user()->can('leaves.approve')) {
            abort(403, '403 — You cannot access this.');
        }

        if ($leave->status === 'approved' && ! $request->user()->can('leaves.approve')) {
            return back()->withErrors(['leave' => 'Approved leave cannot be cancelled by the employee.']);
        }

        $leave->delete();

        return back()->with('status', 'Leave request removed.');
    }

    private function markAttendance(LeaveRequest $leave): void
    {
        for ($date = $leave->start_date->copy(); $date->lte($leave->end_date); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            AttendanceRecord::query()->updateOrCreate(
                ['user_id' => $leave->user_id, 'date' => $date->toDateString()],
                ['status' => 'leave', 'notes' => ucfirst($leave->type).' leave', 'marked_by' => $leave->approved_by],
            );
        }
    }
}
