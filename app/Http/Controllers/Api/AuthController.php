<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->accountData(), app()->getLocale());
        AuditLogger::log($user, 'account_registered', $request);
        $session = $this->auth->issueToken($user, false, $request->userAgent(), $request->ip());

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $session['token'],
            'expires_at' => $session['expires_at']->toIso8601String(),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $user = $this->auth->findUserByCredentials(
            $request->input('email'),
            $request->input('password'),
        );

        if (! $user) {
            $request->hitRateLimiter();
            AuditLogger::log(null, 'login_failed', $request, ['email' => Str::lower((string) $request->input('email'))]);

            // Same message for "unknown email" and "wrong password".
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->clearRateLimiter();
        AuditLogger::log($user, 'login_succeeded', $request);

        $session = $this->auth->issueToken($user, $request->boolean('remember'), $request->userAgent(), $request->ip());

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $session['token'],
            'expires_at' => $session['expires_at']->toIso8601String(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => trans('auth.logged_out')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken() instanceof \Laravel\Sanctum\PersonalAccessToken
            ? $user->currentAccessToken()->id
            : null;

        $revoked = $this->auth->changePassword(
            $user,
            $request->input('current_password'),
            $request->input('password'),
            $currentTokenId,
        );

        AuditLogger::log($user, 'password_changed', $request);

        return response()->json([
            'message' => trans('auth.password_changed'),
            'revoked' => $revoked,
        ]);
    }

    private function userPayload(User $user): array
    {
        return $this->auth->userPayload($user);
    }
}
