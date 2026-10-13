<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Actions\DeletePasskey;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\GenerateVerificationOptions;
use Laravel\Passkeys\Actions\StorePasskey;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Support\WebAuthn;
use Throwable;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Biometric / passkey (WebAuthn) login, on top of laravel/passkeys.
 *
 * IMPORTANT: the package's `PublicKeyCredentialCreationOptions` /
 * `PublicKeyCredentialRequestOptions` objects carry the WebAuthn challenge
 * as raw binary bytes. Never pass them to response()->json() or
 * session()->put() directly:
 *   - response()->json($options) runs json_encode() on the object, which
 *     throws "Malformed UTF-8 characters" because the binary challenge is
 *     not valid UTF-8.
 *   - session()->put('key', $options) tries to (un)serialize the object
 *     across requests, which the webauthn-lib classes aren't built for.
 * Instead we always go through Laravel\Passkeys\Support\WebAuthn:
 *   - WebAuthn::toBrowserArray($options) -> base64url-safe array for the
 *     JSON response the browser's PublicKeyCredential.parse*FromJSON() reads.
 *   - WebAuthn::toJson()/fromJson() -> serialize the options to a plain
 *     string to stash in the session, and rebuild the object on the next
 *     request.
 *
 * The frontend (resources/js/utils/webauthn.js) posts
 * `{ ...credential.toJSON(), alias }` as a *flat* body (no nested
 * `credential` key, and `alias` instead of `name`), so we can't drop in the
 * package's own PasskeyRegistrationRequest/PasskeyVerificationRequest
 * (which expect that different shape) without also changing the frontend.
 * credentialFromRequest() below does the same JSON deserialization those
 * classes do, just against our existing flat body.
 *
 * These endpoints run under Laravel's 'web' middleware group (see
 * routes/api.php) because the challenge has to be held in the session
 * between the "options" call and the "verify" call. CSRF is deliberately
 * exempted for these routes in bootstrap/app.php: the WebAuthn signature
 * itself proves intent, which is what CSRF protection exists to substitute
 * for.
 */
class WebAuthnController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    // ---------------------------------------------------- registering a passkey

    /** POST /api/webauthn/register/options (authenticated) */
    public function registerOptions(Request $request, GenerateRegistrationOptions $generateOptions)
    {
        $options = $generateOptions($request->user());

        $request->session()->put('passkeys.register.options', WebAuthn::toJson($options));

        return response()->json(WebAuthn::toBrowserArray($options));
    }

    /** POST /api/webauthn/register (authenticated) */
    public function register(Request $request, StorePasskey $storePasskey)
    {
        $serialized = $request->session()->pull('passkeys.register.options');

        if (! $serialized) {
            throw ValidationException::withMessages([
                'credential' => trans('auth.webauthn_failed'),
            ]);
        }

        $options = WebAuthn::fromJson($serialized, PublicKeyCredentialCreationOptions::class);

        $credential = $this->credentialFromRequest($request);

        $storePasskey(
            $request->user(),
            (string) $request->input('alias', 'Passkey'),
            $credential,
            $options,
        );

        AuditLogger::log($request->user(), 'webauthn_registered', $request);

        return response()->json(['message' => trans('auth.webauthn_registered')], 201);
    }

    /** GET /api/webauthn/credentials (authenticated) — list registered passkeys */
    public function credentials(Request $request)
    {
        return response()->json(
            $request->user()->passkeys()
                ->orderByDesc('created_at')
                ->get(['id', 'name as alias', 'created_at'])
        );
    }

    /** DELETE /api/webauthn/credentials/{id} (authenticated) */
    public function destroyCredential(Request $request, string $id, DeletePasskey $deletePasskey)
    {
        $passkey = $request->user()->passkeys()->whereKey($id)->first();

        if (! $passkey) {
            abort(404);
        }

        $deletePasskey($passkey);

        AuditLogger::log($request->user(), 'webauthn_removed', $request);

        return response()->json(['message' => trans('auth.webauthn_removed')]);
    }

    // -------------------------------------------------------------- logging in

    /** POST /api/webauthn/login/options (guest) — "usernameless"/discoverable login */
    public function loginOptions(Request $request, GenerateVerificationOptions $generateOptions)
    {
        $options = $generateOptions();

        $request->session()->put('passkeys.login.options', WebAuthn::toJson($options));

        return response()->json(WebAuthn::toBrowserArray($options));
    }

    /** POST /api/webauthn/login (guest) */
    public function login(Request $request, VerifyPasskey $verifyPasskey)
    {
        $serialized = $request->session()->pull('passkeys.login.options');

        if (! $serialized) {
            throw ValidationException::withMessages(['credential' => trans('auth.webauthn_failed')]);
        }

        $options = WebAuthn::fromJson($serialized, PublicKeyCredentialRequestOptions::class);

        $credential = $this->credentialFromRequest($request);

        // The action verifies the assertion's signature against the stored
        // public key and returns the owning Passkey; if we reach the line
        // after this, the credential is authentic.
        $passkey = $verifyPasskey($credential, $options);

        $user = $passkey?->user;

        if (! $user) {
            throw ValidationException::withMessages(['credential' => trans('auth.webauthn_failed')]);
        }

        // We never want a persisted session — a Sanctum token is issued below
        // instead — so make sure nothing got logged in behind our back.
        Auth::logout();

        $session = $this->auth->issueToken($user, false, $request->userAgent(), $request->ip());
        AuditLogger::log($user, 'webauthn_login', $request);

        return response()->json([
            'user' => $this->auth->userPayload($user),
            'token' => $session['token'],
            'expires_at' => $session['expires_at']->toIso8601String(),
        ]);
    }

    /**
     * Decode the posted WebAuthn credential JSON (browser's
     * `credential.toJSON()` output, posted flat, e.g. mixed in with an
     * `alias` field) into the object the package's actions expect.
     */
    private function credentialFromRequest(Request $request): PublicKeyCredential
    {
        try {
            return WebAuthn::fromJson(
                json_encode($request->except(['alias', 'remember'])) ?: '{}',
                PublicKeyCredential::class
            );
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'credential' => trans('auth.webauthn_failed'),
            ]);
        }
    }
}
