<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * "Where am I signed in?" — list and revoke the user's own API tokens.
 * Every query is scoped to $request->user(), so one user can never see or
 * revoke another user's sessions.
 */
class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentId = $this->currentTokenId($request);

        $sessions = $request->user()->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PersonalAccessToken $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'last_used_at' => $t->last_used_at?->toIso8601String(),
                'created_at' => $t->created_at?->toIso8601String(),
                'expires_at' => $t->expires_at?->toIso8601String(),
                'is_current' => $t->id === $currentId,
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->tokens()->whereKey($id)->firstOrFail()->delete();

        return response()->json(['message' => trans('auth.session_revoked')]);
    }

    /** Sign out everywhere except this device. */
    public function destroyOthers(Request $request): JsonResponse
    {
        $query = $request->user()->tokens();

        if ($currentId = $this->currentTokenId($request)) {
            $query->where('id', '!=', $currentId);
        }

        return response()->json([
            'message' => trans('auth.sessions_revoked'),
            'revoked' => $query->delete(),
        ]);
    }

    private function currentTokenId(Request $request): ?int
    {
        $token = $request->user()->currentAccessToken();

        return $token instanceof PersonalAccessToken ? $token->id : null;
    }
}
