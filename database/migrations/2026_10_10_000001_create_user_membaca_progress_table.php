<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Progres per-user untuk fitur Membaca (読解). Bentuknya sama dengan
 * user_kaite_oboeru_progress: bacaan ada di frontend
 * (resources/js/data/practice/membaca.js), bukan baris tabel `lessons`,
 * jadi `set_key` — kunci milik frontend, mis. 'n5l1', 'n4l1', 'n3l1' — satu-satunya
 * pengenal yang dipakai kedua sisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_membaca_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('set_key', 20); // 'n5l1' .. 'n3l12'

            $table->boolean('done')->default(false);
            $table->boolean('crown')->default(false); // selesai tanpa jawaban salah, minimal sekali
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'set_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_membaca_progress');
    }
};
