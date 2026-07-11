<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_connection_id')->nullable()->constrained('ai_connections')->nullOnDelete();
            $table->string('title')->nullable();   // diisi otomatis dari pesan pertama, boleh di-rename user
            $table->text('summary')->nullable();    // ringkasan rolling, dipakai buat hemat token (lihat AiChatService)
            $table->timestamp('summarized_until')->nullable(); // penanda pesan terakhir yang sudah masuk summary
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
