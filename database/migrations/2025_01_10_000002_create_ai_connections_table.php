<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Koneksi akun AI milik masing-masing user (Bring Your Own Key).
     * api_key selalu disimpan terenkripsi lewat cast 'encrypted' di Model.
     */
    public function up(): void
    {
        Schema::create('ai_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_provider_id')->constrained('ai_providers')->cascadeOnDelete();
            $table->text('api_key');                    // di-cast 'encrypted' di Model, JANGAN pernah di-select ke frontend
            $table->string('default_model')->nullable(); // override model per user, fallback ke ai_providers.default_model
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ai_provider_id']); // 1 user hanya 1 koneksi aktif per provider
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_connections');
    }
};
