/* ═══════════════════════════════════════════════════════
   Netra UI — Wizard Lazy-Step Addon
   Pasangan untuk komponen Blade:
     resources/views/components/wizard/index.blade.php
     resources/views/components/wizard/step.blade.php

   TIDAK mengubah/menimpa assets/js/netra-form-wizard.js bawaan tema.
   File ini cuma "menumpang" di atas event & public API yang sudah
   disediakan tema itu:
     - event  nt-wizard:init         (dipakai utk load step pertama)
     - event  nt-wizard:step-change  (dipakai utk load step tujuan)
     - fungsi window.NetraUI.initWizard(root)  (dipanggil ulang setelah
       Blade component ditambahkan dinamis, kalau perlu)

   ATURAN MODE per-step (bukan flag global):
     <x-wizard.step> TANPA prop `url`  → statis, langsung tampil.
     <x-wizard.step> DENGAN prop `url` → di-fetch sekali saat pertama
                                          kali step itu jadi aktif,
                                          hasilnya di-cache di DOM
                                          (data-step-loaded="true"),
                                          tidak fetch ulang lagi.
   Jadi satu wizard boleh campur step statis & step ajax sekaligus.

   Wajib dimuat SETELAH assets/js/netra-form-wizard.js.
   Import di resources/js/app.js:
     import '../../assets/js/netra-form-wizard';
     import './netra-form-wizard-lazy';
   ═══════════════════════════════════════════════════════ */
(() => {
  function currentStepEl(root) {
    return root.querySelector('.nt-wizard-step:not(.nt-wizard-step-hidden)')
      || root.querySelector('.nt-wizard-step');
  }

  function loadStep(root, step) {
    if (!step) return;
    const url = step.dataset.stepUrl;
    if (!url || step.dataset.stepLoaded === 'true') return;

    const mount = step.querySelector('[data-step-mount]') || step;

    fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
      credentials: 'same-origin',
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.text();
      })
      .then((html) => {
        mount.innerHTML = html;
        step.dataset.stepLoaded = 'true';
        root.dispatchEvent(new CustomEvent('nt-wizard:step-loaded', { detail: { step, url } }));
      })
      .catch((err) => {
        console.error('[ntWizardLazy] Gagal memuat konten step:', err);
        mount.innerHTML = `
          <div class="nt-wizard-step-error">
            <p>Gagal memuat konten step ini.</p>
            <button type="button" class="nt-btn nt-btn-secondary nt-btn-sm" data-step-retry>Coba lagi</button>
          </div>`;
        mount.querySelector('[data-step-retry]')?.addEventListener('click', () => {
          step.dataset.stepLoaded = 'false';
          loadStep(root, step);
        }, { once: true });
      });
  }

  /**
   * Semua step ber-url yang WAJIB (bukan optional) tapi belum selesai
   * di-load. Kalau ini tidak kosong, submit HARUS diblokir — soalnya
   * field di step itu belum ada di DOM sama sekali, jadi validasi core
   * (`validateStep`) tidak akan pernah menganggapnya invalid walau
   * sebenarnya belum diisi (false negative).
   */
  function pendingRequiredSteps(root) {
    return Array.from(root.querySelectorAll('.nt-wizard-step[data-step-url]'))
      .filter((step) => step.dataset.stepLoaded !== 'true' && step.dataset.stepOptional !== 'true');
  }

  function showGuardWarning(root, step) {
    const title = step.dataset.stepTitle || 'step ini';
    let box = root.querySelector('.nt-wizard-guard-alert');
    if (!box) {
      box = document.createElement('div');
      box.className = 'nt-wizard-guard-alert';
      const body = root.querySelector('.nt-wizard-body') || root;
      body.insertBefore(box, body.firstChild);
    }
    box.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Lengkapi step "${title}" terlebih dahulu sebelum menyimpan.`;
    clearTimeout(box._ntTimer);
    box._ntTimer = setTimeout(() => box.remove(), 5000);
  }

  function clearGuardWarning(root) {
    root.querySelector('.nt-wizard-guard-alert')?.remove();
  }

  /**
   * Cegat klik tombol Submit di FASE CAPTURE (sebelum handler klik
   * milik core wizard sempat jalan). Kalau masih ada step wajib yang
   * ber-AJAX & belum ke-load, batalkan submit sepenuhnya — baik yang
   * jalur native <form> (preventDefault mencegah form ke-submit)
   * maupun jalur custom event `nt-wizard:submit` (stopImmediatePropagation
   * mencegah handler klik core, termasuk yang menembak event itu, jalan).
   */
  function wireSubmitGuard(root) {
    root.addEventListener('click', (e) => {
      const submitBtn = e.target.closest('[data-wizard-submit]');
      if (!submitBtn) return;

      const pending = pendingRequiredSteps(root);
      if (pending.length === 0) {
        clearGuardWarning(root);
        return; // aman, biarkan core lanjut submit seperti biasa
      }

      e.preventDefault();
      e.stopImmediatePropagation();

      const target = pending[0];
      const steps = Array.from(root.querySelectorAll('.nt-wizard-step'));
      const idx = steps.indexOf(target);

      if (window.NetraUI && typeof window.NetraUI.wizardGoto === 'function') {
        window.NetraUI.wizardGoto(root, idx);
      }
      loadStep(root, target); // pastikan proses fetch-nya mulai/lanjut
      showGuardWarning(root, target);
    }, true); // capture
  }

  function wireWizard(root) {
    if (root._ntWizardLazyWired) return;
    root._ntWizardLazyWired = true;

    root.addEventListener('nt-wizard:init', () => loadStep(root, currentStepEl(root)));
    root.addEventListener('nt-wizard:step-change', (e) => {
      clearGuardWarning(root);
      loadStep(root, e.detail.step);
    });

    wireSubmitGuard(root);

    // Jaga-jaga: kalau core wizard sudah selesai init duluan sebelum
    // listener di atas terpasang (tergantung urutan script), tetap
    // muat step yang sedang aktif sekarang juga.
    loadStep(root, currentStepEl(root));
  }

  function init(rootDoc) {
    (rootDoc || document).querySelectorAll('.nt-wizard').forEach(wireWizard);
  }

  document.addEventListener('DOMContentLoaded', () => init(document));

  window.NetraUI = window.NetraUI || {};
  window.NetraUI.initWizardLazy = init;
})();
