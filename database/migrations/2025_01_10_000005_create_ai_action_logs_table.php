<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak audit setiap kali AI MENGUSULKAN pembuatan data.
     * status:
     *   proposed  -> AI baru mengusulkan, nunggu user konfirmasi (belum ada data nyata)
     *   confirmed -> user klik "Buat Sekarang", data asli sudah dibuat (lihat created_model_type/id)
     *   rejected  -> user klik "Batal"
     *   failed    -> validasi/eksekusi gagal saat konfirmasi
     */
    public function up(): void
    {
        Schema::create('ai_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tool_name');           // 'create_customer'
            $table->text('summary')->nullable();    // teks ringkas dari tool untuk ditampilkan di confirm card
            $table->json('payload');                // argumen yang sudah lolos validasi tool
            $table->string('status')->default('proposed');
            $table->string('created_model_type')->nullable(); // App\Models\Customer
            $table->bigInteger('created_model_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_action_logs');
    }
};
