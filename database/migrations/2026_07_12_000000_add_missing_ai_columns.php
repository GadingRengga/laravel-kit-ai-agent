<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| BUGFIX migration
|--------------------------------------------------------------------------
| Kode Controller/Service/Model sudah lama mereferensikan kolom-kolom ini
| (mis. AiChatController::store() mengirim `attachments`, AiConversation
| punya `title`/`summary`/`summarized_until` di $fillable), tapi tidak ada
| migration yang benar-benar menambahkannya di paket kode ini — jadi kalau
| kolomnya memang belum ada di tabel kamu, INSERT-nya akan gagal (kolom
| tidak dikenal) walau bug JS/PHP lain sudah dibetulkan.
|
| Migration ini aman dijalankan berkali-kali / di database yang sudah
| punya sebagian kolom ini karena tiap kolom dicek dulu dengan
| hasColumn() sebelum ditambahkan.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_messages') && ! Schema::hasColumn('ai_messages', 'attachments')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->json('attachments')->nullable()->after('content');
            });
        }

        if (Schema::hasTable('ai_conversations')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_conversations', 'title')) {
                    $table->string('title')->nullable();
                }
                if (! Schema::hasColumn('ai_conversations', 'summary')) {
                    $table->text('summary')->nullable();
                }
                if (! Schema::hasColumn('ai_conversations', 'summarized_until')) {
                    $table->timestamp('summarized_until')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_messages') && Schema::hasColumn('ai_messages', 'attachments')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable('ai_conversations')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                foreach (['title', 'summary', 'summarized_until'] as $col) {
                    if (Schema::hasColumn('ai_conversations', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
