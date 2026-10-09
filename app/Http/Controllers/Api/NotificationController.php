<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * `title` is an i18n key and `subtitle` is a small JSON blob
     * ({ key, ...params }) written by NotificationService — decoded here so
     * the frontend can translate both in whatever UI language the user has
     * active right now, not whatever language was active when the
     * achievement happened.
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (UserNotification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'subtitle' => json_decode($n->subtitle ?? '{}', true),
                'icon' => $n->icon,
                'color' => $n->color,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * PATCH /api/notifications/read  { ids: number[] }
     */
    public function markRead(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        UserNotification::where('user_id', $request->user()->id)
            ->whereIn('id', $data['ids'])
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * PATCH /api/notifications/unread  { ids: number[] }
     */
    public function markUnread(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        UserNotification::where('user_id', $request->user()->id)
            ->whereIn('id', $data['ids'])
            ->update(['is_read' => false, 'read_at' => null]);

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, int $id)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
