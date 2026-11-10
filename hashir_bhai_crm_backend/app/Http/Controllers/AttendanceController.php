<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Activity;
use App\Support\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date('date') ?? today();

        $records = AttendanceRecord::query()
            ->with('user:id,name,avatar,employee_code,department_id')
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        $employees = User::query()
            ->whereNotNull('role_id')
            ->where('is_active', true)
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return view('pages.hr.attendance', [
            'date' => $date,
            'employees' => $employees,
            'records' => $records,
            'isHoliday' => Holiday::query()->whereDate('date', $date)->exists(),
            'summary' => [
                'present' => $records->whereIn('status', ['present', 'remote', 'half-day'])->count(),
                'late' => $records->where('status', 'late')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'leave' => $records->where('status', 'leave')->count(),
                'unmarked' => $employees->whereNotIn('id', $records->keys())->count(),
            ],
        ]);
    }

    public function my(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = AttendanceRecord::query()
            ->where('user_id', $request->user()->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        return view('pages.hr.attendance-my', [
            'records' => $records,
            'month' => $month,
            'year' => $year,
            'today' => AttendanceRecord::query()->where('user_id', $request->user()->id)->whereDate('date', today())->first(),
            'summary' => [
                'present' => $records->whereIn('status', ['present', 'late', 'remote', 'half-day'])->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'leave' => $records->where('status', 'leave')->count(),
                'hours' => (float) $records->sum('worked_hours'),
                'overtime' => (float) $records->sum('overtime_hours'),
            ],
        ]);
    }

    public function report(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $employees = User::query()
            ->whereNotNull('role_id')
            ->where('is_active', true)
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        $records = AttendanceRecord::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');

        return view('pages.hr.attendance-report', [
            'employees' => $employees,
            'records' => $records,
            'month' => $month,
            'year' => $year,
            'periodLabel' => $start->format('F Y'),
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $record = AttendanceRecord::query()->firstOrNew([
            'user_id' => $request->user()->id,
            'date' => today()->toDateString(),
        ]);

        if ($record->check_in !== null) {
            return back()->withErrors(['attendance' => 'You have already clocked in today.']);
        }

        $record->fill([
            'check_in' => now()->format('H:i:s'),
            'status' => now()->format('H:i') > '09:15' ? 'late' : 'present',
            'marked_by' => $request->user()->id,
        ])->save();

        Activity::log('clock_in', 'Clocked in at '.now()->format('H:i'), $record, [], 'attendance');

        return back()->with('status', 'Clocked in at '.now()->format('h:i A').'.');
    }

    /**
     * Handle the recurring attendance prompt shown to sales/field staff.
     */
    public function prompt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:present,leave,later'],
        ]);

        $user = $request->user();

        if ($data['action'] === 'later') {
            $request->session()->put('attendance_prompt_snooze_until', now()->addHours(4)->timestamp);

            return back();
        }

        if ($data['action'] === 'present') {
            $record = AttendanceRecord::query()->firstOrNew([
                'user_id' => $user->id,
                'date' => today()->toDateString(),
            ]);

            if ($record->check_in === null) {
                $record->fill([
                    'check_in' => now()->format('H:i:s'),
                    'status' => now()->format('H:i') > '09:15' ? 'late' : 'present',
                    'marked_by' => $user->id,
                ])->save();
            }

            $user->forceFill(['attendance_prompted_at' => now()])->save();

            Activity::log('clock_in', 'Attendance marked present via prompt', $record, [], 'attendance');

            Notifier::send(
                Notifier::escalateTo($user),
                $user->name.' marked present',
                'Checked in at '.now()->format('h:i A').' on '.now()->format('d M Y'),
                route('attendance.index'),
                'bx bx-check-circle',
            );

            return back()->with('status', 'Attendance marked — welcome, '.$user->name.'!');
        }

        $leave = LeaveRequest::query()->create([
            'user_id' => $user->id,
            'type' => 'casual',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'days' => 1,
            'reason' => 'Marked as leave from the attendance prompt',
            'status' => 'pending',
            'is_paid' => true,
        ]);

        $user->forceFill(['attendance_prompted_at' => now()])->save();

        Activity::log('requested', 'Leave requested via attendance prompt', $leave, [], 'leaves');

        Notifier::send(
            Notifier::escalateTo($user),
            $user->name.' requested leave',
            'Leave request for '.today()->format('d M Y').' is awaiting approval.',
            route('leaves.index'),
            'bx bx-calendar-x',
        );

        return back()->with('status', 'Leave request submitted for today. Your manager will review it.');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $record = AttendanceRecord::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('date', today())
            ->first();

        if ($record === null || $record->check_in === null) {
            return back()->withErrors(['attendance' => 'Please clock in first.']);
        }

        if ($record->check_out !== null) {
            return back()->withErrors(['attendance' => 'You have already clocked out today.']);
        }

        $checkIn = Carbon::parse($record->date->toDateString().' '.$record->check_in);
        $checkOut = now();
        $worked = round($checkIn->diffInMinutes($checkOut) / 60, 2);

        $record->update([
            'check_out' => $checkOut->format('H:i:s'),
            'worked_hours' => $worked,
            'overtime_hours' => max(0, round($worked - 8, 2)),
        ]);

        Activity::log('clock_out', 'Clocked out at '.now()->format('H:i'), $record, [], 'attendance');

        return back()->with('status', 'Clocked out. Worked '.$worked.' hours today.');
    }

    public function mark(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:'.implode(',', AttendanceRecord::STATUSES)],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $record = AttendanceRecord::query()->updateOrCreate(
            ['user_id' => $data['user_id'], 'date' => $data['date']],
            [
                'status' => $data['status'],
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'notes' => $data['notes'] ?? null,
                'marked_by' => $request->user()->id,
            ],
        );

        if ($record->check_in && $record->check_out) {
            $record->update([
                'worked_hours' => round(Carbon::parse($data['check_in'])->diffInMinutes(Carbon::parse($data['check_out'])) / 60, 2),
            ]);
        }

        Activity::log('marked', 'Attendance marked for user #'.$data['user_id'].' on '.$data['date'], $record, [], 'attendance');

        return back()->with('status', 'Attendance saved.');
    }
}
