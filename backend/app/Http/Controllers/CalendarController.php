<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function events(): JsonResponse
    {
        $events = CalendarEvent::query()->get()->map(fn (CalendarEvent $event) => [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_date->toDateString(),
            'end' => $event->end_date?->toDateString(),
            'className' => $event->class_name,
            'allDay' => $event->all_day,
        ]);

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:50'],
            'all_day' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $event = CalendarEvent::create([
            'title' => $data['title'],
            'start_date' => $data['start'],
            'end_date' => $data['end'] ?? null,
            'class_name' => $data['category'] ?? 'bg-primary',
            'all_day' => (bool) ($data['all_day'] ?? true),
            'description' => $data['description'] ?? null,
            'user_id' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json($event);
        }

        return back()->with('status', 'Event created.');
    }

    public function update(Request $request, CalendarEvent $event): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'start' => ['sometimes', 'date'],
            'end' => ['nullable', 'date'],
            'category' => ['sometimes', 'string', 'max:50'],
            'all_day' => ['nullable', 'boolean'],
        ]);

        $event->update([
            'title' => $data['title'] ?? $event->title,
            'start_date' => $data['start'] ?? $event->start_date,
            'end_date' => $data['end'] ?? null,
            'class_name' => $data['category'] ?? $event->class_name,
            'all_day' => array_key_exists('all_day', $data) ? (bool) $data['all_day'] : $event->all_day,
        ]);

        if ($request->expectsJson()) {
            return response()->json($event);
        }

        return back()->with('status', 'Event updated.');
    }

    public function destroy(CalendarEvent $event): JsonResponse|RedirectResponse
    {
        $event->delete();

        if (request()->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('status', 'Event deleted.');
    }
}
