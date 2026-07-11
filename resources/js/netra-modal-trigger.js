/* ═══════════════════════════════════════════════════════
   Netra UI — Modal Trigger Bridge
   Pasangan untuk komponen Blade:
     resources/views/components/modal/button.blade.php
     resources/views/components/modal/content.blade.php

   Tugas file ini HANYA:
     1. Delegasi klik tombol trigger (termasuk tombol yang lahir
        dari konten yang kamu suntik sendiri lewat AJAX — nested).
     2. Menentukan ukuran modal & status nested secara otomatis.
     3. Menembakkan event `nt-modal:show` SEBELUM modal ditampilkan,
        supaya AJAX kamu sendiri bisa nimbrung di titik yang tepat.
     4. Membuka modal lewat ntModal bawaan (netra-modal.js).

   TIDAK ADA fetch/cache di sini — itu sepenuhnya urusanmu.

   Wajib dimuat SETELAH netra-modal.js (butuh window.ntModal).
   Import di resources/js/app.js:
     import './netra-modal-trigger';
   ═══════════════════════════════════════════════════════ */
(() => {
    if (!window.ntModal) {
        console.warn("[ntModalTrigger] ntModal (netra-modal.js) belum dimuat.");
        return;
    }

    /** Hitung berapa modal lain yang sedang terbuka (untuk deteksi nested) */
    function openCount(excludeId) {
        return [
            ...document.querySelectorAll(".nt-modal-backdrop.nt-modal-open"),
        ].filter((el) => el.id !== excludeId).length;
    }

    function setDialogSize(backdrop, size) {
        if (!size) return;
        const dialog = backdrop.querySelector("[data-nt-modal-dialog]");
        if (!dialog) return;
        dialog.className = dialog.className.replace(
            /\bnt-modal-(xs|sm|md|lg|xl|full)\b/,
            `nt-modal-${size}`,
        );
    }

    function applyNestedState(backdrop, isNested, depth) {
        backdrop.classList.toggle("nt-modal-nested", isNested);
        // Nesting lebih dari 1 level: naikkan z-index bertahap supaya urutan
        // tumpukan tetap benar. CSS bawaan (.nt-modal-nested) sudah cukup utk 1 level.
        backdrop.style.zIndex = depth > 1 ? String(1000 + depth * 50) : "";
    }

    function handleTriggerClick(btn) {
        const target = btn.dataset.ntModalTarget; // "modal-sm" → id dialog
        if (!target) return;

        const dialog = document.getElementById(target);
        if (!dialog) {
            console.warn(
                `[ntModalTrigger] Dialog id="${target}" tidak ditemukan.`,
            );
            return;
        }

        const backdrop = dialog.closest(".nt-modal-backdrop");
        if (!backdrop) {
            console.warn(
                `[ntModalTrigger] Backdrop untuk "${target}" tidak ditemukan.`,
            );
            return;
        }

        const size = btn.dataset.ntModalSize || null;
        const forceNested = btn.dataset.ntModalNested === "1";
        const depth = openCount(backdrop.id);
        const isNested = forceNested || depth > 0;

        setDialogSize(backdrop, size);
        applyNestedState(backdrop, isNested, depth);

        backdrop.dispatchEvent(
            new CustomEvent("nt-modal:show", {
                detail: { target, isNested, depth, trigger: btn },
            }),
        );

        window.ntModal.open(backdrop.id); // ⚠️ lihat catatan di bawah
    }

    // Delegasi klik: mencakup tombol yang lahir dari konten yang kamu
    // suntik sendiri lewat AJAX (skenario nested).
    document.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-nt-modal-btn]");
        if (btn) {
            e.preventDefault();
            handleTriggerClick(btn);
            return;
        }

        // Opsional: tombol tutup generik di dalam konten AJAX-mu, tanpa perlu
        // tahu id modalnya atau menulis inline onclick="ntModal.close(...)".
        // Cukup: <button data-nt-modal-close>Tutup</button>
        const closeBtn = e.target.closest("[data-nt-modal-close]");
        if (closeBtn) {
            const backdrop = closeBtn.closest(".nt-modal-backdrop");
            if (backdrop) window.ntModal.close(backdrop.id);
        }

        function wireModalTitle(backdrop) {
            const titleEl = backdrop.querySelector("[data-nt-modal-title]");
            if (titleEl) titleEl.id = `${backdrop.id}-title`;
        }

        // wiring awal utk modal statis yang sudah ada di slot shell sejak load
        document.querySelectorAll(".nt-modal-backdrop").forEach(wireModalTitle);

        // ekspos biar bisa dipanggil manual setelah kamu selesai
        // document.querySelector('#modal-sm [data-nt-modal-dialog]').innerHTML = html;
        window.ntModal.wireTitle = wireModalTitle;
    });
})();
