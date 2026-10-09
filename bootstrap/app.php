<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [\App\Http\Middleware\SetUiLocale::class]);
        // The lesson/learning-path endpoints live in the api group, so the UI
        // locale must be resolved there too (otherwise prompts fall back to en).
        $middleware->api(append: [\App\Http\Middleware\SetUiLocale::class]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // Must run before auth:sanctum resolves the (now-deleted) token, so an
        // evicted device gets an explanatory message instead of a bare 401.
        $middleware->prependToGroup('api', \App\Http\Middleware\RejectEvictedDeviceToken::class);

        // WebAuthn/passkey endpoints run under the 'web' group (they need a
        // session to hold the challenge between the "options" and "verify"
        // calls), but the SPA calls them as a plain fetch with no CSRF token.
        // This is safe to exempt: the WebAuthn signature itself is the proof
        // of intent — an attacker cannot forge a valid assertion/attestation
        // without the physical authenticator, which is exactly what CSRF
        // protection exists to substitute for.
        $middleware->validateCsrfTokens(except: [
            'api/webauthn/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
