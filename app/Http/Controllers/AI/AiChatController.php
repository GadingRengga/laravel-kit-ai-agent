<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\SendAiMessageRequest;
use App\Models\Ai\AiActionLog;
use App\Models\Ai\AiConnection;
use App\Models\Ai\AiConversation;
use App\Models\Ai\AiMessage;
use App\Services\AI\AiChatService;
use App\Services\AI\AiToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AiChatController extends Controller
{
    public function __construct(
        private readonly AiChatService $chatService,
        private readonly AiToolRegistry $tools,
    ) {}

    /** Halaman utama chat — reuse resources/views/ai/chat.blade.php */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $connection = AiConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $conversation = $this->resolveConversation($request, $user, $connection);

        // BUGFIX (history kurang informatif): sebelumnya query ini cuma
        // ambil id/title/updated_at, jadi sidebar tidak pernah tahu isi
        // pesan terakhir untuk ditampilkan sebagai cuplikan (snippet).
        // Ditambah eager-load 1 pesan terakhir per percakapan supaya
        // _conv-item.blade.php bisa menampilkan cuplikan asli, bukan cuma
        // jam relatif yang diulang dua kali (lihat _conv-item.blade.php).
        $conversations = AiConversation::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get(['id', 'title', 'updated_at'])
            ->load(['messages' => fn($q) => $q->latest()->limit(1)]);

        return view('ai.chat', [
            'conversation'   => $conversation->load('messages.actionLog'),
            'connection'     => $connection,
            'conversations'  => $conversations,
        ]);
    }

    /** Buat percakapan baru lalu redirect ke halaman chat-nya. */
    public function newConversation(Request $request): RedirectResponse
    {
        $conversation = AiConversation::create([
            'user_id'          => Auth::id(),
            'ai_connection_id' => optional(
                AiConnection::where('user_id', Auth::id())->where('is_active', true)->first()
            )->id,
        ]);

        return redirect()->route('ai.chat.index', ['conversation' => $conversation->id]);
    }

    /**
     * Hapus 1 percakapan beserta semua pesan & lampiran gambarnya.
     * Dipanggil via fetch(DELETE) dari tombol trash di sidebar / header chat.
     */
    public function destroyConversation(AiConversation $conversation): JsonResponse|RedirectResponse
    {
        $this->authorizeConversation($conversation);

        // Bersihkan file gambar yang tersimpan di disk 'public' sebelum baris DB-nya dihapus,
        // supaya tidak ninggalin file yatim di storage/app/public/ai-attachments/...
        foreach ($conversation->messages as $message) {
            foreach ((array) ($message->attachments ?? []) as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $conversation->messages()->delete();
        $conversation->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('ai.chat.index');
    }

    /**
     * Endpoint AJAX dipanggil dari textarea chat (lihat resources/js/ai-chat.js).
     * Sekarang juga menerima gambar (multipart/form-data, field images[]).
     * Balikan: partial HTML berisi 1 bubble balasan AI (teks biasa ATAU
     * tool-confirm-card), langsung di-append ke #chat-messages oleh JS.
     */
    public function store(SendAiMessageRequest $request, AiConversation $conversation): View
    {
        $this->authorizeConversation($conversation);

        abort_unless($conversation->connection, 422, 'Hubungkan akun ChatGPT terlebih dahulu.');

        $attachmentPaths = $this->storeUploadedImages($request, $conversation);

        $result = $this->chatService->sendUserMessage(
            conversation: $conversation,
            userText: $request->string('message')->toString(),
            user: Auth::user(),
            allowedTools: $this->allowedToolsForContext($request->input('context')),
            attachments: $attachmentPaths,
            // ^ NOTE: parameter baru. AiChatService::sendUserMessage() perlu disesuaikan
            // supaya menyimpan $attachments ke kolom `attachments` (json) pada pesan
            // role=user yang dibuatnya, dan (opsional) menyisipkan gambar ke payload
            // yang dikirim ke provider AI kalau providernya mendukung vision/image input.
        );

        return $this->renderResult($result);
    }

    /** User menekan "Buat Sekarang" di tool-confirm-card. */
    public function confirmToolAction(AiActionLog $actionLog): View
    {
        $this->authorizeActionLog($actionLog);

        // BUGFIX (race condition antar-tab/klik ganda): sebelumnya status
        // selain 'proposed' membuat authorizeActionLog() abort(409) — dari
        // sisi user (mis. 2 tab dibuka bersamaan, atau tombol double-click)
        // ini tampil sebagai error mentah padahal aksinya sendiri sudah
        // beres di request lain. Cukup tampilkan kartu apa adanya, jangan
        // proses ulang / jangan sisipkan tool response ganda.
        if ($actionLog->status !== 'proposed') {
            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog]);
        }

        $conversation = $actionLog->conversation;

        // BUGFIX: sebelumnya dicari lewat heuristik tool_name+latest() di
        // sini langsung. Sekarang pakai resolveToolCallMessage() yang
        // prioritaskan relasi ai_message_id eksplisit (lihat AiActionLog),
        // supaya tidak salah pasangan kalau ada beberapa draft tool yang
        // sama dalam 1 percakapan.
        $assistantMsg = $actionLog->resolveToolCallMessage();

        try {
            $tool = $this->tools->get($actionLog->tool_name);
        } catch (\Throwable $e) {
            $actionLog->update([
                'status' => 'failed',
                'failure_reason' => "Tool [{$actionLog->tool_name}] sudah tidak tersedia lagi.",
            ]);

            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
        }

        try {
            $model = $tool->confirm($actionLog->payload, Auth::user());

            // BUGFIX: setelah tool berhasil dieksekusi, sisipkan tool response
            // ke history percakapan. Tanpa ini, history pesan akan memiliki
            // assistant message dengan tool_calls tapi TIDAK ADA tool response
            // setelahnya — format percakapan jadi invalid. Provider AI (baik
            // OpenAI maupun Gemini) akan error saat user kirim pesan berikutnya
            // (misalnya "terimakasih") karena ada tool_call yang menggantung
            // tanpa hasil.
            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'         => 'tool',
                'content'      => 'Eksekusi tool berhasil.',
                'tool_call_id' => $assistantMsg?->tool_call_id ?? ('call_' . $actionLog->id),
                'tool_name'    => $actionLog->tool_name,
            ]);

            $actionLog->update([
                'status'             => 'confirmed',
                'created_model_type' => $model::class,
                'created_model_id'   => $model->getKey(),
            ]);

            // JANGAN panggil AI untuk merespon — terlalu riskan karena:
            // 1. Kalau panggilan AI gagal (timeout/rate limit), history jadi
            //    tool_call → tool_response → user("terimakasih")
            //    → DUA user berurutan, Gemini/AI lain menolak format ini.
            // 2. AI bisa memanggil tool LAGI secara rekursif, memperumit state.
            //
            // Solusi: insert assistant message default sebagai penutup agar
            // alternasi role tetap valid. User bisa lanjut chat seperti biasa.
            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'    => 'assistant',
                'content' => 'Data berhasil diproses.',
            ]);

            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
        } catch (\Throwable $e) {
            // Kalau gagal, sisipkan tool response gagal supaya history tetap valid
            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'         => 'tool',
                'content'      => 'Eksekusi tool gagal: ' . $e->getMessage(),
                'tool_call_id' => $assistantMsg?->tool_call_id ?? ('call_' . $actionLog->id),
                'tool_name'    => $actionLog->tool_name,
            ]);

            $actionLog->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
            report($e);

            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
        }
    }

    /** User menekan "Batal" di tool-confirm-card. */
    public function rejectToolAction(AiActionLog $actionLog): View
    {
        $this->authorizeActionLog($actionLog);

        // BUGFIX (race condition antar-tab/klik ganda) — sama seperti di
        // confirmToolAction(): kalau statusnya sudah bukan 'proposed', jangan
        // proses ulang / sisipkan tool response ganda, cukup tampilkan state
        // terkini.
        if ($actionLog->status !== 'proposed') {
            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog]);
        }

        $conversation = $actionLog->conversation;

        // BUGFIX (root cause "AI kadang tidak mau CRUD" & error setelah CRUD):
        // Sebelumnya method ini HANYA update status jadi 'rejected' — tidak
        // pernah menyisipkan tool response. Padahal saat tool_draft dibuat
        // (lihat AiChatService::handleToolCall), sudah ada AiMessage role
        // 'assistant' dengan tool_calls terisi (tool_name != null). Assistant
        // message dengan tool_calls WAJIB diikuti tool response — ini syarat
        // keras format OpenAI ("messages with role 'tool' must be a response
        // to a preceding message with 'tool_calls'"), dan tanpa itu, provider
        // menolak request berikutnya (400) begitu conversation ini dipakai
        // lagi (termasuk saat user mencoba CRUD lain, atau sekadar bilang
        // "terimakasih"). Sisipkan tool response di sini juga, sama seperti
        // confirmToolAction(), supaya history selalu valid apa pun keputusan
        // user. Pakai resolveToolCallMessage() (relasi ai_message_id) supaya
        // tidak salah pasangan seperti heuristik lama.
        $assistantMsg = $actionLog->resolveToolCallMessage();

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'         => 'tool',
            'content'      => 'Dibatalkan oleh user.',
            'tool_call_id' => $assistantMsg?->tool_call_id ?? ('call_' . $actionLog->id),
            'tool_name'    => $actionLog->tool_name,
        ]);

        // Tutup dengan assistant message biasa (bukan panggil AI lagi) —
        // alasan sama seperti di confirmToolAction(): supaya alternasi role
        // tetap valid tanpa risiko AI memanggil tool lagi secara rekursif.
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'    => 'assistant',
            'content' => 'Baik, aksi dibatalkan.',
        ]);

        $actionLog->update(['status' => 'rejected']);

        return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
    }

    /**
     * Simpan gambar yang diupload user ke disk 'public', dikelompokkan per
     * percakapan supaya gampang dibersihkan waktu percakapan dihapus.
     *
     * @return array<int, string> daftar path relatif di disk 'public'
     */
    private function storeUploadedImages(SendAiMessageRequest $request, AiConversation $conversation): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        return collect($request->file('images'))
            ->filter(fn($file) => $file->isValid())
            ->map(fn($file) => $file->store("ai-attachments/{$conversation->id}", 'public'))
            ->values()
            ->all();
    }

    /**
     * Batasi tools yang dikirim ke AI sesuai halaman asal chat dibuka —
     * mengurangi ukuran payload 'tools' yang dikirim tiap request.
     *
     * Tool name di sini HARUS cocok dengan 'name' di config/ai_tools.php.
     * Kalau tidak ada, panggil $this->tools->only() di AiChatService dan
     * tool yang tidak dikenal akan silent-skip.
     */
    private function allowedToolsForContext(?string $context): ?array
    {
        return match ($context) {
            'user'      => null, // semua tool user (create/read/update/delete user)
            default      => null, // null = semua tool terdaftar
        };
    }

    private function renderResult(array $result): View
    {
        if ($result['type'] === 'tool_draft') {
            return view('ai.partials._tool-confirm-card', ['actionLog' => $result['actionLog']]);
        }

        return view('ai.partials._message-ai', [
            'message' => $result['message'],
        ]);
    }

    private function resolveConversation(Request $request, $user, ?AiConnection $connection): AiConversation
    {
        if ($request->filled('conversation')) {
            $conversation = AiConversation::findOrFail($request->input('conversation'));
            abort_unless($conversation->user_id === $user->id, 403);

            return $conversation;
        }

        return AiConversation::where('user_id', $user->id)->latest()->first()
            ?? AiConversation::create(['user_id' => $user->id, 'ai_connection_id' => $connection?->id]);
    }

    private function authorizeConversation(AiConversation $conversation): void
    {
        abort_unless($conversation->user_id === Auth::id(), 403);
    }

    private function authorizeActionLog(AiActionLog $actionLog): void
    {
        abort_unless($actionLog->user_id === Auth::id(), 403);
        // BUGFIX: dulu ada abort_if(status !== 'proposed', 409, ...) di sini.
        // Itu bikin klik ganda / 2 tab terbuka bersamaan tampil sebagai error
        // mentah ke user walau aksinya sendiri sudah selesai di request lain.
        // Sekarang confirmToolAction()/rejectToolAction() masing-masing
        // menangani status non-'proposed' secara idempotent (langsung
        // tampilkan state terkini tanpa proses ulang).
    }
}
