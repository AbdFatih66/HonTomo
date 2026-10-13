<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menggabungkan "Simulasi JLPT" (jlpt-mock) ke Tes JLPT.
 *
 *  - pack : paket soal yang dikerjakan (config/jlpt.php → packs). Attempt lama
 *           memakai bank soal asli, jadi default-nya 'private'.
 *  - mode : 'strict' (kondisi ujian: timer server, audio sekali putar, XP) atau
 *           'practice' (tanpa timer, umpan balik per soal, audio bisa diulang).
 *  - legacy_mock_id / legacy_duration_seconds : jejak baris hasil migrasi dari
 *           user_jlpt_mock_attempts (lihat migration berikutnya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_jlpt_test_attempts', function (Blueprint $table) {
            $table->string('pack', 40)->default('private')->after('level');
            $table->string('mode', 10)->default('strict')->after('pack');
            $table->unsignedBigInteger('legacy_mock_id')->nullable()->unique()->after('status');
            $table->unsignedInteger('legacy_duration_seconds')->nullable()->after('legacy_mock_id');

            $table->index(['user_id', 'pack', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('user_jlpt_test_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'pack', 'status']);
            $table->dropUnique(['legacy_mock_id']);
            $table->dropColumn(['pack', 'mode', 'legacy_mock_id', 'legacy_duration_seconds']);
        });
    }
};
