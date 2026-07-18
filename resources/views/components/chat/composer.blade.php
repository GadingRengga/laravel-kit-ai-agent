{{--
    <x-chat.composer> — Input bar di bagian bawah chat (textarea auto-resize + tombol kirim).

    Contoh:
        <x-chat.composer placeholder="Tulis pesan… (Enter untuk kirim)" />

        <x-chat.composer hint="Enter untuk kirim • Shift + Enter baris baru">
            <x-slot:toolbar>
                <button class="chat-tool-btn" title="Lampirkan file"><i class="fa-solid fa-paperclip"></i></button>
                <button class="chat-tool-btn" title="Sisipkan gambar"><i class="fa-regular fa-image"></i></button>
            </x-slot:toolbar>
        </x-chat.composer>

    Props:
        placeholder   string                                       (default: "Tulis pesan…")
        hint          string|null — teks kecil di bawah input box
        onSend        string — nama fungsi JS untuk tombol kirim    (default: "sendChatMessage()")

    Slot:
        toolbar   — tombol-tombol di atas textarea (attach file, gambar, mic, dst). Kalau kosong,
                    toolbar default (attach/image/mic/template) otomatis ditampilkan.

    JS: butuh netra-chat.js — meng-handle auto-resize (`autoResizeChatInput`) dan submit via Enter
    (`handleChatKeydown`).
--}}
@props([
    'placeholder' => 'Tulis pesan…',
    'hint' => 'Enter untuk kirim • Shift + Enter baris baru • Netra Assistant bisa saja keliru',
    'onSend' => 'sendChatMessage()',
])

<div {{ $attributes->class(['chat-input-wrap']) }}>
    <div class="chat-input-inner">
        <div class="chat-input-toolbar">
            @isset($toolbar)
                {{ $toolbar }}
            @else
                <button type="button" class="chat-tool-btn" title="Lampirkan file"><i class="fa-solid fa-paperclip"></i></button>
                <button type="button" class="chat-tool-btn" title="Sisipkan gambar"><i class="fa-regular fa-image"></i></button>
                <button type="button" class="chat-tool-btn" title="Rekam suara"><i class="fa-solid fa-microphone"></i></button>
                <button type="button" class="chat-tool-btn" title="Template prompt"><i class="fa-solid fa-bolt"></i></button>
            @endisset
        </div>
        <div class="chat-input-box">
            <textarea id="chat-textarea" class="chat-textarea" rows="1" placeholder="{{ $placeholder }}"
                oninput="autoResizeChatInput(this)" onkeydown="handleChatKeydown(event)"></textarea>
            <button id="chat-send-btn" type="button" class="chat-send-btn" onclick="{{ $onSend }}">
                <i class="fa-solid fa-paper-plane text-[13px]"></i>
            </button>
        </div>
        @if($hint)
            <p class="chat-input-hint">{{ $hint }}</p>
        @endif
    </div>
</div>
