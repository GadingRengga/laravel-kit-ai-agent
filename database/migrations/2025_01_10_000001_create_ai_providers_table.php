<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master daftar provider AI yang didukung sistem.
     * Baru "openai" yang aktif sekarang, tapi tabel ini
     * yang bikin nambah provider lain nanti gak perlu migration ulang skema besar.
     */
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();     // 'openai', 'anthropic', dst
            $table->string('label');               // 'ChatGPT (OpenAI)'
            $table->string('default_model')->nullable(); // 'gpt-4.1-mini' misalnya
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
