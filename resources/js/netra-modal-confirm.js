/* ═══════════════════════════════════════════════════════
   Netra UI — ntConfirm helper
   Pasangan untuk cangkang #app-modal-confirm di
   resources/views/layouts/partials/modal-shells.blade.php

   Wajib dimuat SETELAH netra-modal.js & netra-modal-trigger.js.
   Import di resources/js/app.js:
     import './netra-modal-confirm';
   ═══════════════════════════════════════════════════════ */
(() => {
  if (!window.ntModal) {
    console.warn('[ntConfirm] ntModal (netra-modal.js) belum dimuat.');
    return;
  }

  const MODAL_ID = 'app-modal-confirm';

  /**
   * ntConfirm({ title, message, confirmText, cancelText, danger, onConfirm, onCancel })
   *
   * Bisa dipanggil dari mana saja — termasuk dari dalam #app-modal yang
   * isinya sedang berasal dari AJAX-mu sendiri (skenario nested otomatis
   * tertangani karena #app-modal-confirm sudah ditandai `nested` di Blade).
   *
   * Contoh:
   *   ntConfirm({
   *     title: 'Hapus user?',
   *     message: `Data "${user.name}" akan dihapus permanen.`,
   *     confirmText: 'Ya, hapus',
   *     danger: true,
   *     onConfirm: () => fetch(`/users/${user.id}`, { method: 'DELETE', ... }),
   *   });
   */
  function ntConfirm({
    title = 'Konfirmasi',
    message = 'Apakah kamu yakin?',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    danger = false,
    onConfirm = null,
    onCancel = null,
  } = {}) {
    const backdrop = document.getElementById(MODAL_ID);
    if (!backdrop) {
      console.warn(`[ntConfirm] Cangkang #${MODAL_ID} tidak ditemukan. Sudah di-@include di footer?`);
      return;
    }

    backdrop.querySelector('[data-nt-modal-title]').textContent = title;
    backdrop.querySelector('[data-app-confirm-message]').textContent = message;
    backdrop.querySelector('[data-app-confirm-cancel]').textContent = cancelText;

    const okBtn = backdrop.querySelector('[data-app-confirm-ok]');
    okBtn.textContent = confirmText;
    okBtn.className = `nt-btn ${danger ? 'nt-btn-danger' : 'nt-btn-primary'}`;

    // Ganti listener tiap kali dipanggil (supaya callback lama tidak nyangkut)
    const freshOk = okBtn.cloneNode(true);
    okBtn.replaceWith(freshOk);
    freshOk.addEventListener('click', () => {
      window.ntModal.close(MODAL_ID);
      if (typeof onConfirm === 'function') onConfirm();
    });

    // Tombol batal pakai data-nt-modal-close (sudah otomatis menutup modal
    // lewat delegasi di netra-modal-trigger.js), di sini cukup tambahkan
    // callback onCancel opsional di atasnya, dengan cara yang sama
    // (clone+replace) supaya tidak numpuk listener tiap kali ntConfirm() dipanggil.
    const cancelBtn = backdrop.querySelector('[data-app-confirm-cancel]');
    const freshCancel = cancelBtn.cloneNode(true);
    cancelBtn.replaceWith(freshCancel);
    if (typeof onCancel === 'function') {
      freshCancel.addEventListener('click', onCancel, { once: true });
    }

    window.ntModal.open(MODAL_ID);
  }

  window.ntConfirm = ntConfirm;
})();
