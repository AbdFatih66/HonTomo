<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memindahkan riwayat Simulasi JLPT (user_jlpt_mock_attempts) ke tabel Tes JLPT:
 * satu baris mock → satu attempt (status completed) + tiga section.
 *
 * Yang TIDAK ada di data lama, sehingga dibiarkan kosong:
 *  - jawaban per soal (`answers` = null)  → attempt lama tidak bisa direview;
 *  - waktu per sesi (`time_spent_seconds` = null); total durasi disimpan di
 *    kolom attempt `legacy_duration_seconds`;
 *  - jumlah soal kosong per もんだい (`unanswered` = 0).
 *
 * Idempoten (dijaga `legacy_mock_id`) dan TIDAK menyentuh XP/notifikasi.
 * Tabel user_jlpt_mock_attempts sengaja tidak dihapus di sini.
 */
return new class extends Migration
{
    // Jumlah soal resmi N5 — dikunci di sini (bukan dari config) agar migrasi
    // ini tetap benar walau config berubah di kemudian hari.
    private const SECTIONS = [
        'mojigoi' => ['prefix' => 'v', 'column' => 'vocab_correct', 'total' => 33],
        'bunpou_dokkai' => ['prefix' => 'g', 'column' => 'grammar_correct', 'total' => 32],
        'chokai' => ['prefix' => 'l', 'column' => 'listening_correct', 'total' => 24],
    ];

    public function up(): void
    {
        DB::table('user_jlpt_mock_attempts')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $m) {
                if (DB::table('user_jlpt_test_attempts')->where('legacy_mock_id', $m->id)->exists()) {
                    continue;
                }

                DB::transaction(function () use ($m) {
                    $at = $m->completed_at ?? $m->created_at ?? now();

                    $attemptId = DB::table('user_jlpt_test_attempts')->insertGetId([
                        'user_id' => $m->user_id,
                        'level' => $m->level ?: 'N5',
                        'pack' => $m->test_key,
                        'mode' => $m->strict ? 'strict' : 'practice',
                        'status' => 'completed',
                        'legacy_mock_id' => $m->id,
                        'legacy_duration_seconds' => $m->duration_seconds,
                        'created_at' => $at,
                        'updated_at' => $at,
                    ]);

                    $detail = collect(json_decode($m->detail ?? '[]', true) ?: []);

                    foreach (self::SECTIONS as $section => $def) {
                        $breakdown = $detail
                            ->filter(fn ($d) => str_starts_with((string) ($d['id'] ?? ''), $def['prefix']))
                            ->map(fn ($d) => [
                                'mondai' => (int) substr((string) $d['id'], 1),
                                'correct' => (int) ($d['correct'] ?? 0),
                                'total' => (int) ($d['total'] ?? 0),
                                'unanswered' => 0,
                            ])
                            ->sortBy('mondai')
                            ->values()
                            ->all();

                        DB::table('user_jlpt_test_sections')->insert([
                            'attempt_id' => $attemptId,
                            'section' => $section,
                            'started_at' => $at,
                            'submitted_at' => $at,
                            'answers' => null,
                            'score' => (int) $m->{$def['column']},
                            'total' => $def['total'],
                            'breakdown' => json_encode($breakdown),
                            'time_spent_seconds' => null,
                            'timed_out' => false,
                            'created_at' => $at,
                            'updated_at' => $at,
                        ]);
                    }
                });
            }
        });
    }

    public function down(): void
    {
        $ids = DB::table('user_jlpt_test_attempts')->whereNotNull('legacy_mock_id')->pluck('id');

        // user_jlpt_test_sections ikut terhapus lewat cascadeOnDelete
        DB::table('user_jlpt_test_attempts')->whereIn('id', $ids)->delete();
    }
};
