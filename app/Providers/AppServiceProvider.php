<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passkeys\Passkeys;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // We register our own /api/webauthn/* routes (Api\WebAuthnController)
        // to keep issuing Sanctum tokens instead of Fortify-style sessions, so
        // the package's own auto-registered /passkeys/* and /user/passkeys/*
        // routes are turned off to avoid two competing implementations.
        Passkeys::ignoreRoutes();

        // Coarse per-IP ceiling for guest auth endpoints (login has its own
        // per-email+IP limiter on top of this).
        RateLimiter::for('auth-ip', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // Reset links point at the SPA page, not a server route.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return rtrim(config('app.frontend_url'), '/').'/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        // Verification links open an SPA page which then calls the signed API
        // route. The signature is relative (path + query only), so it does not
        // depend on which host the app is served from.
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $signed = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes((int) config('auth.verification.expire', 60)),
                ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())],
                absolute: false,
            );

            parse_str((string) parse_url($signed, PHP_URL_QUERY), $query);

            return rtrim(config('app.frontend_url'), '/').'/verify-email?'.http_build_query([
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'expires' => $query['expires'],
                'signature' => $query['signature'],
            ]);
        });

        RateLimiter::for('verify-email', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('verification-resend', fn (Request $request) => Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()));

        // Password reset: limit per IP and per target email (email is
        // lowercased so case tricks don't multiply the budget).
        RateLimiter::for('forgot-password', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
        ]);
        RateLimiter::for('reset-password', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
        ]);

        RateLimiter::for('change-password', fn (Request $request) => Limit::perMinute(6)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('oauth', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('webauthn', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // Public registration: slow down account-creation abuse.
        RateLimiter::for('register', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(20)->by($request->ip()),
        ]);
    }
}
