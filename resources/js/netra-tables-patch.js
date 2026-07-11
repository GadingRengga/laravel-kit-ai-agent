/* ═══════════════════════════════════════════════════════════════════════
   netra-tables-patch.js
   Load file ini SETELAH netra-tables.js (urutan boleh sebelum atau sesudah
   livedom.js — semua listener di sini pakai document-level delegation atau
   capture phase, jadi tidak bergantung urutan load).
   ═══════════════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* ── 1. Konfirmasi universal (data-nt-confirm) ──
       Bekerja untuk href, wire:click, live-click, form submit — apapun,
       karena dipasang di CAPTURE phase: selalu dievaluasi lebih dulu
       sebelum listener lain (LiveDomJS/Livewire/dst) sempat jalan. */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-nt-confirm]');
        if (!btn) return;
        if (!confirm(btn.dataset.ntConfirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    /* ── 2. Row-click delegation (data-row-href, dari <x-table.row href="...">) ──
       Klik di manapun dalam baris akan navigasi, KECUALI di checkbox, area
       aksi, tombol live-click, atau elemen yang minta konfirmasi. */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.col-check, .nt-cell-actions, [live-click], [data-nt-confirm], .nt-tree-toggle')) {
            return;
        }
        const tr = e.target.closest('[data-row-href]');
        if (tr) window.location = tr.dataset.rowHref;
    });

    /* ── 3. stopPropagation checkbox terpusat ──
       Ganti dari inline onclick per <td> jadi satu listener capture-phase.
       Mencegah klik checkbox ikut memicu row-click di atas. */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.col-check')) {
            e.stopPropagation();
        }
    }, true);

    /* ── 4. Hook live-dom:afterUpdate → basic table (mode="client") ──
       initNetra(e.target) bawaan kamu sudah cover Tree/Collapse/Editable/
       Gantt (listener delegated di elemen yang tidak ikut hilang saat swap).
       Basic table beda: attribute [data-nt-basic-table] ada di WRAPPER,
       sedangkan live-target biasanya diarahkan presisi ke <tbody> yang ada
       DI DALAM wrapper. querySelectorAll (dipakai initBasicTable) mencari ke
       BAWAH jadi tidak ketemu wrapper yang ada di ATAS e.target — makanya
       butuh closest() tambahan di sini. */
    document.addEventListener('live-dom:afterUpdate', function (e) {
        const wrapper = e.target.closest ? e.target.closest('[data-nt-basic-table]') : null;
        if (wrapper && wrapper._ntBasicRefresh) {
            wrapper._ntBasicRefresh();
        }
    });
})();


/* ═══════════════════════════════════════════════════════════════════════
   Contoh callback ntTreeBeforeLoad untuk lazy-load tree table.
   Pasang di JS aplikasi kamu (bukan wajib dari file ini), lalu rujuk lewat
   live-callback-before="ntTreeBeforeLoad" di <x-table.tree-toggle>.

   Fungsi: cegah LiveDomJS fetch ulang children yang sudah pernah di-load —
   klik kedua dst cukup ditangani toggleNode() bawaan Netra (show/hide saja).
   ═══════════════════════════════════════════════════════════════════════ */
function ntTreeBeforeLoad(el) {
    if (el.dataset.loaded === 'true') {
        return false; // cancel AJAX, biarkan toggleNode() bawaan yang show/hide
    }
    el.dataset.loaded = 'true';
    return true;
}
window.ntTreeBeforeLoad = ntTreeBeforeLoad;