<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('pages.hr.departments', [
            'departments' => Department::query()
                ->withCount(['users', 'designations'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:departments,code'],
            'color' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department = Department::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        Activity::log('created', 'Department created: '.$department->name, $department, [], 'hr');

        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:departments,code,'.$department->id],
            'color' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $department->update($data);

        Activity::log('updated', 'Department updated: '.$department->name, $department, [], 'hr');

        return back()->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->users()->exists()) {
            return back()->withErrors(['department' => 'Reassign employees before deleting this department.']);
        }

        $department->delete();

        return back()->with('status', 'Department deleted.');
    }
}
