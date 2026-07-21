<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| BUGFIX: Kolom tool_arguments untuk menyimpan arguments tool call
|--------------------------------------------------------------------------
| Sebelumnya AiMessage hanya menyimpan tool_name tapi TIDAK menyimpan
| arguments yang diberikan AI saat memanggil tool. Akibatnya, saat pesan
| assistant dikirim ulang ke provider AI, tidak ada data tool_calls yang
| lengkap — provider AI tidak bisa menghubungkan assistant message dengan
| tool response setelahnya.
|
| Sekarang kita tambah kolom tool_arguments (json) agar toApiMessage()
| bisa mengembalikan struktur tool_calls yang valid ke OpenAI/Gemini.
| Migration ini aman dijalankan berkali-kali karena pakai hasColumn().
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_messages') && ! Schema::hasColumn('ai_messages', 'tool_arguments')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->json('tool_arguments')->nullable()->after('tool_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_messages') && Schema::hasColumn('ai_messages', 'tool_arguments')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->dropColumn('tool_arguments');
            });
        }
    }
};
