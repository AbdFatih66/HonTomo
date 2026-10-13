<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);          // 'google', later 'github', ...
            $table->string('provider_id');           // stable id from the provider
            $table->string('provider_email')->nullable();
            $table->text('avatar')->nullable();
            $table->timestamps();

            // one external identity can belong to only one user...
            $table->unique(['provider', 'provider_id']);
            // ...and a user links each provider at most once.
            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
