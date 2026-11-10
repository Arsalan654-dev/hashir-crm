<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(): View
    {
        return view('pages.hr.designations', [
            'designations' => Designation::query()
                ->with('department:id,name')
                ->withCount('users')
                ->orderBy('title')
                ->get(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'level' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $designation = Designation::query()->create($data + ['is_active' => true]);

        Activity::log('created', 'Designation created: '.$designation->title, $designation, [], 'hr');

        return back()->with('status', 'Designation created.');
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'level' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $designation->update($data);

        return back()->with('status', 'Designation updated.');
    }

    public function destroy(Designation $designation): RedirectResponse
    {
        if ($designation->users()->exists()) {
            return back()->withErrors(['designation' => 'Reassign employees before deleting this designation.']);
        }

        $designation->delete();

        return back()->with('status', 'Designation deleted.');
    }
}
