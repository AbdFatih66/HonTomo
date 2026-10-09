<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tes JLPT (simulasi ujian). Satu "attempt" = satu kali tes lengkap satu
 * level (3 sesi: moji-goi, bunpou-dokkai, chokai). Tiap sesi punya jam mulai
 * sendiri; batas waktunya dihitung dari started_at di server, jadi refresh /
 * tutup tab tidak mengulang timer.
 *
 * Soal ada di frontend, jawaban user disimpan sebagai JSON {"<id soal>": 1..4}.
 * Kunci jawaban ada di config/jlpt.php (tidak pernah dikirim ke client).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_jlpt_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level', 4); // 'N5'
            $table->string('status', 20)->default('in_progress'); // in_progress | completed | abandoned
            $table->timestamps();

            $table->index(['user_id', 'level']);
        });

        Schema::create('user_jlpt_test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('user_jlpt_test_attempts')->cascadeOnDelete();
            $table->string('section', 20); // mojigoi | bunpou_dokkai | chokai

            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->json('answers')->nullable();     // jawaban tersimpan (autosave / final)
            $table->unsignedSmallInteger('score')->nullable();  // jumlah benar (diisi saat submit)
            $table->unsignedSmallInteger('total')->nullable();  // jumlah soal
            $table->json('breakdown')->nullable();   // benar/total per もんだい
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->boolean('timed_out')->default(false);

            $table->timestamps();

            $table->unique(['attempt_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_jlpt_test_sections');
        Schema::dropIfExists('user_jlpt_test_attempts');
    }
};
