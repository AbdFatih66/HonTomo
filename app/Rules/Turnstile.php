<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a Cloudflare Turnstile token server-side.
 * https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 *
 * If TURNSTILE_SECRET_KEY is not configured, this rule passes everything —
 * so CAPTCHA is opt-in and local/dev environments are never blocked.
 */
class Turnstile implements ValidationRule
{
    public function __construct(private readonly ?string $remoteIp = null) {}

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $secret = config('services.turnstile.secret_key');

        if (! $secret) {
            return; // CAPTCHA disabled — nothing configured
        }

        if (! is_string($value) || $value === '') {
            $fail(trans('auth.captcha_failed'));

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => $this->remoteIp,
                ]);

            if (! $response->json('success', false)) {
                $fail(trans('auth.captcha_failed'));
            }
        } catch (\Throwable $e) {
            // Turnstile's API being down must never lock everyone out of
            // registration; log it and let the request through.
            Log::warning('Turnstile verification request failed', ['exception' => $e::class]);
        }
    }
}
