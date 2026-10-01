<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu kali tes JLPT lengkap untuk satu pack (paket soal) dan satu mode
 * (strict/practice). Lihat migrasi create_jlpt_test_tables,
 * add_pack_mode_to_jlpt_test_attempts dan App\Services\JlptTestService.
 *
 * Baris dengan `legacy_mock_id` terisi = hasil migrasi dari Simulasi JLPT lama:
 * hanya skor, tanpa jawaban per soal.
 */
class UserJlptTestAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    protected $table = 'user_jlpt_test_attempts';

    public const MODE_STRICT = 'strict';
    public const MODE_PRACTICE = 'practice';

    protected $fillable = [
        'user_id', 'level', 'pack', 'mode', 'status',
        'legacy_mock_id', 'legacy_duration_seconds',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(UserJlptTestSection::class, 'attempt_id');
    }
}
