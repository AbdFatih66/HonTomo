<?php

namespace App\Models;

// Note: HasApiTokens is used for Sanctum. If you don't have laravel/sanctum
// installed, remove the trait and its import.
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use PasskeyAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'ui_language',
        'current_level_id',
        'hearts',
        'hearts_refill_at',
        'daily_goal_date',
        'daily_goal_target',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hearts_refill_at' => 'datetime',
            'daily_goal_date' => 'date',
        ];
    }

    // --- Learning profile relations (merged from HasLearningProfile) ---

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function userLessons(): HasMany
    {
        return $this->hasMany(UserLesson::class);
    }

    public function userVocabularies(): HasMany
    {
        return $this->hasMany(UserVocabulary::class);
    }

    public function xpLedger(): HasMany
    {
        return $this->hasMany(UserXp::class, 'user_id');
    }

    public function streak(): HasOne
    {
        return $this->hasOne(UserStreak::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function totalXp(): int
    {
        return (int) $this->xpLedger()->sum('amount');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function knownDevices(): HasMany
    {
        return $this->hasMany(UserKnownDevice::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
