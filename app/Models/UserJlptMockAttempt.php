<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated Simulasi JLPT sudah digabung ke Tes JLPT. Tabel ini tidak lagi
 * ditulis; isinya sudah dipindahkan ke user_jlpt_test_attempts oleh migration
 * migrate_jlpt_mock_attempts_into_test_tables. Dipertahankan satu rilis untuk
 * verifikasi/rollback, lalu tabel dan model ini dihapus.
 *
 * One finished attempt of a JLPT mock test. See the migration docblock:
 * the question bank lives in public/data/jlpt-mock/*.json, not in the database.
 */
class UserJlptMockAttempt extends Model
{
    protected $table = 'user_jlpt_mock_attempts';

    protected $fillable = [
        'user_id', 'test_key', 'level',
        'vocab_correct', 'grammar_correct', 'listening_correct',
        'score_language', 'score_listening', 'score_total', 'passed',
        'strict', 'duration_seconds', 'detail', 'completed_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'strict' => 'boolean',
        'detail' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
