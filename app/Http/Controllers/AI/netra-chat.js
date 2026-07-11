/* ═══════════════════════════════════════════════════════
   Netra UI — AI Chat JS
   Layout switch, kirim pesan (demo), auto-resize textarea,
   drawer mobile untuk conversation & side panel.
   ═══════════════════════════════════════════════════════ */

/* ── Layout switch ── */
function setChatLayout(mode) {
  const shell = document.getElementById('chat-shell');
  if (!shell) return;
  shell.setAttribute('data-layout', mode);
  document.querySelectorAll('.chat-layout-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.layout === mode);
  });
  try { localStorage.setItem('nt-chat-layout', mode); } catch (e) { }
}

(function initChatLayout() {
  let saved = 'classic';
  try { saved = localStorage.getItem('nt-chat-layout') || 'classic'; } catch (e) { }
  document.addEventListener('DOMContentLoaded', () => setChatLayout(saved));
})();

/* ── Mobile drawers ── */
function openConvDrawer() {
  document.getElementById('chat-conv-panel')?.classList.add('open');
  showChatOverlay();
}
function closeConvDrawer() {
  document.getElementById('chat-conv-panel')?.classList.remove('open');
  hideChatOverlay();
}
function openSidePanel() {
  document.getElementById('chat-side-panel')?.classList.add('open');
  showChatOverlay();
}
function closeSidePanel() {
  document.getElementById('chat-side-panel')?.classList.remove('open');
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
  document.getElementById('chat-conv-panel')?.classList.remove('open');
  document.getElementById('chat-side-panel')?.classList.remove('open');
}

/* ── Side panel tabs ── */
function setSideTab(tab) {
  document.querySelectorAll('.chat-side-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
  document.querySelectorAll('.chat-side-pane').forEach(p => p.classList.toggle('hidden', p.dataset.pane !== tab));
}

/* ── Conversation list selection ── */
function selectConversation(el) {
  document.querySelectorAll('.chat-conv-item').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  if (window.innerWidth < 1024) closeConvDrawer();
}

/* ── Textarea auto-resize ── */
function autoResizeChatInput(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 160) + 'px';
  const sendBtn = document.getElementById('chat-send-btn');
  if (sendBtn) sendBtn.classList.toggle('active', el.value.trim().length > 0);
}

/* ── Fill input from suggestion chip ── */
function fillChatPrompt(text) {
  const ta = document.getElementById('chat-textarea');
  if (!ta) return;
  ta.value = text;
  autoResizeChatInput(ta);
  ta.focus();
}

/* ── Send message (demo, no backend) ── */
function handleChatKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendChatMessage();
  }
}

function sendChatMessage() {
  const ta = document.getElementById('chat-textarea');
  const list = document.getElementById('chat-messages');
  if (!ta || !list) return;
  const text = ta.value.trim();
  if (!text) return;

  // Remove hero welcome if present
  const hero = document.getElementById('chat-hero');
  if (hero) hero.remove();

  const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  const userRow = document.createElement('div');
  userRow.className = 'chat-msg-row user';
  userRow.innerHTML = `
    <div class="chat-avatar user">GD</div>
    <div class="chat-msg-col">
      <div class="chat-bubble user"></div>
      <div class="chat-meta-row"><span class="chat-meta-time">${time}</span></div>
    </div>`;
  userRow.querySelector('.chat-bubble').textContent = text;
  list.appendChild(userRow);

  ta.value = '';
  autoResizeChatInput(ta);
  list.scrollTop = list.scrollHeight;

  // Typing indicator
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

  setTimeout(() => {
    typingRow.remove();
    const aiRow = document.createElement('div');
    aiRow.className = 'chat-msg-row ai';
    const aiTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    aiRow.innerHTML = `
      <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
      <div class="chat-msg-col">
        <div class="chat-bubble ai">Ini adalah tanggapan contoh dari asisten AI. Hubungkan bagian ini ke API/back-end Anda untuk balasan yang sesungguhnya.</div>
        <div class="chat-meta-row">
          <span class="chat-meta-time">${aiTime}</span>
          <div class="chat-msg-actions">
            <button class="chat-action-btn" title="Salin"><i class="fa-regular fa-copy"></i></button>
            <button class="chat-action-btn" title="Suka"><i class="fa-regular fa-thumbs-up"></i></button>
            <button class="chat-action-btn" title="Tidak suka"><i class="fa-regular fa-thumbs-down"></i></button>
            <button class="chat-action-btn" title="Buat ulang"><i class="fa-solid fa-rotate-right"></i></button>
          </div>
        </div>
      </div>`;
    list.appendChild(aiRow);
    list.scrollTop = list.scrollHeight;
  }, 1100);
}

/* ── Copy code block ── */
function copyChatCode(btn) {
  const code = btn.closest('.chat-code')?.querySelector('pre code');
  if (!code) return;
  navigator.clipboard?.writeText(code.textContent || '');
  const original = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> Disalin';
  setTimeout(() => btn.innerHTML = original, 1400);
}

/* ── Filter conversation list ── */
function filterChatConversations(value) {
  const q = value.trim().toLowerCase();
  document.querySelectorAll('.chat-conv-item').forEach(item => {
    const title = item.querySelector('.chat-conv-title')?.textContent.toLowerCase() || '';
    const snippet = item.querySelector('.chat-conv-snippet')?.textContent.toLowerCase() || '';
    item.style.display = (!q || title.includes(q) || snippet.includes(q)) ? '' : 'none';
  });
}

/* ── New chat ── */
function startNewChat() {
  const list = document.getElementById('chat-messages');
  if (!list) return;
  list.innerHTML = document.getElementById('chat-hero-template').innerHTML;
  document.querySelectorAll('.chat-conv-item').forEach(i => i.classList.remove('active'));
  if (window.innerWidth < 1024) closeConvDrawer();
}
