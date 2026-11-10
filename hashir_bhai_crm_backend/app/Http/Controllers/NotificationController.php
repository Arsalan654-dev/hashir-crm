<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['data' => []], 401);
        }

        $notifications = $user->notifications()->latest()->take(50)->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'unread' => $user->unreadNotifications()->count(),
                'data' => $notifications->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ]),
            ]);
        }

        return view('pages.notifications', [
            'notifications' => $user->notifications()->latest()->paginate(25),
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()?->unreadNotifications->markAsRead();

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'All notifications marked as read.');
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()?->notifications()->whereKey($notification)->first()?->markAsRead();

        return back();
    }
}
