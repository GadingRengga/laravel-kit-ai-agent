<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUGFIX: sebelumnya, untuk menemukan AiMessage (tool_call) yang berkaitan
 * dengan sebuah AiActionLog, kode selalu menebak lewat query
 *   AiMessage::where('tool_name', $actionLog->tool_name)->whereNull('content')->latest()->first()
 * Heuristik ini rapuh: kalau dalam 1 percakapan user memicu tool yang SAMA
 * lebih dari sekali sebelum draft pertama dikonfirmasi/dibatalkan (misal
 * 2x create_user beruntun), ->latest() bisa mengambil AiMessage yang salah
 * — tool_call_id yang disisipkan ke tool response jadi tidak nyambung
 * dengan tool_calls yang sebenarnya diusulkan, dan providernya menolak
 * riwayat yang "salah pasangan" itu.
 *
 * Solusinya: simpan referensi eksplisit ai_message_id di ai_action_logs
 * begitu draft dibuat (lihat AiChatService::handleToolCall), supaya
 * pencariannya jadi relasi langsung, bukan tebakan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_action_logs', function (Blueprint $table) {
            $table->foreignId('ai_message_id')
                ->nullable()
                ->after('ai_conversation_id')
                ->constrained('ai_messages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_action_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_message_id');
        });
    }
};
