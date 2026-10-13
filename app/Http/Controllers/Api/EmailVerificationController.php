<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Email verification is OPTIONAL: nothing in the app requires a verified
 * email. Verifying only earns the "verified" status (and a banner nudge).
 */
class EmailVerificationController extends Controller
{
    /**
     * POST /api/email/verify/{id}/{hash}?expires=..&signature=..
     *
     * Public on purpose (the link is opened from an inbox, maybe on another
     * device). Protection: the signed, expiring URL (`signed:relative`) plus a
     * hash of the address the link was issued for.
     */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            // Unknown user, or the email changed since the link was issued.
            throw ValidationException::withMessages(['link' => trans('auth.verify_invalid')]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => trans('auth.verify_already'),
                'already_verified' => true,
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            AuditLogger::log($user, 'email_verified', $request);
        }

        return response()->json([
            'message' => trans('auth.verify_success'),
            'already_verified' => false,
        ]);
    }

    /** POST /api/email/verification-notification  (authenticated) */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => trans('auth.verify_already'), 'already_verified' => true]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => trans('auth.verify_sent'), 'already_verified' => false]);
    }
}
