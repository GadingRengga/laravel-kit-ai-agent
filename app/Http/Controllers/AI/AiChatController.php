<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\SendAiMessageRequest;
use App\Models\Ai\AiActionLog; // NOTE: hilang di versi asli — dipakai di confirmToolAction()/rejectToolAction() tapi belum di-import. Sudah dibetulkan di sini.
use App\Models\Ai\AiConnection;
use App\Models\Ai\AiConversation;
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
            'conversation'   => $conversation->load('messages'),
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

        // BUGFIX: kalau tool_name di draft lama ini sudah tidak terdaftar lagi
        // (mis. entry-nya dihapus dari config/ai_tools.php setelah draft dibuat,
        // atau ada deploy versi baru pas draft masih menggantung di layar user),
        // $this->tools->get() melempar InvalidArgumentException. Sebelumnya ini
        // TIDAK ditangkap sama sekali di sini → user dapat error 500 mentah pas
        // klik "Buat Sekarang". Sekarang ditangani sama seperti kegagalan tool
        // lainnya: tandai gagal + kasih pesan yang masuk akal.
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

            $actionLog->update([
                'status'             => 'confirmed',
                'created_model_type' => $model::class,
                'created_model_id'   => $model->getKey(),
            ]);
        } catch (\Throwable $e) {
            $actionLog->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
            report($e);

            return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
        }

        // Sengaja TIDAK memanggil AI lagi cuma untuk kalimat "sudah dibuat" —
        // itu murni templat statis, gak perlu ongkos token tambahan.
        return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog->fresh()]);
    }

    /** User menekan "Batal" di tool-confirm-card. */
    public function rejectToolAction(AiActionLog $actionLog): View
    {
        $this->authorizeActionLog($actionLog);

        $actionLog->update(['status' => 'rejected']);

        return view('ai.partials._tool-confirm-card', ['actionLog' => $actionLog]);
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
     */
    private function allowedToolsForContext(?string $context): ?array
    {
        return match ($context) {
            'customer'   => ['create_customer'],
            'quotation'  => ['create_customer', 'create_quotation'],
            'order'      => ['create_order'],
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
        abort_if($actionLog->status !== 'proposed', 409, 'Aksi ini sudah diproses sebelumnya.');
    }
}
