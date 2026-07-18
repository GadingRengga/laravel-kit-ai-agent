# Netra UI — AI Chat (Blade Component)

Komponen di `resources/views/components/chat/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `shell.blade.php` | `<x-chat.shell>` | Kerangka lengkap: panel percakapan + panel utama + panel asisten. |
| `message.blade.php` | `<x-chat.message>` | Satu baris pesan (AI atau user), mendukung blok kode & tombol aksi. |
| `composer.blade.php` | `<x-chat.composer>` | Input bar bawah (textarea auto-resize + tombol kirim + toolbar). |
| `conversation-item.blade.php` | `<x-chat.conversation-item>` | Satu item di daftar percakapan (sidebar kiri). |

## Instalasi

Pastikan `netra-chat.css` dan `netra-chat.js` sudah ter-load (di atas `netra-base.css/js`).

## Contoh Pakai

```blade
<x-chat.shell ai-name="Netra Assistant" ai-status="Online · merespons dalam hitungan detik" model-label="Netra-1 Pro">

    <x-slot:conversations>
        <div class="chat-conv-group-label">Hari Ini</div>
        <x-chat.conversation-item title="Debug LiveDomJS SPA region" snippet="Kenapa tombol ini reload penuh…"
            icon="fa-solid fa-code" time="09:41" active onclick="selectConversation(this)" />
        <x-chat.conversation-item title="Migrasi skema Event & MarketEvent" snippet="Gimana cara grouping by…"
            icon="fa-solid fa-database" time="08:02" onclick="selectConversation(this)" />
    </x-slot:conversations>

    <x-slot:messages>
        <x-chat.message role="ai" time="09:40">
            <p>Halo Gading 👋 Aku Netra Assistant. Ada yang bisa dibantu?</p>
        </x-chat.message>

        <x-chat.message role="user" initials="GD" time="09:41">
            Tolong buatkan contoh helper JS untuk expose fungsi ke window.
        </x-chat.message>

        <x-chat.message role="ai" time="09:41" actions>
            <p>Tentu, begini caranya:</p>
            <div class="chat-code">
                <div class="chat-code-head">
                    <span>javascript</span>
                    <button class="chat-code-copy" onclick="copyChatCode(this)"><i class="fa-regular fa-copy"></i> Copy</button>
                </div>
                <pre><code>window.toggleDark = toggleDark;</code></pre>
            </div>
        </x-chat.message>
    </x-slot:messages>

    <x-slot:composer>
        <x-chat.composer />
    </x-slot:composer>

    <x-slot:sidePanel>
        <p class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Lanjutkan dengan</p>
        <div class="chat-suggest-item"><i class="fa-solid fa-arrow-turn-up fa-rotate-90 text-indigo-400"></i>Buatkan unit test</div>
    </x-slot:sidePanel>

</x-chat.shell>
```

## Props

**`<x-chat.shell>`**: `aiName`, `aiStatus`, `modelLabel` (opsional — sembunyikan tombol model kalau kosong). Slot: `conversations`, `messages`, `composer`, `sidePanel` (opsional, panel kanan otomatis tersembunyi kalau slot ini tidak diisi).

**`<x-chat.message>`**: `role` (ai/user), `initials` (untuk role user), `time`, `actions` (bool — tampilkan copy/like/dislike/regenerate, khusus role ai).

**`<x-chat.composer>`**: `placeholder`, `hint`, `onSend` (nama fungsi JS tombol kirim). Slot `toolbar` untuk mengganti tombol attach/image/mic/template default.

**`<x-chat.conversation-item>`**: `title`, `snippet`, `icon`, `time`, `active` (bool).

## Catatan

Semua interaksi (kirim pesan, auto-resize textarea, ganti percakapan, tab panel asisten, copy code block) dikendalikan oleh `netra-chat.js` — komponen ini hanya menyediakan markup yang sudah sesuai dengan selector yang dipakai skrip tersebut (id `#chat-messages`, `#chat-textarea`, `#chat-send-btn`, dll). Jangan render `<x-chat.shell>` lebih dari sekali dalam satu halaman karena beberapa id bersifat unik per halaman.
