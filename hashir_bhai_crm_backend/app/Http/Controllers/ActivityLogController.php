<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user:id,name,avatar')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', $search)
                        ->orWhere('action', 'like', $search)
                        ->orWhere('module', 'like', $search);
                });
            })
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('pages.activity-logs', [
            'logs' => $logs,
            'modules' => ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
