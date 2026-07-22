<?php

namespace App\Services\AI;

use App\Models\Ai\AiActionLog;
use App\Models\Ai\AiConversation;
use App\Models\Ai\AiMessage;
use App\Models\Superuser\User;
use App\Services\AI\DTO\ToolCallDTO;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class AiChatService
{
    public function __construct(
        private readonly AiProviderManager $providers,
        private readonly AiToolRegistry $tools,
    ) {}

    /**
     * Titik masuk utama dipanggil controller. Mengembalikan array siap
     * dipakai Blade partial:
     *   ['type' => 'text', 'message' => AiMessage]
     *   ['type' => 'tool_draft', 'message' => AiMessage, 'actionLog' => AiActionLog]
     *
     * @param  string[]|null  $allowedTools  batasi tool sesuai context halaman (hemat token)
     */
    public function sendUserMessage(
        AiConversation $conversation,
        string $userText,
        User $user,
        ?array $allowedTools = null,
        array $attachments = [],
        // ^ BUGFIX: parameter ini sebelumnya TIDAK ADA di sini, padahal
        // AiChatController::store() sudah memanggilnya lewat named argument
        // `attachments: $attachmentPaths`. Named argument yang tidak
        // dikenali method tujuan = Fatal Error di PHP ("Unknown named
        // parameter $attachments"), jadi SETIAP pesan yang dikirim dari
        // /ai/chat (termasuk tanpa gambar sekalipun) gagal dengan 500,
        // yang dari sisi user terasa seperti "tombol kirim tidak berfungsi".
    ): array {
        // BUGFIX (draft menggantung): kalau masih ada AiActionLog berstatus
        // 'proposed' di percakapan ini — user mengirim pesan baru tanpa klik
        // Konfirmasi/Batal dulu — tutup draft itu SEBELUM user message baru
        // disimpan. Tanpa ini, AiMessage assistant dengan tool_calls terisi
        // tetap ikut terkirim ke provider (lewat buildMessagePayload) tanpa
        // pernah diikuti tool response, dan provider (terutama OpenAI) MENOLAK
        // riwayat yang punya tool_calls menggantung begitu percakapan dipakai
        // lagi. _tool-confirm-card.blade.php sudah lama punya cabang tampilan
        // untuk status 'superseded' ini; method inilah yang tadinya belum
        // pernah benar-benar dibuat.
        $this->closeDanglingDrafts($conversation);

        // BUGFIX: sebelumnya selalu mengirim key 'attachments' (walau
        // nilainya null) ke AiMessage::create(). Karena 'attachments'
        // sudah masuk $fillable, Eloquent tetap menyertakan kolom itu di
        // query INSERT meski nilainya null — jadi kolomnya WAJIB ada di
        // DB walau kamu cuma kirim pesan teks biasa tanpa gambar sama
        // sekali. Sekarang key itu hanya disertakan kalau memang ada
        // attachment beneran, supaya pesan teks biasa (dari widget MAUPUN
        // dari halaman chat penuh tanpa lampiran) tidak pernah butuh
        // kolom itu ada di DB. Kolom baru jadi relevan begitu kamu
        // benar-benar pakai fitur lampir gambar di halaman chat penuh
        // (widget belum punya fitur ini sama sekali).
        $messageData = [
            'ai_conversation_id' => $conversation->id,
            'role'    => 'user',
            'content' => $userText,
        ];

        if (! empty($attachments)) {
            $messageData['attachments'] = $attachments;
        }

        AiMessage::create($messageData);

        // BUGFIX (history kurang informatif): judul percakapan sebelumnya
        // TIDAK PERNAH diisi di mana pun, jadi sidebar riwayat selalu
        // menampilkan fallback "Percakapan baru" untuk semua chat, selamanya.
        // Isi otomatis dari pesan pertama user begitu percakapan belum
        // punya judul, supaya riwayat gampang dibedakan satu sama lain.
        if (blank($conversation->title) && trim($userText) !== '') {
            $conversation->update([
                'title' => \Illuminate\Support\Str::limit(trim($userText), 45),
            ]);
        }

        return $this->requestAssistantReply($conversation, $user, $allowedTools);
    }

    /**
     * Dipanggil ulang setelah kita menyisipkan tool response (lihat
     * AiChatController::confirmToolAction) supaya AI bisa merangkai kalimat
     * konfirmasi setelah data benar-benar dibuat.
     */
    public function requestAssistantReply(
        AiConversation $conversation,
        User $user,
        ?array $allowedTools = null,
    ): array {
        // Urutan filter: (1) context halaman ($allowedTools, kalau diisi
        // controller — hemat token, cuma kirim skema yang relevan dgn
        // halaman aktif), lalu (2) hak akses menu POSISI JABATAN user
        // (AiToolRegistry::allowedFor) — AI tidak akan ditawari/mencoba
        // fungsi yang toh akan ditolak GenericModelTool::authorize().
        $toolSchemas = $this->tools->toSchemaArray(
            $this->tools->allowedFor(
                $user,
                $allowedTools ? $this->tools->only($allowedTools) : null
            )
        );

        $provider = $this->providers->resolve($conversation->connection);
        $payload = $this->buildMessagePayload($conversation, $user, $allowedTools);

        $response = $provider->chat(connection: $conversation->connection, messages: $payload, toolSchemas: $toolSchemas);

        // Kasus A: AI mau memanggil tool → jangan simpan sebagai teks biasa,
        // proses jadi draft dan JANGAN sentuh database bisnis dulu.
        if ($response->hasToolCalls()) {
            return $this->handleToolCall($conversation, $response->toolCalls[0], $user);
        }

        // BUGFIX (balasan AI kosong): sebelumnya $response->content langsung
        // disimpan & dirender apa adanya, walau isinya null/string kosong.
        // Dari laporan nyata (lihat laravel.log), penyebabnya BUKAN selalu
        // token reasoning habis (finishReason yang muncul justru "STOP",
        // bukan "MAX_TOKENS") — kadang provider (terutama model "lite")
        // memang sesekali balikin teks kosong walau statusnya sukses.
        // Karena ini murni flakiness sesaat, coba ulang SEKALI dulu — kalau
        // percobaan kedua juga kosong, baru benar-benar dianggap gagal &
        // ditampilkan pesan fallback ke user.
        $content = trim((string) $response->content);

        if ($content === '') {
            $retryResponse = $provider->chat(connection: $conversation->connection, messages: $payload, toolSchemas: $toolSchemas);

            if ($retryResponse->hasToolCalls()) {
                return $this->handleToolCall($conversation, $retryResponse->toolCalls[0], $user);
            }

            $response = $retryResponse;
            $content = trim((string) $response->content);
        }

        if ($content === '') {
            report(new \RuntimeException(
                "Balasan AI kosong 2x berturut-turut untuk conversation #{$conversation->id} — "
                    . 'kemungkinan jatah token habis (reasoning), konten kena filter, atau flakiness model. '
                    . 'Pertimbangkan naikkan ai.max_response_tokens atau ganti model kalau ini sering terjadi.'
            ));

            $content = 'Maaf, saya tidak bisa memberi balasan untuk itu barusan. '
                . 'Coba ulangi pertanyaannya, atau perpendek permintaannya.';
        }

        // Kasus B: balasan teks biasa.
        $message = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'    => 'assistant',
            'content' => $content,
            'prompt_tokens'     => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
        ]);

        $this->maybeSummarize($conversation);

        return ['type' => 'text', 'message' => $message];
    }

    private function handleToolCall(AiConversation $conversation, ToolCallDTO $call, User $user): array
    {
        $assistantMsg = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'      => 'assistant',
            'content'   => null,
            'tool_name' => $call->name,
            'tool_arguments' => $call->arguments,
            'tool_call_id' => $call->id,
        ]);

        try {
            // ->get() melempar InvalidArgumentException kalau nama tool
            // tidak/belum terdaftar (fitur belum dibuat developer, atau AI
            // "berhalusinasi" manggil function yang gak ada) — ditangkap di
            // catch(\Throwable) di bawah, BUKAN dibiarkan jadi error 500.
            $tool = $this->tools->get($call->name);
            $draft = $tool->toDraft($call->arguments, $user);

            // DIRECT RESULT (READ operation) — langsung kirim balik ke AI
            // sebagai tool response, tanpa perlu draft/confirm dari user.
            if ($draft->isDirect) {
                $resultContent = $this->formatDirectResult($draft);
                AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'         => 'tool',
                    'content'      => $resultContent,
                    'tool_call_id' => $call->id,
                ]);
                $assistantMsg->update(['prompt_tokens' => 0, 'completion_tokens' => 0]);
                return $this->requestAssistantReply($conversation, $user);
            }
        } catch (ValidationException $e) {
            // Argumen AI gak valid — balas ke AI sebagai tool response supaya
            // ia bisa tanya ulang ke user, BUKAN dilempar sebagai error ke user.
            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'         => 'tool',
                'content'      => 'Data belum lengkap: ' . $e->validator->errors()->first(),
                'tool_call_id' => $call->id,
            ]);

            return $this->requestAssistantReply($conversation, $user);
        } catch (AuthorizationException $e) {
            // User tidak punya izin (Gate/Policy menolak) — beda pesan dari
            // "fitur belum ada", supaya user gak salah paham perlu hubungi developer.
            $assistantMsg->update([
                'content' => 'Maaf, kamu tidak punya izin untuk melakukan aksi ini.',
            ]);

            return ['type' => 'text', 'message' => $assistantMsg->fresh()];
        } catch (\Throwable $e) {
            // Semua kegagalan lain: tool belum terdaftar, Model/Action belum
            // dibuat, atau error tak terduga lain di dalam tool. Jangan
            // tampilkan detail teknis ke user — cukup arahkan ke developer,
            // dan simpan detailnya ke log supaya developer bisa cek.
            report($e);

            // Kalau masih dalam konteks direct (READ error), beri tahu AI
            // supaya bisa merespon dengan sopan ke user
            if (isset($draft) && $draft->isDirect) {
                AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'         => 'tool',
                    'content'      => 'Terjadi kesalahan saat membaca data: ' . $e->getMessage(),
                    'tool_call_id' => $call->id,
                ]);
                return $this->requestAssistantReply($conversation, $user);
            }

            $assistantMsg->update([
                'content' => 'Maaf, saya belum bisa memproses permintaan itu untuk sekarang. '
                    . 'Silakan hubungi developer aplikasi untuk menambahkan fitur ini.',
            ]);

            return ['type' => 'text', 'message' => $assistantMsg->fresh()];
        }

        // BUGFIX: kolom ai_message_id (migration 2026_07_22_000000) sebelumnya
        // TIDAK PERNAH diisi di sini, padahal migration itu dibuat khusus supaya
        // resolveToolCallMessage() bisa mencocokkan draft ↔ tool_call message lewat
        // relasi eksplisit (bukan tebakan tool_name+content-null+latest()). Tanpa
        // baris ini, resolveToolCallMessage() SELALU jatuh ke heuristik lama, dan
        // kalau user memicu tool yang sama 2x sebelum draft pertama dikonfirmasi/
        // dibatalkan, tool_call_id bisa salah pasang lagi — persis bug yang migration
        // itu seharusnya menutup.
        $actionLog = AiActionLog::create([
            'ai_conversation_id' => $conversation->id,
            'ai_message_id' => $assistantMsg->id,
            'user_id'    => $user->id,
            'tool_name'  => $call->name,
            'summary'    => $draft->summary,
            'payload'    => $draft->payload,
            'status'     => 'proposed',
        ]);

        return ['type' => 'tool_draft', 'message' => $assistantMsg, 'actionLog' => $actionLog];
    }

    /**
     * Format hasil READ jadi teks yang bisa dibaca AI, sehingga AI bisa
     * merangkai kalimat respon yang natural ke user dan LANJUT ke tool
     * lain jika user memberi perintah berikutnya.
     *
     * Support menampilkan relasi (seperti roles, employee, createdBy)
     * yang di-load via with() di query — data array/object dari relasi
     * akan diformat sebagai sub-item.
     *
     * PENTING: Di akhir selalu ada instruksi bahwa AI BOLEH langsung
     * memanggil tool lain (update/delete) berdasarkan data ini tanpa
     * harus menjawab dengan daftar ulang.
     */
    private function formatDirectResult(\App\Services\AI\DTO\AiToolResult $draft): string
    {
        $results = $draft->directResult;

        // ── Hasil AGGREGATE (angka tunggal) ─────────────────────────────────
        // Dibedakan dari hasil list biasa: aggregate tanpa group_by selalu
        // associative dengan key 'value' & 'metric' (lihat
        // GenericModelTool::executeAggregate()), BUKAN array numerik of item.
        if (is_array($results) && array_key_exists('value', $results) && array_key_exists('metric', $results)) {
            $metricLabel = match ($results['metric']) {
                'sum' => 'Total',
                'avg' => 'Rata-rata',
                default => 'Jumlah',
            };
            $columnLabel = $results['column'] ? " {$results['column']}" : '';

            return "{$metricLabel}{$columnLabel}: {$results['value']}\n\n---\n"
                . 'INSTRUKSI: Angka ini SUDAH DIHITUNG PASTI oleh sistem (bukan estimasi/tebakan). '
                . 'Sampaikan apa adanya ke user dengan bahasa natural, JANGAN dihitung ulang, '
                . 'dibulatkan, atau diperkirakan ulang sendiri.';
        }

        // ── Hasil AGGREGATE dengan GROUP BY (per kelompok) ──────────────────
        if (is_array($results) && isset($results[0]) && is_array($results[0]) && array_key_exists('value', $results[0])) {
            $lines = array_map(function ($row) {
                $groupKey = array_key_first(array_diff_key($row, ['value' => null]));
                $groupVal = $groupKey ? ($row[$groupKey] ?? '(kosong)') : '?';
                return "- {$groupVal}: {$row['value']}";
            }, $results);

            return "Hasil analisis per kelompok:\n" . implode("\n", $lines) . "\n\n---\n"
                . 'INSTRUKSI: Angka-angka ini SUDAH FINAL dari sistem (bukan estimasi). '
                . 'Sampaikan apa adanya ke user, JANGAN dijumlahkan/dihitung ulang secara manual.';
        }

        if (empty($results)) {
            return 'Hasil pencarian: tidak ada data ditemukan. '
                . 'Kamu boleh memberitahu user bahwa data tidak ditemukan, '
                . 'dan tanya apakah user ingin mencari dengan kata kunci lain.';
        }

        // ── Helper untuk format relasi ─────────────────────────────────────
        $formatRelations = function (array $item): array {
            $relationLines = [];
            foreach ($item as $key => $value) {
                // Skip kolom biasa (id, name, email, dll) — handle di bagian utama
                if (is_scalar($value) || is_null($value)) {
                    continue;
                }

                // Format relasi BelongsToMany seperti roles — array of objects
                if (is_array($value)) {
                    $labels = [];
                    foreach ($value as $relItem) {
                        if (is_array($relItem)) {
                            $relName = $relItem['name'] ?? $relItem['title'] ?? $relItem['slug'] ?? json_encode($relItem);
                            $labels[] = $relName;
                        }
                    }
                    if (! empty($labels)) {
                        $relationLines[] = "{$key}: " . implode(', ', $labels);
                    }
                }
            }
            return $relationLines;
        };

        // ── Helper untuk format field scalar ──────────────────────────────
        $formatScalars = function (array $item, array $skipKeys = []): array {
            $lines = [];
            $skip = array_merge($skipKeys, ['password', 'remember_token', 'avatar', 'last_login_ip']);
            foreach ($item as $key => $value) {
                if (in_array($key, $skip, true)) continue;
                if (is_scalar($value) && !is_null($value)) {
                    // Format boolean jadi teks
                    if (is_bool($value)) {
                        $value = $value ? 'Aktif' : 'Tidak Aktif';
                    }
                    $lines[] = "{$key}: {$value}";
                }
            }
            return $lines;
        };

        // ── Single item — tampilkan detail lengkap ────────────────────────
        if (count($results) === 1) {
            $item = $results[0];
            $lines = [];

            // Kolom utama
            $lines[] = "─── DATA DETAIL ───";
            $lines = array_merge($lines, $formatScalars($item));

            // Relasi
            $relLines = $formatRelations($item);
            if (! empty($relLines)) {
                $lines[] = "─── RELASI ───";
                $lines = array_merge($lines, $relLines);
            }

            return implode("\n", $lines)
                . "\n\n---\n"
                . 'INSTRUKSI: Sampaikan data ini ke user dengan bahasa natural. '
                . 'Jika user langsung memberi perintah lanjutan (ubah/hapus data ini), '
                . 'KAMU BOLEH LANGSUNG PANGGIL TOOL update_xxx / delete_xxx dengan ID yang sesuai. '
                . 'JANGAN tanya "apa lagi yang bisa dibantu" — langsung proses perintah user.';
        }

        // ── Multiple items — tampilkan daftar ringkas ─────────────────────
        $lines = [];
        foreach ($results as $index => $item) {
            // Cari kolom identifier (name, title, code, email, id)
            $label = $item['name'] ?? $item['title'] ?? $item['code'] ?? $item['email'] ?? $item['id'] ?? "#" . ($index + 1);

            // Tambah info role jika ada relasi roles
            $roleInfo = '';
            if (! empty($item['roles'])) {
                $roleNames = [];
                foreach ($item['roles'] as $role) {
                    if (is_array($role) && ! empty($role['name'])) {
                        $roleNames[] = $role['name'];
                    }
                }
                if (! empty($roleNames)) {
                    $roleInfo = ' [' . implode(', ', $roleNames) . ']';
                }
            }

            $lines[] = "- {$label} (ID: {$item['id']}){$roleInfo}";
        }

        return "Ditemukan " . count($results) . " data:\n" . implode("\n", $lines)
            . "\n\n---\n"
            . 'INSTRUKSI: Sampaikan daftar ini ke user secara ringkas, '
            . 'sertakan informasi role jika ada. '
            . 'Jika user memberi perintah spesifik pada salah satu data (misal "hapus paijo" atau "ubah user id 5"), '
            . 'KAMU BOLEH LANGSUNG PANGGIL TOOL yang sesuai dengan ID yang benar. '
            . 'JANGAN ulangi daftar lengkap lagi — langsung eksekusi perintah user.';
    }

    /**
     * Susun payload messages yang DIKIRIM ke API — bukan seluruh history
     * mentah dari DB. Ini implementasi strategi hemat token:
     *   system prompt + ringkasan (kalau ada) + N pesan terakhir.
     */
    private function buildMessagePayload(
        AiConversation $conversation,
        User $user,
        ?array $allowedTools = null,
    ): array {
        $payload = [['role' => 'system', 'content' => config('ai.system_prompt')]];

        // Suntik daftar KEMAMPUAN NYATA milik user ini ke prompt. Tanpa ini,
        // saat user bertanya "saya bisa akses apa saja?", AI cuma menebak dari
        // contoh generik di system_prompt (customer/quotation/order) — TIDAK
        // peduli permission user sebenarnya. Dengan blok ini, jawaban AI soal
        // "apa yang bisa saya lakukan" selalu berbasis tool yang benar-benar
        // diizinkan untuk user (hasil AiToolRegistry::allowedFor + filter
        // context halaman kalau ada).
        $payload[] = [
            'role'    => 'system',
            'content' => $this->buildCapabilityContext($user, $allowedTools),
        ];

        if ($conversation->summary) {
            $payload[] = [
                'role' => 'system',
                'content' => "Ringkasan percakapan sebelumnya:\n{$conversation->summary}",
            ];
        }

        // 1. Ambil ID dari N pesan terbaru terlebih dahulu
        $recentIds = $conversation->messages()
            ->when($conversation->summarized_until, fn($q) => $q->where('created_at', '>', $conversation->summarized_until))
            ->orderByDesc('id')
            ->limit(config('ai.history_window') ?? 10)
            ->pluck('id');

        // 2. Ambil data aslinya dan urutkan secara Ascending (kronologis: terlama ke terbaru)
        $recent = $conversation->messages()
            ->whereIn('id', $recentIds)
            ->orderBy('id', 'asc')
            ->get();

        // 3. Masukkan ke payload (HAPUS fungsi ->reverse() yang membingungkan)
        foreach ($recent as $msg) {
            $payload[] = $msg->toApiMessage();
        }

        return $payload;
    }

    /**
     * Bangun blok system message berisi DAFTAR KEMAMPUAN NYATA milik user
     * ini — sumber kebenaran saat user bertanya "saya bisa akses apa saja?".
     *
     * Daftar diambil dari tool yang benar-benar diizinkan
     * (AiToolRegistry::allowedFor, yang di balik layar mengecek
     * User::hasPermission / hasMenuAbility), lalu—kalau controller mengisi
     * $allowedTools—dipersempit lagi ke context halaman aktif. Jadi isi blok
     * ini SELALU sinkron dengan permission user, bukan contoh hardcoded.
     *
     * @param  string[]|null  $allowedTools
     */
    private function buildCapabilityContext(User $user, ?array $allowedTools = null): string
    {
        $tools = $this->tools->allowedFor(
            $user,
            $allowedTools ? $this->tools->only($allowedTools) : null
        );

        if (empty($tools)) {
            return 'KEMAMPUAN AKTIF UNTUK USER INI: (kosong). '
                . 'User ini BELUM punya izin untuk membuat/mengubah data apa pun lewat kamu. '
                . 'Kalau user bertanya bisa akses apa saja, jawab jujur bahwa saat ini belum ada '
                . 'aksi yang bisa kamu jalankan untuk mereka, dan sarankan menghubungi admin '
                . 'untuk pengaturan hak akses. JANGAN mengarang daftar fitur.';
        }

        $lines = array_map(
            fn($tool) => '- ' . $tool->name() . ': ' . $tool->description(),
            $tools
        );

        return "KEMAMPUAN AKTIF UNTUK USER INI (berdasarkan hak akses/permission mereka):\n"
            . implode("\n", $lines)
            . "\n\nSaat user bertanya \"saya bisa akses apa saja / apa yang bisa kamu bantu\", "
            . 'jawab HANYA berdasarkan daftar di atas — jangan menyebut fitur yang tidak ada di daftar, '
            . 'dan jangan mengarang. Kalau user minta sesuatu di luar daftar ini, jelaskan dengan sopan '
            . 'bahwa mereka belum punya akses untuk itu.';
    }

    /**
     * Tutup semua draft tool-call yang masih 'proposed' di percakapan ini
     * (belum di-confirm/reject user) dengan status 'superseded', dan
     * sisipkan tool response penutup supaya riwayat tetap valid — sama
     * seperti pola di AiChatController::rejectToolAction(), cuma dipicu
     * otomatis oleh pesan baru, bukan klik tombol "Batal".
     *
     * Dipanggil di awal sendUserMessage() sebelum user message baru
     * disimpan, supaya tidak ada tool_calls yang menggantung tanpa
     * tool response saat requestAssistantReply() dipanggil berikutnya.
     */
    private function closeDanglingDrafts(AiConversation $conversation): void
    {
        $dangling = $conversation->actionLogs()->where('status', 'proposed')->get();

        foreach ($dangling as $actionLog) {
            $assistantMsg = $actionLog->resolveToolCallMessage();

            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'         => 'tool',
                'content'      => 'Dibatalkan otomatis: percakapan dilanjutkan tanpa konfirmasi/pembatalan usulan ini.',
                'tool_call_id' => $assistantMsg?->tool_call_id ?? ('call_' . $actionLog->id),
                'tool_name'    => $actionLog->tool_name,
            ]);

            $actionLog->update(['status' => 'superseded']);
        }
    }

    /**
     * Kalau percakapan sudah panjang, ringkas pesan-pesan lama jadi satu
     * paragraf lalu tandai `summarized_until` — request berikutnya jadi
     * jauh lebih murah karena gak perlu kirim ulang semuanya.
     */
    private function maybeSummarize(AiConversation $conversation): void
    {
        $unsummarizedCount = $conversation->messages()
            ->when($conversation->summarized_until, fn($q) => $q->where('created_at', '>', $conversation->summarized_until))
            ->count();

        if ($unsummarizedCount < config('ai.summarize_threshold')) {
            return;
        }

        $toSummarize = $conversation->messages()
            ->when($conversation->summarized_until, fn($q) => $q->where('created_at', '>', $conversation->summarized_until))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $transcript = $toSummarize->map(fn($m) => "{$m->role}: {$m->content}")->implode("\n");

        $provider = $this->providers->resolve($conversation->connection);

        $response = $provider->chat(
            connection: $conversation->connection,
            messages: [
                ['role' => 'system', 'content' => 'Ringkas percakapan berikut jadi maksimal 5 kalimat, fokus ke keputusan/data penting saja.'],
                ['role' => 'user', 'content' => $transcript],
            ],
            toolSchemas: [],
        );

        $conversation->update([
            'summary'          => $response->content,
            'summarized_until' => $toSummarize->last()->created_at,
        ]);
    }
}
