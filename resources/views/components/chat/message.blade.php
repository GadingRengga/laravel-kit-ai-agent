{{--
    <x-chat.message> — Satu baris pesan chat (AI atau user), dipakai di dalam <x-chat.shell> / #chat-messages.

    Contoh (pesan AI, teks biasa):
        <x-chat.message role="ai" time="09:40">
            <p>Halo Gading 👋 Aku Netra Assistant. Ada yang bisa dibantu?</p>
        </x-chat.message>

    Contoh (pesan user):
        <x-chat.message role="user" initials="GD" time="09:41">
            Tolong buatkan contoh helper JS untuk expose fungsi ke window.
        </x-chat.message>

    Contoh (pesan AI dengan blok kode + tombol aksi):
        <x-chat.message role="ai" time="09:41" actions>
            <p>Ini caranya:</p>
            <div class="chat-code">
                <div class="chat-code-head">
                    <span>javascript</span>
                    <button class="chat-code-copy" onclick="copyChatCode(this)"><i class="fa-regular fa-copy"></i> Copy</button>
                </div>
                <pre><code>window.toggleDark = toggleDark;</code></pre>
            </div>
        </x-chat.message>

    Props:
        role       ai | user                                     (default: ai)
        initials   string|null — inisial avatar untuk role user (role ai otomatis pakai ikon sparkles)
        time       string|null — jam pesan, mis. "09:41"
        actions    bool — tampilkan baris tombol aksi (copy/like/dislike/regenerate) di bawah pesan AI
--}}
@props([
    'role' => 'ai',
    'initials' => null,
    'time' => null,
    'actions' => false,
])

<div {{ $attributes->class(['chat-msg-row', $role]) }}>
    <div class="chat-avatar {{ $role }}">
        @if($role === 'ai')
            <i class="fa-solid fa-sparkles"></i>
        @else
            {{ $initials }}
        @endif
    </div>
    <div class="chat-msg-col">
        <div class="chat-bubble {{ $role }}">
            {{ $slot }}
        </div>
        @if($time || ($actions && $role === 'ai'))
            <div class="chat-meta-row">
                @if($time)
                    <span class="chat-meta-time">{{ $time }}</span>
                @endif
                @if($actions && $role === 'ai')
                    <div class="chat-msg-actions">
                        <button class="chat-action-btn" title="Salin"><i class="fa-regular fa-copy"></i></button>
                        <button class="chat-action-btn" title="Suka"><i class="fa-regular fa-thumbs-up"></i></button>
                        <button class="chat-action-btn" title="Tidak suka"><i class="fa-regular fa-thumbs-down"></i></button>
                        <button class="chat-action-btn" title="Buat ulang"><i class="fa-solid fa-rotate-right"></i></button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
