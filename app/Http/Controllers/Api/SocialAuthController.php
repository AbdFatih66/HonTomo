<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SocialAuthException;
use App\Support\AuditLogger;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Google sign-in for a Bearer-token SPA.
 *
 * The browser round-trip (redirect -> Google -> callback) runs in a session
 * (OAuth `state` is validated by Socialite). The callback never puts an API
 * token in the URL: it hands the SPA a short-lived, single-use code which the
 * SPA exchanges for a token over a normal POST.
 */
class SocialAuthController extends Controller
{
    private const CODE_TTL_SECONDS = 60;
    private const LINK_INTENT_TTL_SECONDS = 300;

    public function __construct(
        private readonly SocialAuthService $social,
        private readonly AuthService $auth,
    ) {}

    /** GET /api/auth/google/redirect  (web session) */
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->toFrontend('/login', ['oauth_error' => 'unavailable']);
        }

        $linkUserId = null;

        if ($intent = $request->query('intent')) {
            // Linking from the profile page: the intent proves who is asking.
            $linkUserId = Cache::pull($this->intentKey((string) $intent));

            if (! $linkUserId) {
                return $this->toFrontend('/profile', ['oauth_error' => 'failed']);
            }
        }

        $request->session()->put('oauth_link_user_id', $linkUserId);
        $request->session()->put('oauth_redirect', $this->safePath($request->query('redirect')));
        $request->session()->put('oauth_lang', in_array($request->query('lang'), ['id', 'en'], true) ? $request->query('lang') : null);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /** GET /api/auth/google/callback  (web session) */
    public function callback(Request $request): RedirectResponse
    {
        $session = $request->session();
        $linkUserId = $session->pull('oauth_link_user_id');
        $redirect = $session->pull('oauth_redirect');
        $lang = $session->pull('oauth_lang');

        $failurePath = $linkUserId ? '/profile' : '/login';

        // User pressed "Cancel" / denied consent on Google.
        if ($request->filled('error')) {
            return $this->toFrontend($failurePath, ['oauth_error' => 'cancelled']);
        }

        try {
            // Validates the OAuth state stored in the session and exchanges the code.
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Class name only: never log the exception message, request URL or tokens.
            Log::warning('Google OAuth callback rejected', ['exception' => $e::class]);

            return $this->toFrontend($failurePath, ['oauth_error' => 'failed']);
        }

        try {
            if ($linkUserId) {
                $user = \App\Models\User::find($linkUserId);

                if (! $user) {
                    throw new SocialAuthException('failed');
                }

                $this->social->linkToUser($user, $googleUser);
                AuditLogger::log($user, 'google_linked', $request);

                return $this->toFrontend('/profile', ['linked' => 'google']);
            }

            $user = $this->social->resolveUserForLogin($googleUser, $lang);
            AuditLogger::log($user, 'google_login', $request);
        } catch (SocialAuthException $e) {
            return $this->toFrontend($failurePath, ['oauth_error' => $e->reason]);
        } catch (Throwable $e) {
            // e.g. a unique-constraint race; nothing half-created (transaction).
            Log::warning('Google OAuth account resolution failed', ['exception' => $e::class]);

            return $this->toFrontend($failurePath, ['oauth_error' => 'failed']);
        }

        $code = Str::random(64);
        Cache::put($this->codeKey($code), $user->id, self::CODE_TTL_SECONDS);

        return $this->toFrontend('/auth/callback', array_filter([
            'code' => $code,
            'redirect' => $redirect,
        ]));
    }

    /** POST /api/auth/google/exchange  (guest, throttled) */
    public function exchange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:64'],
        ]);

        // pull() = read and delete in one step, so a code works exactly once.
        $userId = Cache::pull($this->codeKey($data['code']));
        $user = $userId ? \App\Models\User::find($userId) : null;

        if (! $user) {
            throw ValidationException::withMessages(['code' => trans('auth.oauth_code_invalid')]);
        }

        $session = $this->auth->issueToken($user, false, $request->userAgent(), $request->ip());

        return response()->json([
            'user' => $this->auth->userPayload($user),
            'token' => $session['token'],
            'expires_at' => $session['expires_at']->toIso8601String(),
        ]);
    }

    /** POST /api/auth/google/link-intent  (authenticated) */
    public function linkIntent(Request $request): JsonResponse
    {
        $intent = Str::random(64);
        Cache::put($this->intentKey($intent), $request->user()->id, self::LINK_INTENT_TTL_SECONDS);

        return response()->json([
            'url' => url('/api/auth/google/redirect').'?'.http_build_query(['intent' => $intent]),
        ]);
    }

    /** DELETE /api/auth/google  (authenticated) */
    public function unlink(Request $request): JsonResponse
    {
        $user = $request->user();

        // Without a password, removing Google would lock the user out.
        if ($user->password === null) {
            throw ValidationException::withMessages(['provider' => trans('auth.unlink_requires_password')]);
        }

        $user->socialAccounts()->where('provider', SocialAuthService::PROVIDER)->delete();
        AuditLogger::log($user, 'google_unlinked', $request);

        return response()->json([
            'message' => trans('auth.unlinked'),
            'user' => $this->auth->userPayload($user->refresh()),
        ]);
    }

    private function toFrontend(string $path, array $query = []): RedirectResponse
    {
        $url = rtrim(config('app.frontend_url'), '/').$path;

        return redirect()->away($query ? $url.'?'.http_build_query($query) : $url);
    }

    /** Same-origin in-app paths only (no open redirect). */
    private function safePath(mixed $path): ?string
    {
        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return null;
        }

        return strlen($path) <= 500 ? $path : null;
    }

    private function codeKey(string $code): string
    {
        return 'oauth_code:'.hash('sha256', $code);
    }

    private function intentKey(string $intent): string
    {
        return 'oauth_link_intent:'.hash('sha256', $intent);
    }
}
