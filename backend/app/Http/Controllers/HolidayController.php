<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);

        return view('pages.hr.holidays', [
            'holidays' => Holiday::query()
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get(),
            'year' => $year,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date', 'unique:holidays,date'],
            'is_paid' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Holiday::query()->create($data + ['is_paid' => $request->boolean('is_paid', true)]);

        Activity::log('created', 'Holiday added: '.$data['name'], null, $data, 'hr');

        return back()->with('status', 'Holiday added.');
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date', 'unique:holidays,date,'.$holiday->id],
            'is_paid' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $holiday->update($data + ['is_paid' => $request->boolean('is_paid', true)]);

        return back()->with('status', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('status', 'Holiday removed.');
    }
}
