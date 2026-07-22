{{--
  Direvisi supaya strukturnya sama persis dengan halaman lain di starter kit:
  @extends('layouts.app') + @section('page-title') buat judul/subjudul di
  header (bukan lagi ditulis manual di dalam .chat-shell), lalu isi utama
  di @section('content'). Sidebar/header/footer tetap 100% dari layout.

  Tambahan dari versi sebelumnya:
  - Tombol hapus per-percakapan di sidebar riwayat (chat-conv-delete-btn)
  - Upload gambar di composer (attach button + preview strip)
  - live-scope biar konsisten kalau halaman ini ikut dinavigasi LiveDomJS SPA
    (hapus atribut ini kalau halaman /ai/chat sengaja full reload)
--}}
@extends('layouts.app')

@section('title', 'Netra Assistant')

@section('page-title')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Netra Assistant</h1>
            <p class="text-[13px] text-indigo-400 dark:text-indigo-400 mt-0.5">
                {{ $connection ? 'Terhubung · ' . $connection->resolvedModel() : 'Belum terhubung ke akun AI' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('ai.chat.new') }}" method="POST">
                @csrf
                <button type="submit" class="chat-new-btn"><i class="fa-solid fa-plus"></i> Chat Baru</button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="chat-shell" id="chat-shell" data-layout="classic" live-scope="AiChatController">

        {{-- Mobile overlay dipakai bareng conv-panel & side-panel --}}
        <div id="chat-overlay" class="chat-overlay hidden" onclick="closeConvDrawer()"></div>

        {{-- Conversation / history panel --}}
        <aside class="chat-conv-panel" id="chat-conv-panel">
            <div class="chat-conv-head">
                <button type="button" class="chat-conv-close lg:hidden" onclick="closeConvDrawer()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="chat-conv-search">
                    <i class="fa-solid fa-magnifying-glass text-[12px] text-slate-400"></i>
                    <input type="text" placeholder="Cari percakapan…" oninput="filterChatConversations(this.value)" />
                </div>
            </div>
            <div class="chat-conv-list" id="chat-conv-list">
                @forelse ($conversations as $item)
                    @include('ai.partials._conv-item', [
                        'item' => $item,
                        'active' => $item->id === $conversation->id,
                    ])
                @empty
                    <p class="chat-conv-empty">Belum ada riwayat percakapan.</p>
                @endforelse
            </div>
        </aside>

        {{-- Main chat panel --}}
        <section class="chat-main">

            <div class="chat-main-header">
                <button class="chat-tool-btn lg:hidden" onclick="openConvDrawer()" title="Riwayat chat">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div class="chat-ai-avatar"><i class="fa-solid fa-sparkles"></i></div>
                <div class="chat-ai-name-block min-w-0">
                    <p
                        class="text-[13.5px] font-semibold text-slate-900 dark:text-white leading-tight flex items-center gap-1.5">
                        Netra Assistant
                        @if ($connection)
                            <span class="chat-ai-status-dot"></span>
                        @endif
                    </p>
                    <p class="chat-ai-sub text-[11.5px] text-slate-400 leading-tight">
                        {{ $connection ? 'Terhubung · ' . $connection->resolvedModel() : 'Belum terhubung ke ChatGPT' }}
                    </p>
                </div>
                <button type="button" class="chat-tool-btn ml-auto" title="Hapus percakapan ini"
                    onclick="deleteAiConversation({{ $conversation->id }})">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>

            @unless ($connection)
                <div class="ai-connect-banner">
                    <i class="fa-solid fa-circle-info text-indigo-500"></i>
                    <p>Hubungkan akun ChatGPT kamu dulu supaya asisten bisa mulai membantu.</p>
                    <button type="button" onclick="openAiConnectModal()">Hubungkan</button>
                </div>
            @endunless

            <div class="chat-messages" id="chat-messages">
                @forelse ($conversation->messages as $message)
                    @if ($message->role === 'user')
                        @include('ai.partials._message-user', [
                            'text' => $message->content,
                            'attachments' => $message->attachments ?? [],
                        ])
                    @elseif ($message->role === 'assistant' && $message->content)
                        @include('ai.partials._message-ai', ['message' => $message])
                    @elseif ($message->isToolCall() && $message->actionLog)
                        {{--
                            BUGFIX: sebelumnya pesan tool_call (content=null)
                            selalu di-skip di sini — sama seperti bug yang
                            diperbaiki di AiWidgetController::renderMessages().
                            Akibatnya draft CRUD yang masih 'proposed' hilang
                            dari tampilan begitu halaman di-refresh (padahal
                            actionLog-nya masih ada di DB dan tombol Batal/
                            Konfirmasi jadi tidak bisa diakses lagi). Render
                            ulang kartu konfirmasinya di sini.
                        --}}
                        @include('ai.partials._tool-confirm-card', ['actionLog' => $message->actionLog])
                    @endif
                @empty
                    <div class="chat-hero" id="chat-hero">
                        <div class="chat-hero-badge"><i class="fa-solid fa-sparkles"></i></div>
                        <h2 class="text-[19px] font-bold text-slate-900 dark:text-white">Mulai percakapan baru</h2>
                        <p class="text-[13px] text-slate-400 mt-1.5 mb-6">Contoh: "Buatkan customer baru PT Sinar Abadi,
                            email
                            info@sinarabadi.co.id"</p>
                    </div>
                @endforelse
            </div>

            <div class="chat-input-wrap">
                <div class="chat-input-inner">

                    {{-- Preview gambar yang mau diupload, muncul di atas kotak input --}}
                    <div class="chat-attach-preview hidden" id="chat-attach-preview"></div>

                    <div class="chat-input-box">
                        <button type="button" id="chat-attach-btn" class="chat-attach-btn" title="Lampirkan gambar"
                            @disabled(!$connection) onclick="document.getElementById('chat-attach-input').click()">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                        <input type="file" id="chat-attach-input" accept="image/*" multiple hidden
                            onchange="handleChatAttachChange(event)" />

                        <textarea id="chat-textarea" class="chat-textarea" rows="1"
                            placeholder="{{ $connection ? 'Tulis pesan… (bisa sisipkan gambar)' : 'Hubungkan akun ChatGPT dulu…' }}"
                            @disabled(!$connection) oninput="autoResizeChatInput(this)" onkeydown="handleChatKeydown(event)"></textarea>

                        <button id="chat-send-btn" class="chat-send-btn" onclick="sendChatMessage()"
                            @disabled(!$connection)>
                            <i class="fa-solid fa-paper-plane text-[13px]"></i>
                        </button>
                    </div>
                    <p class="chat-input-hint">Enter untuk kirim • Shift + Enter baris baru • Klik <i
                            class="fa-solid fa-paperclip"></i> untuk lampirkan gambar</p>
                </div>
            </div>
        </section>
    </div>

    @include('ai.partials._connection-modal')

    <script>
        window.AI_CONVERSATION_ID = {{ $conversation->id }};
        window.AI_SEND_URL = "{{ route('ai.chat.store', $conversation) }}";
        window.AI_DELETE_URL_TEMPLATE = "{{ route('ai.chat.destroy', ['conversation' => '__ID__']) }}";
        window.AI_NEW_CHAT_URL = "{{ route('ai.chat.new') }}";
        window.AI_INDEX_URL = "{{ route('ai.chat.index') }}";
        window.AI_CONFIRM_URL_TEMPLATE = "{{ route('ai.action.confirm', ['actionLog' => '__ID__']) }}";
        window.AI_REJECT_URL_TEMPLATE = "{{ route('ai.action.reject', ['actionLog' => '__ID__']) }}";
        window.AI_CSRF_TOKEN = "{{ csrf_token() }}";
        window.AI_USER_INITIALS = "{{ \Illuminate\Support\Str::of(Auth::user()->name)->substr(0, 2)->upper() }}";
    </script>
    @vite('resources/js/ai-chat.js')
@endsection
