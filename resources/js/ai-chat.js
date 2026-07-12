/* ═══════════════════════════════════════════════════════
   Netra UI — AI Chat JS (halaman penuh /ai/chat)
   Menggantikan versi demo lama. Sekarang benar-benar kirim
   pesan ke server (dgn/tanpa gambar), render balasan asli,
   dan bisa hapus riwayat percakapan.

   Butuh variabel global berikut, di-inject dari chat.blade.php:
   AI_CONVERSATION_ID, AI_SEND_URL, AI_DELETE_URL_TEMPLATE,
   AI_NEW_CHAT_URL, AI_INDEX_URL, AI_CONFIRM_URL_TEMPLATE,
   AI_REJECT_URL_TEMPLATE, AI_CSRF_TOKEN, AI_USER_INITIALS
   ═══════════════════════════════════════════════════════ */

/* ── State lampiran gambar yang belum terkirim ── */
let pendingAttachments = []; // array of File

/* ── Mobile drawer riwayat ── */
function openConvDrawer() {
  document.getElementById('chat-conv-panel')?.classList.add('open');
  showChatOverlay();
}
function closeConvDrawer() {
  document.getElementById('chat-conv-panel')?.classList.remove('open');
  hideChatOverlay();
}
function showChatOverlay() {
  const ov = document.getElementById('chat-overlay');
  if (!ov) return;
  ov.classList.remove('hidden');
  requestAnimationFrame(() => ov.style.opacity = '1');
  document.body.style.overflow = 'hidden';
}
function hideChatOverlay() {
  const ov = document.getElementById('chat-overlay');
  if (!ov) return;
  ov.style.opacity = '0';
  setTimeout(() => { ov.classList.add('hidden'); document.body.style.overflow = ''; }, 200);
}

/* ── Textarea auto-resize ── */
function autoResizeChatInput(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 160) + 'px';
  updateSendBtnState();
}

function updateSendBtnState() {
  const ta = document.getElementById('chat-textarea');
  const sendBtn = document.getElementById('chat-send-btn');
  if (!ta || !sendBtn) return;
  const hasText = ta.value.trim().length > 0;
  sendBtn.classList.toggle('active', hasText || pendingAttachments.length > 0);
}

function handleChatKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendChatMessage();
  }
}

/* ── Filter daftar riwayat percakapan ── */
function filterChatConversations(value) {
  const q = value.trim().toLowerCase();
  document.querySelectorAll('.chat-conv-item').forEach(item => {
    const title = item.querySelector('.chat-conv-title')?.textContent.toLowerCase() || '';
    const snippet = item.querySelector('.chat-conv-snippet')?.textContent.toLowerCase() || '';
    item.style.display = (!q || title.includes(q) || snippet.includes(q)) ? '' : 'none';
  });
}

/* ── Upload gambar: pilih file & preview ── */
function handleChatAttachChange(event) {
  const files = Array.from(event.target.files || []);
  if (!files.length) return;

  files.forEach(file => {
    if (!file.type.startsWith('image/')) return;
    pendingAttachments.push(file);
  });

  renderAttachPreview();
  updateSendBtnState();
  event.target.value = ''; // biar bisa pilih file yang sama lagi kalau dihapus
}

function removeChatAttachment(index) {
  pendingAttachments.splice(index, 1);
  renderAttachPreview();
  updateSendBtnState();
}

function renderAttachPreview() {
  const box = document.getElementById('chat-attach-preview');
  if (!box) return;

  if (!pendingAttachments.length) {
    box.classList.add('hidden');
    box.innerHTML = '';
    return;
  }

  box.classList.remove('hidden');
  box.innerHTML = pendingAttachments.map((file, i) => {
    const url = URL.createObjectURL(file);
    return `
      <div class="chat-attach-thumb">
        <img src="${url}" alt="${file.name}">
        <button type="button" class="chat-attach-remove" onclick="removeChatAttachment(${i})">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>`;
  }).join('');
}

/* ── Kirim pesan (teks + gambar) ke server ── */
function sendChatMessage() {
  const ta = document.getElementById('chat-textarea');
  const list = document.getElementById('chat-messages');
  const sendBtn = document.getElementById('chat-send-btn');
  if (!ta || !list) return;

  const text = ta.value.trim();
  if (!text && pendingAttachments.length === 0) return;

  const hero = document.getElementById('chat-hero');
  if (hero) hero.remove();

  const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  // ── Optimistic render bubble user (pakai object URL lokal utk preview gambar) ──
  const localImageUrls = pendingAttachments.map(f => URL.createObjectURL(f));
  const userRow = document.createElement('div');
  userRow.className = 'chat-msg-row user';
  userRow.innerHTML = `
    <div class="chat-avatar user">${AI_USER_INITIALS || 'ME'}</div>
    <div class="chat-msg-col">
      ${localImageUrls.length ? `<div class="chat-msg-images">${localImageUrls.map(u => `<div class="chat-msg-image"><img src="${u}"></div>`).join('')}</div>` : ''}
      ${text ? `<div class="chat-bubble user"></div>` : ''}
      <div class="chat-meta-row"><span class="chat-meta-time">${time}</span></div>
    </div>`;
  if (text) userRow.querySelector('.chat-bubble').textContent = text;
  list.appendChild(userRow);

  // ── Siapkan payload sebelum reset state ──
  const formData = new FormData();
  formData.append('message', text);
  pendingAttachments.forEach(file => formData.append('images[]', file));

  // ── Reset input ──
  ta.value = '';
  autoResizeChatInput(ta);
  pendingAttachments = [];
  renderAttachPreview();
  if (sendBtn) sendBtn.disabled = true;
  list.scrollTop = list.scrollHeight;

  // ── Typing indicator ──
  const typingRow = document.createElement('div');
  typingRow.className = 'chat-msg-row ai';
  typingRow.id = 'chat-typing-row';
  typingRow.innerHTML = `
    <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
    <div class="chat-msg-col">
      <div class="chat-bubble ai chat-typing"><span></span><span></span><span></span></div>
    </div>`;
  list.appendChild(typingRow);
  list.scrollTop = list.scrollHeight;

  fetch(AI_SEND_URL, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': AI_CSRF_TOKEN, 'Accept': 'text/html' },
    body: formData,
  })
    .then(async (res) => {
      if (!res.ok) throw new Error(await res.text());
      return res.text();
    })
    .then((html) => {
      document.getElementById('chat-typing-row')?.remove();
      list.insertAdjacentHTML('beforeend', html);
      list.scrollTop = list.scrollHeight;
    })
    .catch((err) => {
      console.error('Gagal mengirim pesan AI:', err);
      document.getElementById('chat-typing-row')?.remove();
      list.insertAdjacentHTML('beforeend', `
        <div class="chat-msg-row ai">
          <div class="chat-avatar ai"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="chat-msg-col">
            <div class="chat-bubble ai chat-bubble-error">Gagal mengirim pesan. Coba lagi.</div>
          </div>
        </div>`);
      list.scrollTop = list.scrollHeight;
    })
    .finally(() => {
      if (sendBtn) sendBtn.disabled = false;
    });
}

/* ── Hapus percakapan (dari sidebar atau tombol trash di header) ── */
function deleteAiConversation(id) {
  if (!confirm('Hapus percakapan ini? Semua pesan di dalamnya akan hilang permanen.')) return;

  const url = AI_DELETE_URL_TEMPLATE.replace('__ID__', id);

  fetch(url, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': AI_CSRF_TOKEN, 'Accept': 'application/json' },
  })
    .then((res) => {
      if (!res.ok) throw new Error('Gagal menghapus percakapan.');

      // Hapus item dari sidebar
      document.querySelector(`.chat-conv-item[data-conv-id="${id}"]`)?.remove();

      // Kalau yang dihapus adalah percakapan yang sedang dibuka, pindah ke chat baru/index
      if (Number(id) === Number(AI_CONVERSATION_ID)) {
        window.location.href = AI_INDEX_URL;
        return;
      }
    })
    .catch((err) => {
      console.error(err);
      alert('Gagal menghapus percakapan. Coba lagi.');
    });
}

/* ── Modal koneksi AI ── */
function openAiConnectModal() {
  document.getElementById('ai-connect-modal')?.classList.remove('hidden');
  document.getElementById('ai-connect-modal')?.classList.add('flex');
}
function closeAiConnectModal() {
  document.getElementById('ai-connect-modal')?.classList.add('hidden');
  document.getElementById('ai-connect-modal')?.classList.remove('flex');
}

/* ── Tool-confirm-card actions (usulan data dari AI) ── */
function confirmAiAction(id) {
  postAiActionCard(AI_CONFIRM_URL_TEMPLATE.replace('__ID__', id), id);
}
function rejectAiAction(id) {
  postAiActionCard(AI_REJECT_URL_TEMPLATE.replace('__ID__', id), id);
}
function editAiAction(id) {
  // Placeholder: arahkan ke textarea supaya user bisa tulis ulang permintaannya.
  // Sesuaikan kalau nanti ada UI edit payload yang lebih spesifik.
  document.getElementById('chat-textarea')?.focus();
}
function postAiActionCard(url, id) {
  fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': AI_CSRF_TOKEN, 'Accept': 'text/html' },
  })
    .then(async (res) => {
      if (!res.ok) throw new Error(await res.text());
      return res.text();
    })
    .then((html) => {
      const row = document.getElementById(`ai-action-${id}`);
      if (row) row.outerHTML = html;
    })
    .catch((err) => console.error('Gagal memproses aksi AI:', err));
}

/* ── Copy code block di dalam bubble AI ── */
function copyChatCode(btn) {
  const code = btn.closest('.chat-code')?.querySelector('pre code');
  if (!code) return;
  navigator.clipboard?.writeText(code.textContent || '');
  const original = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> Disalin';
  setTimeout(() => btn.innerHTML = original, 1400);
}

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('chat-messages');
  if (list) list.scrollTop = list.scrollHeight;
});

/* ═══════════════════════════════════════════════════════
   PENTING — BUGFIX: expose ke window
   ───────────────────────────────────────────────────────
   File ini dimuat lewat @vite(...), yang otomatis render
   <script type="module">. Function yang dideklarasikan di
   dalam ES module TIDAK otomatis masuk ke scope global,
   jadi semua atribut onclick="..."/oninput="..."/onchange="..."
   di chat.blade.php & partial-nya (mis. onclick="sendChatMessage()",
   onclick="deleteAiConversation(1)") gagal total dengan error
   "sendChatMessage is not defined" — inilah sebab tombol Kirim
   & Hapus percakapan tidak berfungsi sama sekali sebelumnya.

   Solusinya: attach eksplisit semua handler yang dipanggil dari
   markup ke window, sama seperti yang sudah dilakukan di
   ai-widget.js (window.AiWidget = AiWidget).
   ═══════════════════════════════════════════════════════ */
window.openConvDrawer = openConvDrawer;
window.closeConvDrawer = closeConvDrawer;
window.autoResizeChatInput = autoResizeChatInput;
window.handleChatKeydown = handleChatKeydown;
window.filterChatConversations = filterChatConversations;
window.handleChatAttachChange = handleChatAttachChange;
window.removeChatAttachment = removeChatAttachment;
window.sendChatMessage = sendChatMessage;
window.deleteAiConversation = deleteAiConversation;
window.openAiConnectModal = openAiConnectModal;
window.closeAiConnectModal = closeAiConnectModal;
window.confirmAiAction = confirmAiAction;
window.rejectAiAction = rejectAiAction;
window.editAiAction = editAiAction;
window.copyChatCode = copyChatCode;