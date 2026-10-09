<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserXp;

class XpService
{
    /**
     * Award XP to a user and record it in the ledger.
     */
    public function award(User $user, int $amount, string $source, ?int $sourceId = null): UserXp
    {
        return UserXp::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source' => $source,
            'source_id' => $sourceId,
        ]);
    }

    public function totalXp(User $user): int
    {
        return (int) $user->xpLedger()->sum('amount');
    }

    /**
     * Simple level curve: every 100 XP = 1 level. Tune as needed.
     */
    public function levelFromXp(int $xp): int
    {
        return intdiv($xp, 100) + 1;
    }
}
