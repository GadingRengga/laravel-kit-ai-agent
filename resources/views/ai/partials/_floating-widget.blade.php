{{--
  Floating AI Widget — tombol mengambang + modal login/chat/logout.
  Include SEKALI di layouts/app.blade.php (di luar @yield('content'),
  taruh persis sebelum @stack('scripts') di penutup <body>), supaya
  persist & tampil di SELURUH halaman yang pakai layout ini.

  State ditentukan murni dari data (AiConnection aktif ada / tidak),
  di-fetch via AJAX setiap modal dibuka — bukan dari session, jadi
  selalu akurat walau user connect/logout dari tab lain.

  Pakai sistem modal Netra yang sudah ada (ntModal + class nt-modal-*)
  dan class chat-* yang sudah kamu punya dari integrasi ai-kit
  (chat-msg-row, chat-avatar, chat-bubble, chat-hero, chat-send-btn, dst)
  supaya tampilannya konsisten dengan halaman /ai/chat penuh.
--}}
@auth
    <button type="button" id="ai-widget-fab" class="ai-widget-fab" onclick="AiWidget.open()" aria-haspopup="dialog"
        aria-label="Buka Netra Assistant" data-state-url="{{ route('ai.widget.state') }}"
        data-connect-url="{{ route('ai.widget.connect') }}" data-disconnect-url="{{ route('ai.widget.disconnect') }}"
        data-send-url="{{ route('ai.widget.send') }}" data-new-url="{{ route('ai.widget.new') }}"
        data-confirm-url-template="{{ route('ai.action.confirm', ['actionLog' => '__ID__']) }}"
        data-reject-url-template="{{ route('ai.action.reject', ['actionLog' => '__ID__']) }}"
        data-model-options="{{ json_encode(config('ai_models', [])) }}">
        <i class="fa-solid fa-sparkles"></i>
        <span class="ai-widget-fab-dot" id="ai-widget-fab-dot"></span>
    </button>

    <div id="ai-widget-modal" class="nt-modal-backdrop" onclick="ntModal.closeOnBackdrop(event, 'ai-widget-modal')">
        <div class="nt-modal nt-modal-sm ai-widget-dialog" data-nt-modal-dialog>

            {{-- Header --}}
            <div class="nt-modal-header ai-widget-header">
                <div class="nt-modal-icon nt-modal-icon-primary"><i class="fa-solid fa-sparkles"></i></div>
                <div class="nt-modal-title-wrap">
                    <p class="nt-modal-title" id="ai-widget-title">Netra Assistant</p>
                    <p class="nt-modal-subtitle" id="ai-widget-subtitle">Memuat…</p>
                </div>

                <button type="button" class="ai-widget-icon-btn" id="ai-widget-new-btn" title="Chat baru"
                    onclick="AiWidget.newConversation()" style="display:none;">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button type="button" class="ai-widget-icon-btn" id="ai-widget-logout-btn" title="Logout AI"
                    onclick="AiWidget.logout()" style="display:none;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
                <button type="button" class="nt-modal-close" onclick="ntModal.close('ai-widget-modal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="nt-modal-body ai-widget-body" id="ai-widget-body">

                {{-- State: loading --}}
                <div id="ai-widget-loading" class="ai-widget-loading">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <p>Menghubungkan…</p>
                </div>

                {{-- State: belum login AI --}}
                <div id="ai-widget-login" class="ai-widget-login" style="display:none;">
                    <div class="ai-widget-login-icon"><i class="fa-solid fa-key"></i></div>
                    <h3>Hubungkan Akun AI</h3>
                    <p>Pilih provider & masukkan API key kamu untuk mulai chat dengan asisten dari halaman mana saja.</p>

                    <form id="ai-widget-connect-form" onsubmit="AiWidget.connect(event)">
                        {{--
                            Dulu ini hidden input hardcode value="openai".
                            Sekarang dropdown supaya provider lain (mis. Gemini)
                            juga bisa dipilih user tanpa developer perlu ubah blade
                            tiap kali nambah provider baru. Data diambil dari tabel
                            ai_providers — tambah row baru di situ (code, label,
                            default_model, is_active) otomatis muncul di sini.
                        --}}
                        <label>Provider</label>
                        <select name="provider_code" id="ai-widget-provider-select" required class="ai-widget-input"
                            onchange="AiWidget.populateModels(this.value)">
                            @foreach (\App\Models\Ai\AiProvider::where('is_active', true)->orderBy('label')->get() as $provider)
                                <option value="{{ $provider->code }}">{{ $provider->label }}</option>
                            @endforeach
                        </select>

                        <label>API Key</label>
                        <input type="password" name="api_key" required placeholder="Masukkan API key…"
                            class="ai-widget-input" />

                        <label>Model</label>
                        <select name="default_model" id="ai-widget-model-select" class="ai-widget-input"></select>

                        <button type="submit" class="ai-widget-btn-primary">
                            <span class="ai-widget-btn-label">Hubungkan</span>
                            <i class="fa-solid fa-circle-notch fa-spin ai-widget-btn-spinner" style="display:none;"></i>
                        </button>
                        <p class="ai-widget-error" id="ai-widget-connect-error"></p>
                    </form>
                </div>

                {{-- State: sudah login AI → chat --}}
                <div id="ai-widget-chat" class="ai-widget-chat" style="display:none;">
                    <div class="chat-messages ai-widget-messages" id="ai-widget-messages"></div>
                </div>
            </div>

            {{-- Footer: input pesan, cuma muncul di state chat --}}
            <div class="nt-modal-footer ai-widget-footer" id="ai-widget-footer" style="display:none;">
                <div class="chat-input-box ai-widget-input-box">
                    <textarea id="ai-widget-textarea" rows="1" placeholder="Tulis pesan…" oninput="AiWidget.autoResize(this)"
                        onkeydown="AiWidget.handleKeydown(event)"></textarea>
                    <button type="button" id="ai-widget-send-btn" class="chat-send-btn" onclick="AiWidget.send()">
                        <i class="fa-solid fa-paper-plane text-[13px]"></i>
                    </button>
                </div>
                @if (Route::has('ai.chat.index'))
                    <a href="{{ route('ai.chat.index') }}" class="ai-widget-fullpage-link">
                        Buka tampilan penuh <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                @endif
            </div>

        </div>
    </div>
@endauth
