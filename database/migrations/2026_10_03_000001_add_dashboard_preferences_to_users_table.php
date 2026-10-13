<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preferensi dasbor yang sebelumnya hanya tersimpan di browser:
 *  - jlpt_exam_date : tanggal ujian JLPT pilihan user (null = pakai jadwal resmi)
 *  - seen_badges    : kunci lencana yang sudah pernah dilihat user. null = belum
 *                     pernah membuka dasbor versi lencana (kunjungan pertama
 *                     tidak memunculkan notifikasi lencana).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('jlpt_exam_date')->nullable()->after('daily_goal_target');
            $table->json('seen_badges')->nullable()->after('jlpt_exam_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jlpt_exam_date', 'seen_badges']);
        });
    }
};
