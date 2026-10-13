<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserKnownDevice;
use App\Notifications\NewDeviceSignIn;
use Illuminate\Auth\Events\Registered;
use App\Http\Middleware\RejectEvictedDeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Account creation and Sanctum token issuing.
 *
 * Authentication is Sanctum personal-access-token (Bearer). Tokens always get
 * an expiry so a stolen/forgotten device does not stay signed in forever.
 */
class AuthService
{
    /**
     * @param  array{name:string,email:string,password:string}  $data
     */
    public function register(array $data, string $locale): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
            'ui_language' => in_array($locale, ['id', 'en'], true) ? $locale : 'id',
        ]);

        // role is not mass-assignable from input: always the DB default ('user').
        // Reload so DB defaults (role, hearts, ...) are present on the model.
        $user->refresh();

        // Fires Laravel's verification email (User implements MustVerifyEmail).
        // A mail outage must never block sign-up: the user can resend later.
        try {
            event(new Registered($user));
        } catch (Throwable $e) {
            Log::warning('Registration email could not be sent', ['exception' => $e::class]);
        }

        return $user;
    }

    /**
     * Verify credentials without leaking whether the email exists.
     * A dummy hash check is performed for unknown emails so response time
     * does not reveal account existence. Accounts without a password
     * (e.g. Google-only) can never log in with a password.
     */
    public function findUserByCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', Str::lower($email))->first();

        $hash = $user?->password ?: Hash::make(Str::random(32));
        $matches = Hash::check($password, $hash);

        return ($user && $user->password && $matches) ? $user : null;
    }

    /**
     * @return array{token:string,expires_at:\Illuminate\Support\Carbon}
     */
    /** Max number of devices allowed to be signed in to one account at the same time. */
    public const MAX_CONCURRENT_DEVICES = 3;

    public function issueToken(User $user, bool $remember = false, ?string $userAgent = null, ?string $ip = null): array
    {
        $this->enforceDeviceLimit($user);
        $this->notifyIfNewDevice($user, $userAgent, $ip);

        $expiresAt = $remember
            ? now()->addDays((int) config('sanctum.remember_ttl_days', 30))
            : now()->addHours((int) config('sanctum.session_ttl_hours', 24));

        $token = $user->createToken($this->deviceName($userAgent), ['*'], $expiresAt)->plainTextToken;

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    /**
     * Sends a "new device sign-in" email the first time this account is
     * accessed from a given (IP, browser/OS) combination. A mail outage
     * must never block login, and this must never throw.
     */
    private function notifyIfNewDevice(User $user, ?string $userAgent, ?string $ip): void
    {
        $fingerprint = hash('sha256', ($ip ?? '').'|'.($userAgent ?? ''));

        $device = UserKnownDevice::firstOrNew([
            'user_id' => $user->id,
            'fingerprint' => $fingerprint,
        ]);

        $isNew = ! $device->exists;

        $device->device_name = $this->deviceName($userAgent);
        $device->ip_address = $ip;
        $device->last_seen_at = now();
        $device->save();

        if (! $isNew) {
            return;
        }

        // First sign-in ever for this account creates no "surprise": every
        // device is new at that point. Only alert from the second device on.
        if ($user->knownDevices()->count() <= 1) {
            return;
        }

        try {
            $user->notify(new NewDeviceSignIn($device->device_name ?? 'Unknown device', $ip, now()));
        } catch (Throwable $e) {
            Log::warning('New-device sign-in email could not be sent', ['exception' => $e::class]);
        }
    }

    /**
     * A new login always succeeds; instead of blocking it, the least
     * recently used device(s) are signed out to make room, so the account
     * never has more than MAX_CONCURRENT_DEVICES - 1 active sessions before
     * the new one is added (making MAX_CONCURRENT_DEVICES total).
     * Expired tokens don't count against the limit.
     */
    private function enforceDeviceLimit(User $user): void
    {
        $active = $user->tokens()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByRaw('last_used_at IS NOT NULL') // NULL (never used) is oldest
            ->orderBy('last_used_at')
            ->orderBy('created_at')
            ->get();

        $toRemove = $active->count() - (self::MAX_CONCURRENT_DEVICES - 1);

        if ($toRemove > 0) {
            $active->take($toRemove)->each(function ($token) {
                // Remember *why* for a short while, so the evicted device gets
                // an explanatory message the next time it calls the API,
                // instead of a bare 401 (the token row itself is deleted below,
                // same as any other revoke).
                Cache::put(
                    RejectEvictedDeviceToken::cacheKey($token->token),
                    'device_limit',
                    now()->addHours(6),
                );

                $token->delete();
            });
        }
    }

    /**
     * Change the signed-in user's password. Every other active session is
     * revoked (this device's own token is left alone) so a password change
     * actually locks out anyone else who had access.
     *
     * @return int number of other sessions revoked
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword, ?int $currentTokenId = null): int
    {
        // Accounts created via Google-only sign-up have no password yet;
        // that case is handled by the "set password" email flow instead.
        if (! $user->password || ! Hash::check($currentPassword, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'current_password' => trans('auth.current_password_incorrect'),
            ]);
        }

        $user->forceFill([
            'password' => $newPassword, // hashed by the model cast
            'remember_token' => Str::random(60),
        ])->save();

        $query = $user->tokens();
        if ($currentTokenId) {
            $query->where('id', '!=', $currentTokenId);
        }

        return $query->delete();
    }

    /** The user object the SPA receives (never includes secrets). */
    public function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role,
            'ui_language' => $user->ui_language,
            'email_verified' => $user->email_verified_at !== null,
            'has_password' => $user->password !== null,
            'providers' => $user->socialAccounts()->pluck('provider')->values()->all(),
        ];
    }

    /** Human-readable label for the sessions list, e.g. "Chrome · Windows". */
    public function deviceName(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'spa';
        }

        // Order matters: Edge/Opera/Chrome UAs also contain "Safari"/"Chrome",
        // Android UAs contain "Linux", iOS UAs contain "Mac OS X".
        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') => 'Opera',
            str_contains($userAgent, 'Firefox/') || str_contains($userAgent, 'FxiOS') => 'Firefox',
            str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        $os = match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };

        return $os ? "{$browser} · {$os}" : $browser;
    }
}
