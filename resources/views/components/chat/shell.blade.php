{{--
    <x-chat.shell> — Kerangka lengkap AI Chat (panel percakapan + panel utama + panel asisten).
    Gabungkan dengan <x-chat.conversation-item>, <x-chat.message>, dan <x-chat.composer> di dalam slot-nya.

    Contoh:
        <x-chat.shell ai-name="Netra Assistant" ai-status="Online · merespons dalam hitungan detik">
            <x-slot:conversations>
                <div class="chat-conv-group-label">Hari Ini</div>
                <x-chat.conversation-item title="Debug LiveDomJS SPA region" snippet="Kenapa tombol ini..."
                    icon="fa-solid fa-code" time="09:41" active onclick="selectConversation(this)" />
            </x-slot:conversations>

            <x-slot:messages>
                <x-chat.message role="ai" time="09:40"><p>Halo Gading 👋</p></x-chat.message>
                <x-chat.message role="user" initials="GD" time="09:41">Tolong bantu debug ini.</x-chat.message>
            </x-slot:messages>

            <x-slot:composer>
                <x-chat.composer />
            </x-slot:composer>

            <x-slot:sidePanel>
                {{-- opsional: rekomendasi lanjutan, sumber, canvas --}}
            </x-slot:sidePanel>
        </x-chat.shell>

    Props:
        aiName      string                                          (default: "Netra Assistant")
        aiStatus    string                                          (default: "Online")
        modelLabel  string|null — label model aktif di header, mis. "Netra-1 Pro"

    Slots:
        conversations   — daftar <x-chat.conversation-item> untuk sidebar kiri
        messages        — daftar <x-chat.message> untuk area percakapan
        composer        — biasanya diisi <x-chat.composer />
        sidePanel       — konten panel asisten kanan (opsional, saran/sumber/canvas)

    JS: butuh netra-chat.js untuk toggle sidebar mobile, kirim pesan, ganti tab panel asisten, dll.
--}}
@props([
    'aiName' => 'Netra Assistant',
    'aiStatus' => 'Online · merespons dalam hitungan detik',
    'modelLabel' => null,
])

<div {{ $attributes->class(['chat-shell']) }} id="chat-shell" data-layout="classic">

    <aside class="chat-conv-panel" id="chat-conv-panel">
        <div class="chat-conv-head">
            <button type="button" class="chat-new-btn" onclick="startNewChat()">
                <i class="fa-solid fa-plus"></i> Chat Baru
            </button>
            <div class="chat-conv-search">
                <i class="fa-solid fa-magnifying-glass text-[12px] text-slate-400"></i>
                <input type="text" placeholder="Cari percakapan…" oninput="filterChatConversations(this.value)" />
            </div>
        </div>
        <div class="chat-conv-list">
            {{ $conversations ?? '' }}
        </div>
    </aside>

    <section class="chat-main">
        <div class="chat-main-header">
            <button type="button" class="chat-tool-btn lg:hidden" onclick="openConvDrawer()" title="Percakapan">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <div class="chat-ai-avatar"><i class="fa-solid fa-sparkles"></i></div>
            <div class="chat-ai-name-block min-w-0">
                <p class="text-[13.5px] font-semibold text-slate-900 dark:text-white leading-tight flex items-center gap-1.5">
                    {{ $aiName }} <span class="chat-ai-status-dot"></span>
                </p>
                <p class="chat-ai-sub text-[11.5px] text-slate-400 leading-tight">{{ $aiStatus }}</p>
            </div>
            <div class="ml-auto flex items-center gap-1.5">
                @if($modelLabel)
                    <button type="button" class="chat-model-btn" title="Model aktif">
                        <i class="fa-solid fa-microchip text-indigo-500"></i>
                        <span class="hidden sm:inline">{{ $modelLabel }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                    </button>
                @endif
                <button type="button" class="chat-tool-btn" title="Cari di percakapan"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button type="button" class="chat-tool-btn hidden lg:flex" onclick="openSidePanel()" title="Panel Asisten"><i class="fa-solid fa-table-cells-large"></i></button>
                <button type="button" class="chat-tool-btn lg:hidden" onclick="openSidePanel()" title="Panel Asisten"><i class="fa-solid fa-ellipsis-vertical"></i></button>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            {{ $messages ?? '' }}
        </div>

        {{ $composer ?? '' }}
    </section>

    @isset($sidePanel)
        <aside class="chat-side-panel" id="chat-side-panel">
            <div class="flex items-center justify-between px-4 pt-4">
                <p class="text-[13px] font-semibold text-slate-900 dark:text-white">Panel Asisten</p>
                <button type="button" class="chat-tool-btn xl:hidden" onclick="closeSidePanel()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="chat-side-body">
                {{ $sidePanel }}
            </div>
        </aside>
    @endisset

</div>
