<?php

namespace App\Services\AI;

use App\Models\Ai\AiActionLog;
use App\Models\Ai\AiConversation;
use App\Models\Ai\AiMessage;
use App\Models\User;
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
    ): array {
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'    => 'user',
            'content' => $userText,
        ]);

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
        $toolSchemas = $this->tools->toSchemaArray(
            $allowedTools ? $this->tools->only($allowedTools) : null
        );

        $provider = $this->providers->resolve($conversation->connection);

        $response = $provider->chat(
            connection: $conversation->connection,
            messages: $this->buildMessagePayload($conversation),
            toolSchemas: $toolSchemas,
        );

        // Kasus A: AI mau memanggil tool → jangan simpan sebagai teks biasa,
        // proses jadi draft dan JANGAN sentuh database bisnis dulu.
        if ($response->hasToolCalls()) {
            return $this->handleToolCall($conversation, $response->toolCalls[0], $user);
        }

        // Kasus B: balasan teks biasa.
        $message = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'    => 'assistant',
            'content' => $response->content,
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
    private function buildMessagePayload(AiConversation $conversation): array
    {
        $payload = [['role' => 'system', 'content' => config('ai.system_prompt')]];

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
