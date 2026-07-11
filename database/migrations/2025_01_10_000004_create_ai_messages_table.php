<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['system', 'user', 'assistant', 'tool']);
            $table->longText('content')->nullable();     // null kalau murni tool_call tanpa teks
            $table->string('tool_name')->nullable();      // diisi kalau role = tool / assistant memanggil tool
            $table->string('tool_call_id')->nullable();   // id dari OpenAI, buat mapping request/response tool
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->timestamps();

            $table->index(['ai_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
