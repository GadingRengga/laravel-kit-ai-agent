{{--
  Kerangka halaman ini mengikuti struktur pages/ai-chat.html (Netra UI) yang
  sudah ada — sidebar/header/footer TETAP pakai layout Blade utama kamu
  (x-app-layout / @extends, sesuaikan baris paling atas & bawah file ini).
  Yang berubah dari versi statis sebelumnya cuma bagian dalam <div class="chat-shell">
  yang sekarang di-render dari data asli.
--}}
@extends('layouts.app') {{-- ganti sesuai nama layout utama starter kit-mu --}}

@section('content')
<div class="chat-shell" id="chat-shell" data-layout="classic">

  {{-- Conversation panel --}}
  <aside class="chat-conv-panel" id="chat-conv-panel">
    <div class="chat-conv-head">
      <form action="{{ route('ai.chat.new') }}" method="POST">
        @csrf
        <button type="submit" class="chat-new-btn"><i class="fa-solid fa-plus"></i> Chat Baru</button>
      </form>
      <div class="chat-conv-search">
        <i class="fa-solid fa-magnifying-glass text-[12px] text-slate-400"></i>
        <input type="text" placeholder="Cari percakapan…" oninput="filterChatConversations(this.value)" />
      </div>
    </div>
    <div class="chat-conv-list">
      @foreach ($conversations as $item)
        <div class="chat-conv-item {{ $item->id === $conversation->id ? 'active' : '' }}"
             onclick="location.href='{{ route('ai.chat.index', ['conversation' => $item->id]) }}'">
          <div class="chat-conv-icon"><i class="fa-solid fa-message"></i></div>
          <div class="chat-conv-body">
            <p class="chat-conv-title">{{ $item->title ?? 'Percakapan baru' }}</p>
            <p class="chat-conv-snippet">{{ $item->updated_at->diffForHumans() }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </aside>

  {{-- Main chat panel --}}
  <section class="chat-main">

    <div class="chat-main-header">
      <button class="chat-tool-btn lg:hidden" onclick="openConvDrawer()"><i class="fa-solid fa-bars-staggered"></i></button>
      <div class="chat-ai-avatar"><i class="fa-solid fa-sparkles"></i></div>
      <div class="chat-ai-name-block min-w-0">
        <p class="text-[13.5px] font-semibold text-slate-900 dark:text-white leading-tight flex items-center gap-1.5">
          Netra Assistant
          @if ($connection) <span class="chat-ai-status-dot"></span> @endif
        </p>
        <p class="chat-ai-sub text-[11.5px] text-slate-400 leading-tight">
          {{ $connection ? 'Terhubung · '.$connection->resolvedModel() : 'Belum terhubung ke ChatGPT' }}
        </p>
      </div>
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
          @include('ai.partials._message-user', ['text' => $message->content])
        @elseif ($message->role === 'assistant' && $message->content)
          @include('ai.partials._message-ai', ['message' => $message])
        @endif
        {{-- Catatan: tool draft yang masih 'proposed' idealnya dimuat ulang di
             sini juga (join ke ai_action_logs by ai_message terkait) supaya
             kalau user refresh halaman, kartu konfirmasi tetap muncul.
             Disederhanakan di skeleton ini — tinggal tambah relasi kalau perlu. --}}
      @empty
        <div class="chat-hero" id="chat-hero">
          <div class="chat-hero-badge"><i class="fa-solid fa-sparkles"></i></div>
          <h2 class="text-[19px] font-bold text-slate-900 dark:text-white">Mulai percakapan baru</h2>
          <p class="text-[13px] text-slate-400 mt-1.5 mb-6">Contoh: "Buatkan customer baru PT Sinar Abadi, email
            info@sinarabadi.co.id"</p>
        </div>
      @endforelse
    </div>

    <div class="chat-input-wrap">
      <div class="chat-input-inner">
        <div class="chat-input-box">
          <textarea id="chat-textarea" class="chat-textarea" rows="1"
            placeholder="{{ $connection ? 'Tulis pesan…' : 'Hubungkan akun ChatGPT dulu…' }}"
            @disabled(!$connection)
            oninput="autoResizeChatInput(this)" onkeydown="handleChatKeydown(event)"></textarea>
          <button id="chat-send-btn" class="chat-send-btn" onclick="sendChatMessage()" @disabled(!$connection)>
            <i class="fa-solid fa-paper-plane text-[13px]"></i>
          </button>
        </div>
        <p class="chat-input-hint">Enter untuk kirim • Shift + Enter baris baru</p>
      </div>
    </div>
  </section>
</div>

@include('ai.partials._connection-modal')

<script>
  const AI_CONVERSATION_ID = {{ $conversation->id }};
  const AI_SEND_URL = "{{ route('ai.chat.store', $conversation) }}";
  const AI_CONFIRM_URL_TEMPLATE = "{{ route('ai.action.confirm', ['actionLog' => '__ID__']) }}";
  const AI_REJECT_URL_TEMPLATE = "{{ route('ai.action.reject', ['actionLog' => '__ID__']) }}";
  const AI_CSRF_TOKEN = "{{ csrf_token() }}";
</script>
@vite('resources/js/ai-chat.js')
@endsection
