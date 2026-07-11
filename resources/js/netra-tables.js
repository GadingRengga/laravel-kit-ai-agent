/* ═══════════════════════════════════════════════════════
   Netra UI — Tables JS (initTable, TreeTable, CollapseTable, EditableTable, Gantt, BasicTable)  (v5, modular split)
   Membutuhkan netra-base.js. Aman dimuat tanpa netra-forms.js
   (window.NetraUI di-guard otomatis).
   ═══════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════
   NetraUI.initTable  —  Sortable + Selectable + Search + Pagination
   Dipindahkan & dikonversi dari advance-table.html (inline <script>).

   STATIC HTML MODE — tidak ada innerHTML injection.
   JS hanya membaca data-* attributes dan memanipulasi class/visibility.

   Usage (HTML):
     <div data-nt-table="sortable-selectable"
          data-search="true"
          data-page-size="5">

       <div class="nt-table-toolbar" data-nt-table-toolbar>
         ...
         <div class="nt-table-search" data-nt-table-search>
           <i class="fa-solid fa-magnifying-glass"></i>
           <input type="text" placeholder="Cari…" />
         </div>
       </div>

       <table class="nt-table">
         <thead>
           <tr>
             <th data-nt-col-check><input type="checkbox" /></th>
             <!-- data-sort="<key>" → aktifkan sorting pada kolom ini -->
             <th class="nt-th-sort" data-sort="nama">Nama ...</th>
           </tr>
         </thead>
         <tbody>
           <!-- data-<key>="<nilai>" pada <tr> dipakai untuk sort & search -->
           <tr data-nama="Andi" data-departemen="Eng" ...>
             <td class="col-check"><input type="checkbox" /></td>
             ...
           </tr>
         </tbody>
       </table>

       <div class="nt-table-footer" data-nt-table-footer>
         <span data-nt-table-info>Menampilkan 1–5 dari N entri</span>
         <div class="nt-pagination" data-nt-table-pagination>
           <button class="nt-page-btn" data-page="prev">‹</button>
           <button class="nt-page-btn active" data-page="1">1</button>
           <button class="nt-page-btn" data-page="next">›</button>
         </div>
       </div>
     </div>

   JS API:
     window.NetraUI.initTable(root);  // re-init untuk dynamic content

   Catatan penting:
   - JS TIDAK pernah mengubah innerHTML tabel.
   - Sort bekerja dengan memindahkan <tr> elemen (DOM reorder).
   - Search bekerja dengan toggle display none/'' pada <tr>.
   - Pagination bekerja dengan merender ulang tombol page di [data-nt-table-pagination].
   ═══════════════════════════════════════════════════════ */

function initTable(root) {
  root = root || document;

  root.querySelectorAll('[data-nt-table]').forEach(container => {
    if (container._ntTableInit) return; // guard — skip double-init
    container._ntTableInit = true;

    const pageSize    = parseInt(container.dataset.pageSize  || '10');
    const searchEnabled = container.dataset.search !== 'false';

    const table       = container.querySelector('table');
    if (!table) return;

    const tbody       = table.querySelector('tbody');
    const searchWrap  = container.querySelector('[data-nt-table-search]');
    const searchInput = searchWrap ? searchWrap.querySelector('input') : null;
    const infoEl      = container.querySelector('[data-nt-table-info]');
    const paginEl     = container.querySelector('[data-nt-table-pagination]');
    const checkAll    = container.querySelector('[data-nt-col-check] input[type="checkbox"]');

    /* ── State ── */
    let currentPage   = 1;
    let sortKey       = null;
    let sortDir       = 'asc';   // 'asc' | 'desc'
    let filterText    = '';

    /* ── Helpers ── */
    function allRows() {
      return Array.from(tbody.querySelectorAll('tr'));
    }

    function visibleRows() {
      return allRows().filter(tr => tr.style.display !== 'none');
    }

    /* ── Filter / Search ── */
    function applyFilter() {
      const q = filterText.toLowerCase().trim();
      allRows().forEach(tr => {
        if (!q) { tr.dataset.ntHidden = ''; return; }
        // Cek semua data-* dan teks sel
        const haystack = Object.values(tr.dataset).join(' ').toLowerCase()
          + ' ' + tr.textContent.toLowerCase();
        tr.dataset.ntHidden = haystack.includes(q) ? '' : '1';
      });
      currentPage = 1;
      applyPagination();
    }

    /* ── Sort ── */
    function applySort() {
      if (!sortKey) return;
      const rows = allRows();
      rows.sort((a, b) => {
        const av = (a.dataset[sortKey] || '').toLowerCase();
        const bv = (b.dataset[sortKey] || '').toLowerCase();
        // Numeric sort jika kedua value adalah angka
        const an = parseFloat(av), bn = parseFloat(bv);
        const numeric = !isNaN(an) && !isNaN(bn);
        if (numeric) return sortDir === 'asc' ? an - bn : bn - an;
        return sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
      });
      rows.forEach(tr => tbody.appendChild(tr)); // DOM reorder, no innerHTML
      applyPagination();
    }

    /* ── Pagination ── */
    function applyPagination() {
      const visible = allRows().filter(tr => !tr.dataset.ntHidden);
      const total   = visible.length;
      const pages   = Math.max(1, Math.ceil(total / pageSize));
      if (currentPage > pages) currentPage = pages;

      visible.forEach((tr, i) => {
        const inPage = i >= (currentPage - 1) * pageSize && i < currentPage * pageSize;
        tr.style.display = inPage ? '' : 'none';
      });

      // Info text
      if (infoEl) {
        const from = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const to   = Math.min(currentPage * pageSize, total);
        infoEl.textContent = `Menampilkan ${from}–${to} dari ${total} entri`;
      }

      // Pagination buttons
      if (paginEl) {
        paginEl.innerHTML = '';

        const prevBtn = document.createElement('button');
        prevBtn.className = 'nt-page-btn';
        prevBtn.dataset.page = 'prev';
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-[10px]"></i>';
        if (currentPage === 1) prevBtn.disabled = true;
        prevBtn.addEventListener('click', () => { currentPage--; applyPagination(); });
        paginEl.appendChild(prevBtn);

        for (let p = 1; p <= pages; p++) {
          const btn = document.createElement('button');
          btn.className = 'nt-page-btn' + (p === currentPage ? ' active' : '');
          btn.dataset.page = p;
          btn.textContent  = p;
          btn.addEventListener('click', () => { currentPage = p; applyPagination(); });
          paginEl.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.className = 'nt-page-btn';
        nextBtn.dataset.page = 'next';
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-[10px]"></i>';
        if (currentPage === pages) nextBtn.disabled = true;
        nextBtn.addEventListener('click', () => { currentPage++; applyPagination(); });
        paginEl.appendChild(nextBtn);
      }
    }

    /* ── Sort headers ── */
    container.querySelectorAll('th[data-sort]').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        if (sortKey === key) {
          sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          sortKey = key;
          sortDir = 'asc';
        }
        // Update visual state
        container.querySelectorAll('th.nt-th-sort').forEach(h => {
          h.classList.remove('asc', 'desc');
        });
        th.classList.add(sortDir);
        applySort();
      });
    });

    /* ── Search input ── */
    if (searchEnabled && searchInput) {
      searchInput.addEventListener('input', () => {
        filterText = searchInput.value;
        applyFilter();
      });
    }

    /* ── Select all checkbox ── */
    if (checkAll) {
      checkAll.addEventListener('change', () => {
        const checked = checkAll.checked;
        visibleRows().forEach(tr => {
          const cb = tr.querySelector('input[type="checkbox"]');
          if (cb) cb.checked = checked;
          tr.classList.toggle('nt-row-selected', checked);
        });
      });

      // Row-level checkboxes
      tbody.addEventListener('change', e => {
        if (e.target.type !== 'checkbox') return;
        const tr = e.target.closest('tr');
        if (tr) tr.classList.toggle('nt-row-selected', e.target.checked);
        // Sync check-all state
        const rowCbs = visibleRows().map(r => r.querySelector('input[type="checkbox"]')).filter(Boolean);
        checkAll.checked        = rowCbs.length > 0 && rowCbs.every(c => c.checked);
        checkAll.indeterminate  = rowCbs.some(c => c.checked) && !checkAll.checked;
      });
    }

    /* ── Initial render ── */
    applyFilter(); // covers pagination init too
  });
}

/* ── Auto-init on DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
  initTable(document);
});

/* ── Export ke public API ── */
if (!window.NetraUI) window.NetraUI = {};
window.NetraUI.initTable = initTable;

/* ═══════════════════════════════════════════════════════
   NetraUI.initTreeTable  —  Tree / Hierarki Table
   (dipindahkan dari premium-table.html)

   STATIC HTML MODE: JS tidak me-render baris, hanya
   membaca data-* attribute dan toggle visibility.

   Usage (HTML):
     <table data-nt-tree-table>
       <tbody>
         <!-- Level 1 (root) -->
         <tr class="nt-tree-l1 nt-tree-node"
             data-node-id="div-tech">
           <td>
             <div class="flex items-center" style="padding-left:0;gap:4px">
               <button class="nt-tree-toggle" data-node-id="div-tech">
                 <i class="fa-solid fa-minus"></i>
               </button>
               <i class="fa-solid fa-folder-open text-[12px]" style="color:#4F46E5"></i>
               <span>Divisi Teknologi</span>
             </div>
           </td>
           ...
         </tr>
         <!-- Level 2 (child dari div-tech) -->
         <tr class="nt-tree-l2 nt-tree-node nt-tree-child"
             data-node-id="tm-eng"
             data-parent="div-tech">
           <td>
             <div class="flex items-center" style="padding-left:24px;gap:4px">
               <span class="nt-tree-line"></span>
               <button class="nt-tree-toggle" data-node-id="tm-eng">
                 <i class="fa-solid fa-minus"></i>
               </button>
               ...
             </div>
           </td>
         </tr>
         <!-- Level 3 (leaf) -->
         <tr class="nt-tree-l3 nt-tree-node nt-tree-child"
             data-node-id="mb-fe"
             data-parent="tm-eng">
           ...
         </tr>
       </tbody>
     </table>

     <!-- Expand/Collapse All buttons -->
     <button data-nt-tree-expand-all data-target="[data-nt-tree-table]">Expand All</button>
     <button data-nt-tree-collapse-all data-target="[data-nt-tree-table]">Collapse</button>

   data-* attributes pada <tr>:
     data-node-id   — ID unik node
     data-parent    — ID parent node (opsional, hanya pada child)

   data-* attributes pada .nt-tree-toggle <button>:
     data-node-id   — sama dengan row, untuk identifikasi toggle

   JS API:
     window.NetraUI.initTreeTable(root); // re-init untuk dynamic content
   ═══════════════════════════════════════════════════════ */

function initTreeTable(root) {
  root = root || document;

  root.querySelectorAll('[data-nt-tree-table]').forEach(table => {
    if (table._ntTreeInit) return;
    table._ntTreeInit = true;

    /* ── Toggle satu node ── */
    function toggleNode(nodeId) {
      const btn = table.querySelector(`.nt-tree-toggle[data-node-id="${nodeId}"]`);
      if (!btn) return;
      const icon = btn.querySelector('i');
      const isOpen = icon && icon.classList.contains('fa-minus');

      // Toggle icon
      if (icon) icon.className = isOpen ? 'fa-solid fa-plus' : 'fa-solid fa-minus';

      // Sembunyikan / tampilkan direct children
      table.querySelectorAll(`tr[data-parent="${nodeId}"]`).forEach(row => {
        if (isOpen) {
          row.classList.add('hidden');
          // Collapse recursive grandchildren
          const childId = row.dataset.nodeId;
          if (childId) collapseSubtree(childId);
        } else {
          row.classList.remove('hidden');
        }
      });
    }

    /* ── Collapse semua keturunan node ── */
    function collapseSubtree(nodeId) {
      table.querySelectorAll(`tr[data-parent="${nodeId}"]`).forEach(row => {
        row.classList.add('hidden');
        const childId = row.dataset.nodeId;
        if (childId) {
          const childBtn = table.querySelector(`.nt-tree-toggle[data-node-id="${childId}"]`);
          const childIcon = childBtn && childBtn.querySelector('i');
          if (childIcon) childIcon.className = 'fa-solid fa-plus';
          collapseSubtree(childId);
        }
      });
    }

    /* ── Event listener pada semua toggle button ── */
    table.addEventListener('click', e => {
      const btn = e.target.closest('.nt-tree-toggle');
      if (!btn) return;
      e.stopPropagation();
      const nodeId = btn.dataset.nodeId;
      if (nodeId) toggleNode(nodeId);
    });

    /* ── Public: expose expand/collapse all via data-attribute buttons ── */
    table._ntTreeExpandAll = function () {
      table.querySelectorAll('.nt-tree-child').forEach(r => r.classList.remove('hidden'));
      table.querySelectorAll('.nt-tree-toggle i').forEach(i => i.className = 'fa-solid fa-minus');
    };
    table._ntTreeCollapseAll = function () {
      table.querySelectorAll('.nt-tree-child').forEach(r => r.classList.add('hidden'));
      table.querySelectorAll('.nt-tree-toggle i').forEach(i => i.className = 'fa-solid fa-plus');
    };
  });

  /* ── Wire expand/collapse all buttons ── */
  root.querySelectorAll('[data-nt-tree-expand-all]').forEach(btn => {
    if (btn._ntTreeWired) return;
    btn._ntTreeWired = true;
    btn.addEventListener('click', () => {
      const target = btn.dataset.target
        ? root.querySelector(btn.dataset.target)
        : root.querySelector('[data-nt-tree-table]');
      if (target && target._ntTreeExpandAll) target._ntTreeExpandAll();
    });
  });

  root.querySelectorAll('[data-nt-tree-collapse-all]').forEach(btn => {
    if (btn._ntTreeWired) return;
    btn._ntTreeWired = true;
    btn.addEventListener('click', () => {
      const target = btn.dataset.target
        ? root.querySelector(btn.dataset.target)
        : root.querySelector('[data-nt-tree-table]');
      if (target && target._ntTreeCollapseAll) target._ntTreeCollapseAll();
    });
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initCollapseTable  —  Accordion Row Detail
   (dipindahkan dari premium-table.html)

   STATIC HTML MODE: JS tidak me-render detail row,
   hanya toggle class .open pada .nt-detail-inner dan
   .nt-expand-btn.

   Usage (HTML):
     <table data-nt-collapse-table>
       <tbody>
         <!-- Main row: klik baris atau tombol expand -->
         <tr class="cursor-pointer"
             data-nt-collapse-row
             data-target="detail-row-0">
           <td>
             <button class="nt-expand-btn"
                     data-nt-expand-btn
                     data-target="detail-row-0">
               <i class="fa-solid fa-chevron-right"></i>
             </button>
           </td>
           <td>...</td>
         </tr>
         <!-- Detail row: hidden by default via max-height:0 -->
         <tr class="nt-detail-row" id="detail-row-0">
           <td colspan="8">
             <div class="nt-detail-inner" id="detail-inner-0">
               <!-- Konten detail statis di sini -->
             </div>
           </td>
         </tr>
       </tbody>
     </table>

   Konvensi ID:
     data-target pada row/button = ID dari <tr> detail row
     <div class="nt-detail-inner"> harus berada di dalam <tr> detail row

   JS API:
     window.NetraUI.initCollapseTable(root);
   ═══════════════════════════════════════════════════════ */

function initCollapseTable(root) {
  root = root || document;

  root.querySelectorAll('[data-nt-collapse-table]').forEach(table => {
    if (table._ntCollapseInit) return;
    table._ntCollapseInit = true;

    function toggleRow(targetId, forceClose) {
      const detailRow = document.getElementById(targetId);
      if (!detailRow) return;
      const inner = detailRow.querySelector('.nt-detail-inner');
      const btn   = table.querySelector(`[data-nt-expand-btn][data-target="${targetId}"]`);
      const isOpen = inner && inner.classList.contains('open');

      if (forceClose || isOpen) {
        if (inner) inner.classList.remove('open');
        if (btn)   btn.classList.remove('open');
      } else {
        if (inner) inner.classList.add('open');
        if (btn)   btn.classList.add('open');
      }
    }

    /* ── Klik pada main row ── */
    table.addEventListener('click', e => {
      // Cek klik pada expand-btn (agar tidak double-toggle)
      const expandBtn = e.target.closest('[data-nt-expand-btn]');
      if (expandBtn) {
        e.stopPropagation();
        toggleRow(expandBtn.dataset.target);
        return;
      }
      // Klik pada row utama
      const row = e.target.closest('[data-nt-collapse-row]');
      if (row) toggleRow(row.dataset.target);
    });
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.showToast / formatRupiah  —  Shared utilities
   Dipindahkan dari ultimate-table.html (inline <script>).

   Markup toast (statis, sekali di layout/body):
     <div id="nt-toast">
       <i class="fa-solid fa-circle-check" style="color:#34D399"></i>
       <span id="nt-toast-msg"></span>
     </div>

   JS API:
     window.NetraUI.showToast(msg, durationMs?)
     window.NetraUI.formatRupiah(number)
   ═══════════════════════════════════════════════════════ */
let _ntToastTimer = null;
function showToast(msg, duration = 2200) {
  const t = document.getElementById('nt-toast');
  const m = document.getElementById('nt-toast-msg');
  if (!t || !m) { console.warn('[NetraUI] #nt-toast tidak ditemukan di halaman.'); return; }
  m.textContent = msg;
  t.classList.add('show');
  if (_ntToastTimer) clearTimeout(_ntToastTimer);
  _ntToastTimer = setTimeout(() => t.classList.remove('show'), duration);
}

function formatRupiah(n) {
  return 'Rp ' + Number(n).toLocaleString('id-ID');
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initEditableTable  —  Inline Cell Editing (Generic)
   Dipindahkan & dikonversi dari ultimate-table.html (inline <script>).

   STATIC HTML MODE — tidak ada innerHTML re-render baris.
   JS hanya membaca data-* attributes, menyisipkan <input>
   sementara saat editing, lalu menulis hasilnya kembali
   ke <td> + data-value.

   GENERIC: tidak terikat ke entity tertentu (produk, proyek,
   ticket, dll). Field, format, dan rule status semuanya
   dikonfigurasi via data-attribute di markup.

   Usage (HTML) — contoh entity "proyek":
     <table data-nt-editable-table
            data-currency-prefix="Rp "
            data-name-field="nama"
            data-entity-label="Proyek"
            data-status-field="progress"
            data-status-rules='[
              {"max":0,"label":"Belum Mulai","cls":"nt-badge-secondary"},
              {"max":49,"label":"Tertunda","cls":"nt-badge-danger"},
              {"max":99,"label":"In Progress","cls":"nt-badge-warning"},
              {"max":100,"label":"Selesai","cls":"nt-badge-success"}
            ]'>
       <tbody data-nt-editable-tbody>
         <tr data-nt-row data-row-id="1">
           <td class="nt-editable" data-field="nama" data-value="Redesign Website" data-edit-type="text">Redesign Website</td>
           <td class="nt-editable font-mono" data-field="budget" data-value="50000000" data-edit-type="number" data-format="currency">Rp 50.000.000</td>
           <td class="nt-editable font-mono" data-field="progress" data-value="85" data-edit-type="number">85</td>
           <td data-status-cell><span class="nt-badge nt-badge-warning">In Progress</span></td>
           <td>
             <div class="nt-cell-actions justify-end">
               <button data-nt-row-detail title="Detail"><i class="fa-regular fa-eye"></i></button>
               <button data-nt-row-delete title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
             </div>
           </td>
         </tr>
       </tbody>
     </table>

     <span data-nt-edit-status class="hidden">Tersimpan</span>
     <button data-nt-editable-reset>Reset</button>

   data-* attributes pada <table data-nt-editable-table>:
     data-currency-prefix — prefix mata uang untuk data-format="currency" (default "Rp ")
     data-name-field       — nama field yang dipakai sebagai label di toast detail/hapus
                              (default "name"). Sesuaikan dengan field judul/nama entity.
     data-entity-label     — label entity untuk teks toast, misal "Produk", "Proyek", "Ticket"
                              (default "Item")
     data-status-field     — nama field yang memicu perhitungan ulang badge status
                              (opsional; kosongkan jika tidak ada status otomatis)
     data-status-rules     — JSON array rule status, dievaluasi urut dari atas:
                              rule pertama dengan value <= rule.max akan dipakai.
                              Setiap rule: { "max": number, "label": string, "cls": string,
                              "color": string (opsional, warna teks sel data-status-field) }

   data-* attributes pada <td class="nt-editable">:
     data-field      — nama field (key untuk status rule & toast)
     data-value      — nilai mentah (number untuk numeric, text untuk lainnya)
     data-edit-type  — "text" | "number"
     data-format     — "currency" | "integer" (opsional, untuk format tampilan)

   Tombol:
     [data-nt-editable-reset] — reload halaman untuk reset ke markup asli
     [data-nt-row-delete]     — hapus <tr> dari DOM
     [data-nt-row-detail]     — tampilkan toast detail baris

   Integrasi AJAX (opsional):
     Setiap edit yang berhasil disimpan (value berubah) memicu
     CustomEvent 'nt:cell-saved' pada <table>, dan setiap hapus
     baris memicu 'nt:row-deleted'. Tangkap di luar untuk
     kirim request ke server:

       document.querySelector('[data-nt-editable-table]')
         .addEventListener('nt:cell-saved', (e) => {
           const { rowId, field, newValue } = e.detail;
           fetch(`/api/proyek/${rowId}`, {
             method: 'PATCH',
             headers: { 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
             body: JSON.stringify({ [field]: newValue })
           }).catch(() => NetraUI.showToast('Gagal menyimpan ke server'));
         });

       document.querySelector('[data-nt-editable-table]')
         .addEventListener('nt:row-deleted', (e) => {
           const { rowId } = e.detail;
           fetch(`/api/proyek/${rowId}`, {
             method: 'DELETE',
             headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
           });
         });

     event.detail berisi: { table, row, rowId, field, oldValue, newValue, type, format }
     (rowId diambil dari atribut [data-row-id] pada <tr data-nt-row>).

   JS API:
     window.NetraUI.initEditableTable(root); // re-init untuk dynamic content
   ═══════════════════════════════════════════════════════ */

function initEditableTable(root) {
  root = root || document;

  root.querySelectorAll('[data-nt-editable-table]').forEach(table => {
    if (table._ntEditableInit) return;
    table._ntEditableInit = true;

    const currencyPrefix = table.dataset.currencyPrefix || 'Rp ';
    const nameField      = table.dataset.nameField || 'name';
    const entityLabel    = table.dataset.entityLabel || 'Item';
    const statusField    = table.dataset.statusField || null;

    let statusRules = [];
    if (table.dataset.statusRules) {
      try { statusRules = JSON.parse(table.dataset.statusRules); }
      catch (e) { console.warn('[NetraUI] data-status-rules JSON tidak valid:', e); }
    }

    let activeCell = null;

    function displayValue(td) {
      const type   = td.dataset.editType || 'text';
      const format = td.dataset.format;
      const value  = td.dataset.value;

      if (type === 'number') {
        const num = parseFloat(value) || 0;
        if (format === 'currency') return currencyPrefix + num.toLocaleString('id-ID');
        return String(num);
      }
      return value;
    }

    /* ── Update badge status berdasarkan rule generic ── */
    function updateStatusCell(tr, value) {
      if (!statusRules.length) return;
      const statusCell = tr.querySelector('[data-status-cell]');
      if (!statusCell) return;
      const badge = statusCell.querySelector('.nt-badge');
      if (!badge) return;

      const rule = statusRules.find(r => value <= r.max) || statusRules[statusRules.length - 1];
      if (!rule) return;

      // Reset semua kelas nt-badge-* lalu pasang yang baru
      badge.className = 'nt-badge ' + (rule.cls || '');
      badge.textContent = rule.label || '';

      // Sinkronkan warna teks pada sel status-field itu sendiri (opsional)
      const statusValueCell = tr.querySelector(`[data-field="${statusField}"]`);
      if (statusValueCell) {
        statusValueCell.style.color = rule.color || '';
      }
    }

    function startEdit(td) {
      if (activeCell) finishEdit(true);

      const type  = td.dataset.editType || 'text';
      const value = td.dataset.value;

      activeCell = { td };
      td.classList.add('nt-editing');

      const input = document.createElement('input');
      input.className = 'nt-cell-input';
      input.id = 'nt-active-input';
      input.type = type === 'number' ? 'number' : 'text';
      input.value = value;
      if (type === 'number') input.min = '0';

      td.textContent = '';
      td.appendChild(input);
      input.focus();
      input.select();

      input.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === 'Tab') { e.preventDefault(); finishEdit(true); }
        if (e.key === 'Escape') finishEdit(false);
      });
      input.addEventListener('blur', () => setTimeout(() => { if (activeCell) finishEdit(true); }, 100));
    }

    function finishEdit(save) {
      if (!activeCell) return;
      const { td } = activeCell;
      const input = document.getElementById('nt-active-input');
      const field = td.dataset.field;
      const type  = td.dataset.editType || 'text';
      const tr    = td.closest('tr');
      const oldValue = td.dataset.value;

      if (save && input) {
        let newValue = input.value;

        if (type === 'number') {
          const num = parseFloat(newValue);
          newValue = !isNaN(num) && num >= 0 ? num : parseFloat(td.dataset.value);
        }

        td.dataset.value = String(newValue);

        if (statusField && field === statusField) updateStatusCell(tr, Number(newValue));

        const displayed = (type === 'number' && td.dataset.format === 'currency')
          ? currencyPrefix + Number(newValue).toLocaleString('id-ID')
          : newValue;
        showToast(`Tersimpan: ${field} → ${displayed}`);

        /* ── Dispatch event untuk integrasi AJAX ──
           Tangkap di luar (root.addEventListener('nt:cell-saved', ...))
           untuk kirim PATCH/PUT ke server. Event ini hanya terpicu
           saat value benar-benar berubah & berhasil disimpan. */
        if (String(oldValue) !== String(newValue)) {
          table.dispatchEvent(new CustomEvent('nt:cell-saved', {
            bubbles: true,
            detail: {
              table,
              row: tr,
              rowId: tr ? tr.dataset.rowId : null,
              field,
              oldValue,
              newValue,
              type,
              format: td.dataset.format || null
            }
          }));
        }
      }

      activeCell = null;
      td.classList.remove('nt-editing');
      td.textContent = displayValue(td);
    }

    /* ── Klik sel editable ── */
    table.addEventListener('click', e => {
      const td = e.target.closest('.nt-editable');
      if (td && td.closest('table') === table && !td.classList.contains('nt-editing')) {
        startEdit(td);
        return;
      }

      /* ── Hapus baris ── */
      const delBtn = e.target.closest('[data-nt-row-delete]');
      if (delBtn) {
        const tr = delBtn.closest('[data-nt-row]');
        if (tr) {
          const rowId = tr.dataset.rowId;
          table.dispatchEvent(new CustomEvent('nt:row-deleted', {
            bubbles: true,
            detail: { table, row: tr, rowId }
          }));
          tr.remove();
          showToast(`${entityLabel} dihapus`);
        }
        return;
      }

      /* ── Detail baris ── */
      const detailBtn = e.target.closest('[data-nt-row-detail]');
      if (detailBtn) {
        const tr = detailBtn.closest('[data-nt-row]');
        const nameCell = tr && tr.querySelector(`[data-field="${nameField}"]`);
        const name = nameCell ? nameCell.dataset.value : '';
        showToast(`Detail ${entityLabel.toLowerCase()}: ${name}`);
      }
    });
  });

  /* ── Tombol reset (re-init dengan reload halaman) ── */
  root.querySelectorAll('[data-nt-editable-reset]').forEach(btn => {
    if (btn._ntEditableResetWired) return;
    btn._ntEditableResetWired = true;
    btn.addEventListener('click', () => {
      showToast('Data di-reset ke default');
      setTimeout(() => location.reload(), 400);
    });
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initGanttTable  —  Project Roadmap Gantt Chart
   Dipindahkan & dikonversi dari ultimate-table.html (inline <script>).

   STATIC HTML MODE — setiap bulan (prev / aktif / next)
   dirender sebagai <table class="nt-gantt nt-gantt-month">
   statis di markup. JS hanya men-toggle atribut [hidden]
   antar bulan dan men-scroll ke kolom "today".

   Usage (HTML):
     <div data-nt-gantt data-active-month="2026-06">
       <table class="nt-gantt nt-gantt-month" data-month="2026-05" hidden>...</table>
       <table class="nt-gantt nt-gantt-month" data-month="2026-06" data-today-day="12">...</table>
       <table class="nt-gantt nt-gantt-month" data-month="2026-07" hidden>...</table>
     </div>

     <button data-nt-gantt-prev>‹</button>
     <span data-nt-gantt-label></span>
     <button data-nt-gantt-next>›</button>

   Catatan:
   - data-month menggunakan format "YYYY-MM".
   - data-today-day (opsional, di tabel aktif) memicu
     auto-scroll horizontal ke kolom hari ini saat init.
   - Karena markup tiap bulan sudah statis & lengkap di HTML,
     navigasi hanya berpindah di antara tabel yang tersedia.
     Tambahkan tabel bulan lain ke markup bila dibutuhkan.

   JS API:
     window.NetraUI.initGanttTable(root); // re-init untuk dynamic content
   ═══════════════════════════════════════════════════════ */

const NT_MONTHS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function initGanttTable(root) {
  root = root || document;

  root.querySelectorAll('[data-nt-gantt]').forEach(container => {
    if (container._ntGanttInit) return;
    container._ntGanttInit = true;

    const prevBtn = container.parentElement.querySelector('[data-nt-gantt-prev]')
      || document.querySelector('[data-nt-gantt-prev]');
    const nextBtn = container.parentElement.querySelector('[data-nt-gantt-next]')
      || document.querySelector('[data-nt-gantt-next]');
    const label   = container.parentElement.querySelector('[data-nt-gantt-label]')
      || document.querySelector('[data-nt-gantt-label]');

    function months() {
      return Array.from(container.querySelectorAll('.nt-gantt-month'));
    }

    function activeIndex() {
      return months().findIndex(t => !t.hasAttribute('hidden'));
    }

    function updateLabel(table) {
      if (!label) return;
      const [y, m] = table.dataset.month.split('-').map(Number);
      label.textContent = `${NT_MONTHS_ID[m - 1]} ${y}`;
    }

    function scrollToToday(table) {
      const todayDay = parseInt(table.dataset.todayDay || '0', 10);
      if (todayDay > 0) {
        const targetLeft = (todayDay - 5) * 28;
        setTimeout(() => { container.scrollLeft = Math.max(0, targetLeft); }, 50);
      } else {
        container.scrollLeft = 0;
      }
    }

    function showMonth(idx) {
      const list = months();
      if (idx < 0 || idx >= list.length) return;
      list.forEach((t, i) => { t.hidden = i !== idx; });
      updateLabel(list[idx]);
      scrollToToday(list[idx]);

      if (prevBtn) prevBtn.disabled = idx === 0;
      if (nextBtn) nextBtn.disabled = idx === list.length - 1;
    }

    /* ── Init: tampilkan bulan aktif ── */
    const initIdx = Math.max(0, activeIndex());
    showMonth(initIdx);

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        const idx = activeIndex();
        if (idx > 0) showMonth(idx - 1);
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        const idx = activeIndex();
        if (idx < months().length - 1) showMonth(idx + 1);
      });
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════
   initBasicTable() — VERSI PERBAIKAN

   Ganti fungsi initBasicTable() yang ada di netra-tables.js dengan versi
   ini. Perubahan dari versi asli:

   1. `allRows` diubah dari snapshot statis (Array.from sekali di awal)
      menjadi fungsi allRows() yang query ulang dari DOM tiap dipanggil.
      → Tidak akan pernah stale walau <tbody> diganti dari luar (LiveDomJS
        live-target, atau apapun).

   2. Guard `wrapper._ntBasicInit` — mencegah listener dobel kalau
      initBasicTable() ke-panggil lagi untuk wrapper yang sama.

   3. Expose `wrapper._ntBasicRefresh = render` — dipanggil dari
      js/netra-tables-patch.js lewat closest() saat live-target presisi ke
      <tbody> (di dalam wrapper), bukan ke wrapper itu sendiri.
   ═══════════════════════════════════════════════════════════════════════ */
function initBasicTable(root = document) {
    root.querySelectorAll('[data-nt-basic-table]').forEach(wrapper => {
        if (wrapper._ntBasicInit) return;
        wrapper._ntBasicInit = true;

        const PAGE_SIZE = parseInt(wrapper.dataset.ntPageSize || '8', 10);

        const searchInput = wrapper.querySelector('[data-nt-search-input]');
        const checkAll = wrapper.querySelector('[data-nt-check-all]');
        const selectedCount = wrapper.querySelector('[data-nt-selected-count]');
        const tbody = wrapper.querySelector('[data-nt-table-body]');
        const paginationEl = wrapper.querySelector('[data-nt-pagination]');
        const tableInfoEl = wrapper.querySelector('[data-nt-table-info]');
        if (!tbody) return;

        let currentPage = 1;
        let filterText = '';
        let sortCol = null;
        let sortDir = 'asc';

        /* Query live, bukan snapshot beku — kunci dari fix ini */
        function allRows() {
            return Array.from(tbody.querySelectorAll('tr'));
        }

        function cellText(tr, col) {
            const td = tr.querySelectorAll('td')[col];
            if (!td) return '';
            return (td.dataset.sortValue || td.innerText || td.textContent || '').trim().toLowerCase();
        }

        function getFiltered() {
            const rows = allRows();
            if (!filterText) return rows;
            return rows.filter(tr =>
                Array.from(tr.querySelectorAll('td')).some(td =>
                    (td.dataset.sortValue || td.innerText || td.textContent || '')
                        .toLowerCase().includes(filterText)
                )
            );
        }

        function render() {
            const rows = getFiltered();
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
            if (currentPage > totalPages) currentPage = 1;
            const start = (currentPage - 1) * PAGE_SIZE;
            const slice = rows.slice(start, start + PAGE_SIZE);

            allRows().forEach(tr => {
                tr.style.display = 'none';
                tr.classList.remove('nt-row-selected');
            });
            if (checkAll) checkAll.checked = false;

            slice.forEach(tr => {
                tr.style.display = '';
                const cb = tr.querySelector('.row-check');
                if (cb) cb.checked = false;
            });

            if (tableInfoEl) {
                const from = total === 0 ? 0 : start + 1;
                const to = Math.min(start + PAGE_SIZE, total);
                tableInfoEl.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
            }

            updateSelected();
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (!paginationEl) return;
            let html = `<button class="nt-page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i></button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<button class="nt-page-btn${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<span class="nt-page-btn" style="pointer-events:none;border:none;background:none">…</span>`;
                }
            }

            html += `<button class="nt-page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i></button>`;
            paginationEl.innerHTML = html;

            paginationEl.querySelectorAll('[data-page]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const p = parseInt(btn.dataset.page, 10);
                    const total = Math.ceil(getFiltered().length / PAGE_SIZE);
                    if (p < 1 || p > total) return;
                    currentPage = p;
                    render();
                });
            });
        }

        function updateSelected() {
            const count = wrapper.querySelectorAll('.row-check:checked').length;
            if (selectedCount) {
                selectedCount.textContent = count + ' dipilih';
                selectedCount.classList.toggle('hidden', count === 0);
            }
        }

        tbody.addEventListener('click', e => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            if (e.target.matches('.row-check')) return;
            tr.classList.toggle('nt-row-selected');
            const cb = tr.querySelector('.row-check');
            if (cb) cb.checked = tr.classList.contains('nt-row-selected');
            updateSelected();
        });

        tbody.addEventListener('change', e => {
            if (!e.target.matches('.row-check')) return;
            e.target.closest('tr').classList.toggle('nt-row-selected', e.target.checked);
            updateSelected();
        });

        if (checkAll) {
            checkAll.addEventListener('change', () => {
                const visible = allRows().filter(tr => tr.style.display !== 'none');
                visible.forEach(tr => {
                    const cb = tr.querySelector('.row-check');
                    if (cb) cb.checked = checkAll.checked;
                    tr.classList.toggle('nt-row-selected', checkAll.checked);
                });
                updateSelected();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                filterText = searchInput.value.trim().toLowerCase();
                currentPage = 1;
                render();
            });
        }

        wrapper.querySelectorAll('.nt-th-sort').forEach(th => {
            th.addEventListener('click', () => {
                const col = th.dataset.ntSortCol;
                wrapper.querySelectorAll('.nt-th-sort').forEach(t => t.classList.remove('asc', 'desc'));

                if (sortCol === col) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortCol = col;
                    sortDir = 'asc';
                }
                th.classList.add(sortDir);

                const thIndex = Array.from(th.closest('tr').querySelectorAll('th')).indexOf(th);
                const sorted = allRows().sort((a, b) => {
                    const va = cellText(a, thIndex);
                    const vb = cellText(b, thIndex);
                    return sortDir === 'asc' ? va.localeCompare(vb, 'id') : vb.localeCompare(va, 'id');
                });
                sorted.forEach(tr => tbody.appendChild(tr));
                currentPage = 1;
                render();
            });
        });

        /* Expose supaya bisa dipanggil dari luar (lihat netra-tables-patch.js) */
        wrapper._ntBasicRefresh = render;

        render();
    });
}

/* ── Auto-init on DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
  initTreeTable(document);
  initCollapseTable(document);
  initEditableTable(document);
  initGanttTable(document);
  initBasicTable(document);
});

/* ── Export ke public API ── */
if (!window.NetraUI) window.NetraUI = {};
window.NetraUI.initTreeTable     = initTreeTable;
window.NetraUI.initCollapseTable = initCollapseTable;
window.NetraUI.initEditableTable = initEditableTable;
window.NetraUI.initGanttTable    = initGanttTable;
window.NetraUI.initBasicTable    = initBasicTable;
window.NetraUI.showToast         = showToast;
window.NetraUI.formatRupiah      = formatRupiah;

