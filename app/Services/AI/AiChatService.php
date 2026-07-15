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
        ]);

        try {
            // ->get() melempar InvalidArgumentException kalau nama tool
            // tidak/belum terdaftar (fitur belum dibuat developer, atau AI
            // "berhalusinasi" manggil function yang gak ada) — ditangkap di
            // catch(\Throwable) di bawah, BUKAN dibiarkan jadi error 500.
            $tool = $this->tools->get($call->name);
            $draft = $tool->toDraft($call->arguments, $user);
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

            $assistantMsg->update([
                'content' => 'Maaf, saya belum bisa membuatkan data itu untuk sekarang. '
                    . 'Silakan hubungi developer aplikasi untuk menambahkan fitur ini.',
            ]);

            return ['type' => 'text', 'message' => $assistantMsg->fresh()];
        }

        $actionLog = AiActionLog::create([
            'ai_conversation_id' => $conversation->id,
            'user_id'    => $user->id,
            'tool_name'  => $call->name,
            'summary'    => $draft->summary,
            'payload'    => $draft->payload,
            'status'     => 'proposed',
        ]);

        return ['type' => 'tool_draft', 'message' => $assistantMsg, 'actionLog' => $actionLog];
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
