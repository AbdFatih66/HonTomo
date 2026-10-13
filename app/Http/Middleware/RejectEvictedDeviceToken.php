<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs before auth:sanctum. If this bearer token was just evicted by the
 * concurrent-device limit (see AuthService::enforceDeviceLimit), the account
 * still exists and the token row is gone either way — but here we can tell
 * the device *why* it was signed out, instead of a plain 401.
 */
class RejectEvictedDeviceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if ($plainToken && str_contains($plainToken, '|')) {
            [, $plainToken] = explode('|', $plainToken, 2); // Sanctum's "id|token" format
        }

        if ($plainToken) {
            $hash = hash('sha256', $plainToken);
            $reason = Cache::get(self::cacheKey($hash));

            if ($reason) {
                return response()->json([
                    'message' => trans("auth.device_evicted.{$reason}"),
                    'reason' => $reason,
                ], 401);
            }
        }

        return $next($request);
    }

    public static function cacheKey(string $tokenHash): string
    {
        return 'evicted_token:'.$tokenHash;
    }
}
