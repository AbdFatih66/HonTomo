<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use Illuminate\Http\Request;

/** Read-only viewer for the append-only auth_audit_logs table. Admin only. */
class AuditLogController extends Controller
{
    private const ACTIONS = [
        'account_registered', 'login_succeeded', 'login_failed', 'password_reset',
        'email_verified', 'google_linked', 'google_unlinked', 'google_login',
        'admin_user_created', 'admin_password_changed', 'admin_role_changed', 'admin_user_deleted',
    ];

    public function index(Request $request)
    {
        $logs = AuthAuditLog::query()
            ->with(['user:id,name,email'])
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('email'), function ($q) use ($request) {
                $email = $request->string('email')->toString();
                $q->whereHas('user', fn ($u) => $u->where('email', 'like', "%{$email}%"));
            })
            ->when($request->filled('from'), fn ($q) => $q->where('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('created_at', '<=', $request->date('to')->endOfDay()))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($logs);
    }

    /** For the filter dropdown — every action that actually gets logged. */
    public function actions()
    {
        return response()->json(['actions' => self::ACTIONS]);
    }
}
