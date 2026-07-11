/* ═══════════════════════════════════════════════════════
   Netra UI — Modal Manager (ntModal)  (v5, modular split)
   Membutuhkan netra-base.js. Aman dimuat tanpa netra-forms.js
   (window.NetraUI di-guard otomatis).
   ═══════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════
   NetraUI.Modal  —  Modal Manager

   API:
     ntModal.open(id)               — buka modal
     ntModal.close(id)              — tutup modal
     ntModal.closeAll()             — tutup semua
     ntModal.closeOnBackdrop(e, id) — tutup saat klik backdrop

   Keyboard: Esc tutup modal teratas.
   Auto-init: terbaca otomatis saat DOMContentLoaded.
   Blade: include netra-ui.js sekali di layout utama.
     @push('scripts') <script src="{{ asset('js/netra-ui.js') }}"> @endpush
   ═══════════════════════════════════════════════════════ */
const ntModal = (() => {
  const stack = [];

  function open(id) {
    const backdrop = document.getElementById(id);
    if (!backdrop) { console.warn('[ntModal] Elemen tidak ditemukan:', id); return; }
    backdrop.style.display = '';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        backdrop.classList.add('nt-modal-open');
        document.body.style.overflow = 'hidden';
        if (!stack.includes(id)) stack.push(id);
        setTimeout(() => {
          const focusable = backdrop.querySelector(
            'input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [href]'
          );
          if (focusable) focusable.focus({ preventScroll: true });
        }, 260);
      });
    });
  }

  function close(id) {
    const backdrop = document.getElementById(id);
    if (!backdrop) return;
    backdrop.classList.remove('nt-modal-open');
    setTimeout(() => {
      if (!backdrop.classList.contains('nt-modal-open')) {
        backdrop.style.display = 'none';
      }
    }, 240);
    const idx = stack.indexOf(id);
    if (idx > -1) stack.splice(idx, 1);
    if (stack.length === 0) {
      setTimeout(() => {
        if (stack.length === 0) document.body.style.overflow = '';
      }, 240);
    }
  }

  function closeAll() {
    [...stack].reverse().forEach(id => close(id));
  }

  function closeOnBackdrop(event, id) {
    if (event.target === event.currentTarget) close(id);
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && stack.length > 0) {
      close(stack[stack.length - 1]);
    }
  });

  return { open, close, closeAll, closeOnBackdrop };
})();

window.ntModal = ntModal;

if (!window.NetraUI) window.NetraUI = {};
window.NetraUI.modal = ntModal;
