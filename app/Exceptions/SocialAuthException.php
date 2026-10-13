<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A social-login problem that maps to a user-facing reason code
 * (translated by the SPA): cancelled, failed, unavailable,
 * email_unverified, account_exists, already_linked, provider_exists.
 */
class SocialAuthException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct("Social auth failed: {$reason}");
    }
}
