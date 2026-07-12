/* ═══════════════════════════════════════════════════════
   AI Widget — tombol mengambang + modal (login / chat / logout)
   Tampil di semua halaman lewat layouts/app.blade.php.

   Butuh dimuat SETELAH netra-modal.js (window.ntModal) & netra-base.js.
   Import di resources/js/app.js:
     import './ai-widget';
   ═══════════════════════════════════════════════════════ */
const AiWidget = (() => {
  let urls = {};
  let modelOptions = {};
  let loaded = false;
  let sending = false;

  function fab() {
    return document.getElementById('ai-widget-fab');
  }

  function readUrls() {
    const btn = fab();
    if (!btn) return {};
    return {
      state: btn.dataset.stateUrl,
      connect: btn.dataset.connectUrl,
      disconnect: btn.dataset.disconnectUrl,
      send: btn.dataset.sendUrl,
      new: btn.dataset.newUrl,
      confirmTpl: btn.dataset.confirmUrlTemplate,
      rejectTpl: btn.dataset.rejectUrlTemplate,
    };
  }

  function readModelOptions() {
    const btn = fab();
    if (!btn || !btn.dataset.modelOptions) return {};
    try {
      return JSON.parse(btn.dataset.modelOptions);
    } catch (err) {
      return {};
    }
  }

  /**
   * Isi ulang <select id="ai-widget-model-select"> sesuai provider yang
   * dipilih. Dipanggil saat form login pertama tampil (provider default =
   * option pertama) DAN tiap kali user ganti provider (onchange di blade).
   * Daftar model per provider datang dari config/ai_models.php lewat
   * data-model-options di tombol FAB — jadi nambah/ubah model cukup edit
   * config itu, tidak perlu sentuh JS ini sama sekali.
   */
  function populateModels(providerCode) {
    const select = el('ai-widget-model-select');
    if (!select) return;

    const options = modelOptions[providerCode] || [];
    select.innerHTML = '';

    if (options.length === 0) {
      select.innerHTML = '<option value="">(Pakai default provider)</option>';
      return;
    }

    options.forEach((opt) => {
      const el2 = document.createElement('option');
      el2.value = opt.value;
      el2.textContent = opt.label;
      select.appendChild(el2);
    });
  }

  function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function el(id) {
    return document.getElementById(id);
  }

  function showState(name) {
    ['loading', 'login', 'chat'].forEach((s) => {
      const node = el(`ai-widget-${s}`);
      if (node) node.style.display = s === name ? '' : 'none';
    });
    const isChat = name === 'chat';
    el('ai-widget-footer').style.display = isChat ? '' : 'none';
    el('ai-widget-new-btn').style.display = isChat ? '' : 'none';
    el('ai-widget-logout-btn').style.display = isChat ? '' : 'none';
  }

  function emptyHero() {
    return `
      <div class="chat-hero ai-widget-hero" id="ai-widget-hero">
        <div class="chat-hero-badge"><i class="fa-solid fa-sparkles"></i></div>
        <h2>Mulai percakapan baru</h2>
        <p>Tanyakan apa saja, atau minta bantuan langsung dari halaman ini.</p>
      </div>`;
  }

  function scrollToBottom() {
    const list = el('ai-widget-messages');
    if (list) list.scrollTop = list.scrollHeight;
  }

  function applyConnected(data) {
    el('ai-widget-subtitle').textContent = `Terhubung · ${data.model}`;
    el('ai-widget-fab-dot')?.classList.add('is-on');

    const list = el('ai-widget-messages');
    list.innerHTML = (data.messages_html && data.messages_html.trim().length)
      ? data.messages_html
      : emptyHero();

    scrollToBottom();
    showState('chat');
  }

  function applyDisconnected() {
    el('ai-widget-subtitle').textContent = 'Belum terhubung';
    el('ai-widget-fab-dot')?.classList.remove('is-on');
    showState('login');
    // Provider default = option pertama di <select provider_code> —
    // langsung populate model-nya biar dropdown Model gak kosong.
    populateModels(el('ai-widget-provider-select')?.value);
  }

  async function open() {
    urls = readUrls();
    modelOptions = readModelOptions();
    window.ntModal.open('ai-widget-modal');
    showState('loading');

    try {
      const res = await fetch(urls.state, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();
      loaded = true;
      data.connected ? applyConnected(data) : applyDisconnected();
    } catch (err) {
      applyDisconnected();
    }
  }

  function close() {
    window.ntModal.close('ai-widget-modal');
  }

  async function connect(event) {
    event.preventDefault();
    const form = el('ai-widget-connect-form');
    const btn = form.querySelector('.ai-widget-btn-primary');
    const label = form.querySelector('.ai-widget-btn-label');
    const spinner = form.querySelector('.ai-widget-btn-spinner');
    const errorEl = el('ai-widget-connect-error');

    errorEl.textContent = '';
    btn.disabled = true;
    label.style.display = 'none';
    spinner.style.display = '';

    try {
      const res = await fetch(urls.connect, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });

      const data = await res.json();

      if (!res.ok) {
        errorEl.textContent = data.errors?.api_key?.[0] || data.message || 'API key tidak valid, coba lagi.';
        return;
      }

      form.reset();
      applyConnected(data);
    } catch (err) {
      errorEl.textContent = 'Gagal terhubung ke server.';
    } finally {
      btn.disabled = false;
      label.style.display = '';
      spinner.style.display = 'none';
    }
  }

  async function logout() {
    if (!confirm('Putuskan koneksi AI? Kamu perlu hubungkan ulang untuk chat lagi.')) return;

    try {
      await fetch(urls.disconnect, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      });
    } finally {
      applyDisconnected();
    }
  }

  async function newConversation() {
    try {
      await fetch(urls.new, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      });
    } finally {
      el('ai-widget-messages').innerHTML = emptyHero();
    }
  }

  function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    updateSendBtnState();
  }

  // BUGFIX (widget UI kurang enak): tombol kirim di widget sebelumnya
  // TIDAK PERNAH mendapat class `.active` (lihat .chat-send-btn.active di
  // netra-chat.css), jadi tampilannya selalu warna pudar/non-aktif walau
  // user sudah mengetik sesuatu — beda dengan halaman /ai/chat penuh yang
  // sudah punya updateSendBtnState() untuk ini. Disamakan di sini.
  function updateSendBtnState() {
    const ta = el('ai-widget-textarea');
    const btn = el('ai-widget-send-btn');
    if (!ta || !btn) return;
    btn.classList.toggle('active', ta.value.trim().length > 0);
  }

  function handleKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      send();
    }
  }

  function appendTyping() {
    const list = el('ai-widget-messages');
    const row = document.createElement('div');
    row.className = 'chat-msg-row ai';
    row.id = 'ai-widget-typing';
    row.innerHTML = `
      <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
      <div class="chat-msg-col">
        <div class="chat-bubble ai chat-typing"><span></span><span></span><span></span></div>
      </div>`;
    list.appendChild(row);
    scrollToBottom();
  }

  function removeTyping() {
    el('ai-widget-typing')?.remove();
  }

  function appendError(text) {
    const list = el('ai-widget-messages');
    const row = document.createElement('div');
    row.className = 'chat-msg-row ai';
    row.innerHTML = `
      <div class="chat-avatar ai"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div class="chat-msg-col"><div class="chat-bubble ai" style="color:#DC2626;">${text}</div></div>`;
    list.appendChild(row);
    scrollToBottom();
  }

  async function send() {
    if (sending) return;
    const textarea = el('ai-widget-textarea');
    const list = el('ai-widget-messages');
    const text = textarea.value.trim();
    if (!text) return;

    el('ai-widget-hero')?.remove();

    const userRow = document.createElement('div');
    userRow.className = 'chat-msg-row user';
    userRow.innerHTML = `
      <div class="chat-avatar user">Me</div>
      <div class="chat-msg-col">
        <div class="chat-bubble user"></div>
        <div class="chat-meta-row"><span class="chat-meta-time">${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span></div>
      </div>`;
    userRow.querySelector('.chat-bubble').textContent = text; // textContent = aman dari XSS
    list.appendChild(userRow);

    textarea.value = '';
    autoResize(textarea);
    updateSendBtnState();
    textarea.disabled = true;
    sending = true;
    scrollToBottom();
    appendTyping();

    try {
      const res = await fetch(urls.send, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          Accept: 'text/html',
        },
        body: JSON.stringify({ message: text }),
      });

      removeTyping();

      if (!res.ok) {
        appendError(res.status === 422 ? 'Hubungkan akun AI dulu, ya.' : 'Terjadi kesalahan, coba lagi.');
        return;
      }

      const html = await res.text();
      list.insertAdjacentHTML('beforeend', html);
      scrollToBottom();
    } catch (err) {
      removeTyping();
      appendError('Gagal terhubung ke server.');
    } finally {
      textarea.disabled = false;
      textarea.focus();
      sending = false;
    }
  }

  /* ── Tool confirm-card actions (dipakai tombol di _tool-confirm-card.blade.php) ──
     Didefinisikan global di sini karena widget ini muncul di semua halaman,
     bukan cuma halaman /ai/chat (yang sudah punya versi sendiri di ai-chat.js). */
  async function replaceActionCard(card, url) {
    if (!card) return;
    card.style.opacity = '.5';
    card.style.pointerEvents = 'none';

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'text/html',
        },
      });
      const html = await res.text();
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html.trim();
      card.replaceWith(wrapper.firstElementChild);
    } catch (err) {
      card.style.opacity = '1';
      card.style.pointerEvents = 'auto';
    }
  }

  function confirmAction(actionLogId) {
    const card = document.getElementById(`ai-action-${actionLogId}`);
    replaceActionCard(card, urls.confirmTpl.replace('__ID__', actionLogId));
  }

  function rejectAction(actionLogId) {
    const card = document.getElementById(`ai-action-${actionLogId}`);
    replaceActionCard(card, urls.rejectTpl.replace('__ID__', actionLogId));
  }

  return {
    open, close, connect, logout, newConversation,
    autoResize, handleKeydown, send, populateModels,
    confirmAction, rejectAction,
  };
})();

window.AiWidget = AiWidget;

// Jembatan nama fungsi supaya tombol di _tool-confirm-card.blade.php
// (onclick="confirmAiAction(...)"/"rejectAiAction(...)") tetap jalan
// walau kartu itu muncul di dalam widget (bukan di halaman /ai/chat).
if (typeof window.confirmAiAction === 'undefined') {
  window.confirmAiAction = (id) => AiWidget.confirmAction(id);
}
if (typeof window.rejectAiAction === 'undefined') {
  window.rejectAiAction = (id) => AiWidget.rejectAction(id);
}