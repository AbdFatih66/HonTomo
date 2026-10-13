<?php

namespace App\Support;

use App\Models\AuthAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Records sensitive account actions (password changes, Google link/unlink,
 * email verification, ...) to a simple append-only table for later
 * investigation. Logging must never break the action it's attached to.
 *
 * IMPORTANT: `meta` is stored as plain JSON — never pass secrets (passwords,
 * tokens, full session data) into it, only small non-sensitive context like
 * a provider name or an old/new email.
 */
class AuditLogger
{
    public static function log(?User $user, string $action, ?Request $request = null, array $meta = []): void
    {
        try {
            AuthAuditLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Audit log write failed', ['action' => $action, 'exception' => $e::class]);
        }
    }
}
