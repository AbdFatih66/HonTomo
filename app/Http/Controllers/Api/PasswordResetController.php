<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Forgot / reset password using Laravel's password broker
 * (hashed, single-use, expiring tokens in `password_reset_tokens`).
 */
class PasswordResetController extends Controller
{
    /**
     * Always answers with the same 200 response, whether or not the email is
     * registered (or the broker throttled us), so this endpoint cannot be used
     * to discover which emails have accounts.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => trans('auth.reset_link_sent')]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request) {
                $user->forceFill([
                    'password' => $password, // hashed by the model cast
                    'remember_token' => Str::random(60),
                ])->save();

                // Sign the account out everywhere: whoever had access before
                // (possibly the reason for the reset) loses it. Learning data
                // is untouched.
                $user->tokens()->delete();

                AuditLogger::log($user, 'password_reset', $request);

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            // Invalid token, expired token, already-used token and unknown
            // email all get the same message.
            throw ValidationException::withMessages([
                'email' => trans('auth.reset_invalid'),
            ]);
        }

        return response()->json(['message' => trans('auth.reset_success')]);
    }
}
