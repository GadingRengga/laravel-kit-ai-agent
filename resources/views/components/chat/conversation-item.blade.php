{{--
    <x-chat.conversation-item> — Satu item di daftar percakapan pada sidebar chat.

    Contoh:
        <x-chat.conversation-item title="Debug LiveDomJS SPA region" snippet="Kenapa tombol ini reload penuh..."
            icon="fa-solid fa-code" time="09:41" active onclick="selectConversation(this)" />

    Props:
        title     string
        snippet   string|null — cuplikan pesan terakhir
        icon      string — class FontAwesome untuk ikon topik (default: fa-solid fa-message)
        time      string|null — waktu singkat, mis. "09:41" / "Kmrn" / "Sen"
        active    bool
--}}
@props([
    'title',
    'snippet' => null,
    'icon' => 'fa-solid fa-message',
    'time' => null,
    'active' => false,
])

<div {{ $attributes->class(['chat-conv-item', 'active' => $active]) }}>
    <div class="chat-conv-icon"><i class="{{ $icon }}"></i></div>
    <div class="chat-conv-body">
        <p class="chat-conv-title">{{ $title }}</p>
        @if($snippet)
            <p class="chat-conv-snippet">{{ $snippet }}</p>
        @endif
    </div>
    @if($time)
        <span class="chat-conv-time">{{ $time }}</span>
    @endif
</div>
