<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kana_characters', function (Blueprint $table) {
            $table->id();
            $table->string('script', 10);              // 'hiragana' | 'katakana'
            $table->string('character');                // あ, ガ, ぱ, ...
            $table->string('romaji');                    // a, ga, pa, ...
            $table->string('type', 15);                  // 'gojuon' | 'dakuten' | 'handakuten'
            $table->string('row');                        // 'a','ka','sa',... (gojuon row/column group, for chart layout)
            $table->unsignedInteger('column')->default(0); // position within the row (0=a,1=i,2=u,3=e,4=o)
            $table->foreignId('base_character_id')->nullable()
                ->constrained('kana_characters')->nullOnDelete(); // dakuten/handakuten -> its gojuon base
            $table->unsignedInteger('stroke_count');
            $table->json('stroke_order');                 // ["step 1 text", "step 2 text", ...] (id) — see stroke_order_en
            $table->json('stroke_order_en');
            $table->text('usage_note_id')->nullable();     // extra note (mnemonic, look-alike warning, etc.)
            $table->text('usage_note_en')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['script', 'type']);
            $table->index(['script', 'row', 'column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kana_characters');
    }
};
