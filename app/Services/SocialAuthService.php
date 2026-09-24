<?php

namespace App\Services;

use App\Exceptions\SocialAuthException;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Account-linking policy for "Continue with Google".
 *
 *  C. Google id already linked          -> log in to that account.
 *  A. Email unknown                      -> create a new account (only when
 *                                           Google says the email is verified).
 *  B. Email belongs to an existing user  -> NEVER merge automatically. Someone
 *     could have registered that address with a password they know; merging
 *     would hand them the victim's Google login. The user must sign in with
 *     their password and link Google from their profile.
 */
class SocialAuthService
{
    public const PROVIDER = 'google';

    public function resolveUserForLogin(SocialiteUser $g, ?string $locale = null): User
    {
        $providerId = trim((string) $g->getId());

        if ($providerId === '') {
            throw new SocialAuthException('failed');
        }

        $linked = SocialAccount::where('provider', self::PROVIDER)
            ->where('provider_id', $providerId)
            ->first();

        if ($linked) {
            return $linked->user;
        }

        $email = Str::lower(trim((string) $g->getEmail()));

        if ($email === '') {
            throw new SocialAuthException('failed');
        }

        if (! $this->emailIsVerified($g)) {
            throw new SocialAuthException('email_unverified');
        }

        if (User::where('email', $email)->exists()) {
            throw new SocialAuthException('account_exists');
        }

        $user = DB::transaction(function () use ($g, $providerId, $email, $locale) {
            $avatar = $this->safeAvatar($g->getAvatar());

            $user = User::create([
                'name' => Str::limit(trim((string) $g->getName()) ?: Str::before($email, '@'), 100, ''),
                'email' => $email,
                'password' => null,
                'avatar' => $avatar,
                'ui_language' => in_array($locale, ['id', 'en'], true) ? $locale : 'id',
            ]);

            // Google verified this address; the model does not mass-assign it.
            $user->forceFill(['email_verified_at' => now()])->save();

            $user->socialAccounts()->create([
                'provider' => self::PROVIDER,
                'provider_id' => $providerId,
                'provider_email' => $email,
                'avatar' => $avatar,
            ]);

            return $user->refresh();
        });

        event(new Registered($user));

        return $user;
    }

    /** Attach Google to the currently signed-in user (from their profile). */
    public function linkToUser(User $user, SocialiteUser $g): SocialAccount
    {
        $providerId = trim((string) $g->getId());

        if ($providerId === '') {
            throw new SocialAuthException('failed');
        }

        $existing = SocialAccount::where('provider', self::PROVIDER)
            ->where('provider_id', $providerId)
            ->first();

        if ($existing) {
            if ($existing->user_id === $user->id) {
                return $existing; // already linked to this very account
            }

            throw new SocialAuthException('already_linked');
        }

        if ($user->socialAccounts()->where('provider', self::PROVIDER)->exists()) {
            throw new SocialAuthException('provider_exists');
        }

        $avatar = $this->safeAvatar($g->getAvatar());

        $account = $user->socialAccounts()->create([
            'provider' => self::PROVIDER,
            'provider_id' => $providerId,
            'provider_email' => $g->getEmail() ? Str::lower($g->getEmail()) : null,
            'avatar' => $avatar,
        ]);

        // Never overwrite what the user already has; only fill an empty slot.
        if (! $user->avatar && $avatar) {
            $user->forceFill(['avatar' => $avatar])->save();
        }

        return $account;
    }

    public function emailIsVerified(SocialiteUser $g): bool
    {
        $raw = is_array($g->user ?? null) ? $g->user : [];

        return filter_var($raw['email_verified'] ?? $raw['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function safeAvatar(?string $url): ?string
    {
        return ($url && str_starts_with($url, 'https://') && strlen($url) <= 2048) ? $url : null;
    }
}
