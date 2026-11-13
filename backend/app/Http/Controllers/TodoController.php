<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(): View
    {
        return view('pages.apps-todo', [
            'todos' => Todo::query()->orderBy('id')->get(),
            'counts' => [
                'all' => Todo::query()->count(),
                'inprogress' => Todo::query()->where('status', 'Waiting')->count(),
                'complete' => Todo::query()->where('status', 'Completed')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'team' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        Todo::create($data + [
            'status' => $data['status'] ?? 'Waiting',
            'is_completed' => ($data['status'] ?? '') === 'Completed',
            'comments' => 0,
            'members' => [],
            'user_id' => auth()->id(),
        ]);

        return redirect('/apps-todo')->with('status', 'Task created.');
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $data = $request->validate([
            'is_completed' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $todo->update($data);

        if (array_key_exists('is_completed', $data)) {
            if ((bool) ($data['is_completed'] ?? false)) {
                $todo->update(['status' => 'Completed']);
            }
        }

        return back()->with('status', 'Task updated.');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        $todo->delete();

        return back()->with('status', 'Task deleted.');
    }
}
