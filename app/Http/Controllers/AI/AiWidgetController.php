<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\SendAiMessageRequest;
use App\Http\Requests\AI\StoreAiConnectionRequest;
use App\Models\Ai\AiConnection;
use App\Models\Ai\AiConversation;
use App\Models\Ai\AiProvider;
use App\Models\User;
use App\Services\AI\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller khusus floating widget AI (tombol mengambang di semua halaman).
 *
 * Sengaja DIPISAH dari AiChatController & AiConnectionController (yang
 * dipakai halaman /ai/chat penuh) supaya:
 *   - semua response di sini JSON atau partial HTML kecil (tidak ada
 *     redirect), cocok dipanggil lewat fetch() dari mana saja;
 *   - tidak mengubah / berisiko merusak perilaku controller lama yang
 *     sudah kamu pakai di halaman /ai/chat.
 *
 * State (login / chat) di-resolve murni dari data (ada AiConnection aktif
 * atau tidak), jadi tidak butuh session/flag tambahan.
 */
class AiWidgetController extends Controller
{
    public function __construct(
        private readonly AiChatService $chatService,
    ) {}

    /**
     * Dipanggil JS setiap modal widget dibuka.
     * Balikan: status koneksi + (kalau sudah connect) riwayat pesan yang
     * sudah di-render jadi HTML pakai partial yang sama dengan halaman
     * /ai/chat, supaya tampilannya konsisten tanpa duplikasi template JS.
     */
    public function state(): JsonResponse
    {
        $user = Auth::user();

        $connection = $this->activeConnection($user);

        if (! $connection) {
            return response()->json(['connected' => false]);
        }

        $conversation = $this->resolveConversation($user, $connection);

        return response()->json([
            'connected'       => true,
            'model'           => $connection->resolvedModel(),
            'conversation_id' => $conversation->id,
            'messages_html'   => $this->renderMessages($conversation),
        ]);
    }

    /** Submit form "Hubungkan Akun AI" di modal widget (BYOK, sama seperti _connection-modal). */
    public function connect(StoreAiConnectionRequest $request): JsonResponse
    {
        $user = Auth::user();
        $provider = AiProvider::where('code', $request->provider_code)->firstOrFail();

        // BUGFIX (history hanya sesuai AI yang dipilih) — lihat penjelasan
        // lengkap di AiConnectionController::store(), fix yang sama
        // diterapkan di sini karena widget juga bisa dipakai untuk connect.
        AiConnection::where('user_id', $user->id)
            ->where('ai_provider_id', '!=', $provider->id)
            ->update(['is_active' => false]);

        $connection = AiConnection::updateOrCreate(
            [
                'user_id'        => $user->id,
                'ai_provider_id' => $provider->id,
            ],
            [
                'api_key'          => $request->api_key,
                'default_model'    => $request->default_model,
                'is_active'        => true,
                'last_verified_at' => now(),
            ]
        );

        $conversation = $this->resolveConversation($user, $connection);

        return response()->json([
            'connected'       => true,
            'model'           => $connection->resolvedModel(),
            'conversation_id' => $conversation->id,
            'messages_html'   => $this->renderMessages($conversation),
        ]);
    }

    /** Tombol "Logout AI" — putus semua koneksi aktif user (biasanya cuma 1 provider). */
    public function disconnect(): JsonResponse
    {
        AiConnection::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get()
            ->each->delete();

        return response()->json(['connected' => false]);
    }

    /**
     * Kirim pesan dari widget. Balikannya HTML fragment 1 bubble
     * (teks biasa ATAU tool-confirm-card) — persis format yang dipakai
     * AiChatController::store, supaya JS bisa langsung insertAdjacentHTML.
     */
    public function send(SendAiMessageRequest $request): View
    {
        $user = Auth::user();
        $connection = $this->activeConnection($user);

        abort_unless($connection, 422, 'Hubungkan akun AI terlebih dahulu.');

        $conversation = $this->resolveConversation($user, $connection);

        $result = $this->chatService->sendUserMessage(
            conversation: $conversation,
            userText: $request->string('message')->toString(),
            user: $user,
        );

        if ($result['type'] === 'tool_draft') {
            return view('ai.partials._tool-confirm-card', ['actionLog' => $result['actionLog']]);
        }

        return view('ai.partials._message-ai', ['message' => $result['message']]);
    }

    /** Tombol "Chat Baru" di header widget. */
    public function newConversation(): JsonResponse
    {
        $user = Auth::user();
        $connection = $this->activeConnection($user);

        $conversation = AiConversation::create([
            'user_id'          => $user->id,
            'ai_connection_id' => $connection?->id,
        ]);

        return response()->json(['conversation_id' => $conversation->id]);
    }

    private function activeConnection(User $user): ?AiConnection
    {
        return AiConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    private function resolveConversation(User $user, AiConnection $connection): AiConversation
    {
        // BUGFIX (history hanya sesuai AI yang dipilih): implementasi lama
        // di sini strict filter by ai_connection_id, jadi begitu user
        // logout/connect ulang (atau ganti provider), AiConnection lama
        // dianggap "beda" dari yang baru meski user cuma mau melanjutkan
        // obrolan yang sama → widget selalu bikin percakapan BARU yang
        // kosong, dan riwayat lama seolah hilang total dari widget
        // (padahal masih ada di database, cuma tidak pernah ditemukan lagi
        // oleh query ini). Dari sudut pandang user: "history cuma nongol
        // sesuai AI yang lagi dipilih".
        //
        // Fix: ambil percakapan TERAKHIR milik user apa pun connection-nya,
        // lalu re-attach ke connection yang sedang aktif sekarang kalau
        // beda. Ini tetap menjaga jaminan dari komentar lama (conversation
        // yang dipakai chat SELALU punya ai_connection_id yang valid &
        // aktif, jadi $provider->chat() tidak akan pernah null), tapi tanpa
        // mengorbankan kontinuitas riwayat percakapan.
        $conversation = AiConversation::where('user_id', $user->id)->latest()->first();

        if (! $conversation) {
            return AiConversation::create(['user_id' => $user->id, 'ai_connection_id' => $connection->id]);
        }

        if ($conversation->ai_connection_id !== $connection->id) {
            $conversation->update(['ai_connection_id' => $connection->id]);
        }

        return $conversation;
    }

    private function renderMessages(AiConversation $conversation): string
    {
        $conversation->loadMissing('messages');

        return $conversation->messages->map(function ($message) {
            if ($message->role === 'user') {
                return view('ai.partials._message-user', ['text' => $message->content])->render();
            }

            if ($message->role === 'assistant' && $message->content) {
                return view('ai.partials._message-ai', ['message' => $message])->render();
            }

            return '';
        })->implode('');
    }
}
