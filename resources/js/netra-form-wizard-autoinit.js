/* ═══════════════════════════════════════════════════════
   Netra UI — Wizard Auto-Reinit untuk LiveDomJs

   Kenapa perlu: netra-form-wizard.js hanya initWizard() SEKALI saat
   DOMContentLoaded. Kalau LiveDomJs mengganti sebagian DOM setelah itu
   (live-target, atau navigasi live-spa-region), <x-wizard> yang baru
   disuntikkan TIDAK otomatis punya head/foot/klik Next-Prev — perlu
   di-init ulang.

   LiveDomJs menembak event `live-dom:afterUpdate` ke document setelah
   SPA navigation MAUPUN update DOM biasa selesai — jadi cukup dengar
   event itu sekali, tidak perlu MutationObserver/tebak-tebak lagi.

   initWizard() & initWizardLazy() sudah idempotent (ada guard
   `_ntWizard` / `_ntWizardLazyWired` di dalamnya), jadi aman dipanggil
   berkali-kali — wizard yang sudah aktif tidak di-reset / tidak
   kehilangan step yang sedang berjalan.

   Wajib dimuat PALING TERAKHIR, setelah:
     netra-form-wizard.js
     netra-form-wizard-lazy.js   (kalau dipakai)
   Import di resources/js/app.js:
     import './netra-form-wizard-autoinit';
   ═══════════════════════════════════════════════════════ */
(() => {
  function rescan() {
    if (window.NetraUI && typeof window.NetraUI.initWizard === 'function') {
      window.NetraUI.initWizard(document);
    }
    if (window.NetraUI && typeof window.NetraUI.initWizardLazy === 'function') {
      window.NetraUI.initWizardLazy(document);
    }
  }

  // Muat pertama kali (halaman biasa, sebelum ada SPA navigation apapun)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', rescan);
  } else {
    rescan();
  }

  // Setiap LiveDomJs selesai memperbarui DOM (SPA maupun live-target biasa)
  document.addEventListener('live-dom:afterUpdate', rescan);
})();
