<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Progres per-user untuk latihan percakapan (Kaiwa). Pola sama dengan
 * user_chokai_progress: materi ada di berkas JSON (public/data/kaiwa), bukan
 * baris tabel, jadi `set_key` = id skenario di JSON itu (mis. 'l2-s1',
 * kelak 'sit-pabrik-1') dan satu-satunya pengenal yang dipakai kedua sisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_kaiwa_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('set_key', 40); // id skenario, mis. 'l2-s1'

            $table->boolean('done')->default(false);
            $table->boolean('crown')->default(false); // semua giliran lolos pada percobaan pertama, minimal sekali
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'set_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_kaiwa_progress');
    }
};
