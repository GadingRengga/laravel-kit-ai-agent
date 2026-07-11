/* ═══════════════════════════════════════════════════════
   Netra UI — Kanban Module JS  (netra-kanban.js)
   Drag-and-drop, view switching, quick-add, list collapse.
   Semua interaksi via data-* attribute agar compatible
   dengan Laravel Blade / Livewire.

   DATA ATTRIBUTES yang dipakai:
   - data-kanban-board          : board container
   - data-kanban-col-cards      : droppable zone per kolom
   - data-kanban-card           : draggable card
   - data-kanban-col-id         : ID kolom (string)
   - data-kanban-card-id        : ID kartu  (string)
   - data-view-tab="board|list" : tombol ganti view
   - data-view-target           : id container view
   - data-quick-add-trigger     : tombol "Add card" di footer
   - data-quick-add-form        : inline quick-add form
   - data-quick-save            : tombol simpan quick-add
   - data-quick-cancel          : tombol batal quick-add
   - data-list-group-toggle     : header kolom di list view
   - data-list-rows             : rows container di list view
   - data-col-menu              : toggle menu kolom
   - data-card-menu             : toggle menu kartu
═══════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── Helpers ── */
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }
  function on(el, ev, fn) { if (el) el.addEventListener(ev, fn); }

  /* ══════════════════════════════════════
     1. VIEW SWITCHER  (Board / List)
  ══════════════════════════════════════ */
  function initViewSwitcher() {
    qsa('[data-view-tab]').forEach(tab => {
      on(tab, 'click', function () {
        const viewId = this.dataset.viewTab;

        /* deactivate all tabs */
        qsa('[data-view-tab]').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        /* hide/show view panels */
        qsa('[data-view-target]').forEach(panel => {
          panel.hidden = panel.dataset.viewTarget !== viewId;
        });
      });
    });
  }

  /* ══════════════════════════════════════
     2. DRAG AND DROP  (HTML5 DnD API)
  ══════════════════════════════════════ */
  let dragCard = null;
  let ghostEl = null;

  function createGhost() {
    const g = document.createElement('div');
    g.className = 'nt-kanban-ghost';
    g.setAttribute('aria-hidden', 'true');
    return g;
  }

  function removeGhost() {
    if (ghostEl && ghostEl.parentNode) {
      ghostEl.parentNode.removeChild(ghostEl);
    }
    ghostEl = null;
  }

  function getInsertionPoint(zone, clientY) {
    const cards = qsa('[data-kanban-card]:not(.nt-dragging)', zone);
    let closest = null;
    let closestOffset = Number.NEGATIVE_INFINITY;
    cards.forEach(card => {
      const rect = card.getBoundingClientRect();
      const offset = clientY - (rect.top + rect.height / 2);
      if (offset < 0 && offset > closestOffset) {
        closestOffset = offset;
        closest = card;
      }
    });
    return closest; // insert before this, or null = append
  }

  function initDragDrop() {
    /* Make existing cards draggable */
    bindCards(document);

    /* Zone listeners */
    qsa('[data-kanban-col-cards]').forEach(zone => bindZone(zone));
  }

  function bindCards(ctx) {
    qsa('[data-kanban-card]', ctx).forEach(card => {
      if (card._ntDndBound) return;
      card._ntDndBound = true;
      card.setAttribute('draggable', 'true');

      on(card, 'dragstart', function (e) {
        dragCard = this;
        this.classList.add('nt-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.kanbanCardId || '');
        ghostEl = createGhost();
        /* clone ghost height to match card */
        ghostEl.style.height = this.offsetHeight + 'px';
      });

      on(card, 'dragend', function () {
        this.classList.remove('nt-dragging');
        removeGhost();
        qsa('[data-kanban-col-cards]').forEach(z => z.classList.remove('nt-drop-over'));
        dragCard = null;
      });
    });
  }

  function bindZone(zone) {
    if (zone._ntZoneBound) return;
    zone._ntZoneBound = true;

    on(zone, 'dragover', function (e) {
      if (!dragCard) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      this.classList.add('nt-drop-over');

      /* reposition ghost */
      removeGhost();
      ghostEl = createGhost();
      ghostEl.style.height = dragCard.offsetHeight + 'px';
      const before = getInsertionPoint(this, e.clientY);
      if (before) {
        this.insertBefore(ghostEl, before);
      } else {
        this.appendChild(ghostEl);
      }
    });

    on(zone, 'dragleave', function (e) {
      /* only remove if truly leaving zone */
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('nt-drop-over');
        removeGhost();
      }
    });

    on(zone, 'drop', function (e) {
      e.preventDefault();
      if (!dragCard) return;
      this.classList.remove('nt-drop-over');

      const before = ghostEl ? ghostEl.nextElementSibling : null;
      removeGhost();

      if (before && before !== dragCard) {
        this.insertBefore(dragCard, before);
      } else if (!before) {
        this.appendChild(dragCard);
      }

      /* Update column count badges */
      updateColCounts();

      /* Dispatch custom event (Livewire / Alpine can listen) */
      const colId = this.closest('[data-kanban-col-id]')?.dataset.kanbanColId;
      const cardId = dragCard.dataset.kanbanCardId;
      document.dispatchEvent(new CustomEvent('nt-kanban:move', {
        detail: { cardId, colId }
      }));
    });
  }

  function updateColCounts() {
    qsa('[data-kanban-col-id]').forEach(col => {
      const cards = qsa('[data-kanban-card]', col).length;
      const badge = qs('[data-col-count]', col);
      if (badge) badge.textContent = cards;
    });
  }

  /* ══════════════════════════════════════
     3. QUICK ADD CARD (inline)
  ══════════════════════════════════════ */
  function initQuickAdd() {
    /* Trigger buttons */
    qsa('[data-quick-add-trigger]').forEach(btn => {
      on(btn, 'click', function () {
        const colId = this.dataset.quickAddTrigger;
        const form = qs(`[data-quick-add-form="${colId}"]`);
        if (!form) return;
        form.hidden = false;
        this.hidden = true;
        const ta = qs('textarea', form);
        if (ta) ta.focus();
      });
    });

    /* Save buttons */
    qsa('[data-quick-save]').forEach(btn => {
      on(btn, 'click', function () {
        const colId = this.dataset.quickSave;
        const form = qs(`[data-quick-add-form="${colId}"]`);
        const zone = qs(`[data-kanban-col-cards][data-col-for="${colId}"]`);
        const ta = qs('textarea', form);
        const text = ta ? ta.value.trim() : '';

        if (text && zone) {
          const newCard = buildNewCard(text, colId);
          zone.appendChild(newCard);
          bindCards(zone);
          updateColCounts();
        }

        closeQuickAdd(colId);
      });
    });

    /* Cancel buttons */
    qsa('[data-quick-cancel]').forEach(btn => {
      on(btn, 'click', function () {
        closeQuickAdd(this.dataset.quickCancel);
      });
    });

    /* Keyboard: Ctrl/Cmd+Enter = save, Esc = cancel */
    document.addEventListener('keydown', function (e) {
      const form = e.target.closest('[data-quick-add-form]');
      if (!form) return;
      const colId = form.dataset.quickAddForm;
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        qs(`[data-quick-save="${colId}"]`)?.click();
      }
      if (e.key === 'Escape') {
        closeQuickAdd(colId);
      }
    });
  }

  function closeQuickAdd(colId) {
    const form = qs(`[data-quick-add-form="${colId}"]`);
    const btn = qs(`[data-quick-add-trigger="${colId}"]`);
    if (form) {
      form.hidden = true;
      const ta = qs('textarea', form);
      if (ta) ta.value = '';
    }
    if (btn) btn.hidden = false;
  }

  function buildNewCard(text, colId) {
    const wrap = document.createElement('div');
    wrap.className = 'nt-kanban-card';
    wrap.setAttribute('data-kanban-card', '');
    wrap.setAttribute('data-kanban-card-id', 'new-' + Date.now());
    wrap.innerHTML = `
      <div class="nt-kanban-card-top">
        <span class="nt-kanban-card-label nt-label-indigo">Task</span>
        <button class="nt-kanban-card-menu-btn" data-card-menu aria-label="Menu kartu">
          <i class="fa-solid fa-ellipsis"></i>
        </button>
      </div>
      <p class="nt-kanban-card-title">${escHtml(text)}</p>
      <div class="nt-kanban-card-footer">
        <div class="nt-kanban-card-meta">
          <span class="nt-kanban-card-meta-item">
            <i class="fa-regular fa-calendar"></i> Baru dibuat
          </span>
        </div>
      </div>`;
    return wrap;
  }

  function escHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /* ══════════════════════════════════════
     4. LIST VIEW — COLLAPSIBLE GROUPS
  ══════════════════════════════════════ */
  function initListCollapse() {
    qsa('[data-list-group-toggle]').forEach(header => {
      on(header, 'click', function () {
        const rows = qs(`[data-list-rows="${this.dataset.listGroupToggle}"]`);
        const chevron = qs('.nt-kanban-list-chevron', this);
        if (!rows) return;
        const open = !rows.hidden;
        rows.hidden = open;
        if (chevron) chevron.classList.toggle('open', !open);
      });
    });
  }

  /* ══════════════════════════════════════
     5. COLUMN CONTEXT MENU  (simple)
  ══════════════════════════════════════ */
  function initContextMenus() {
    document.addEventListener('click', function (e) {
      /* Column menu toggle */
      const colMenuBtn = e.target.closest('[data-col-menu]');
      if (colMenuBtn) {
        const panel = qs(`[data-col-menu-panel="${colMenuBtn.dataset.colMenu}"]`);
        if (panel) {
          const open = panel.classList.contains('open');
          closeAllMenuPanels();
          if (!open) panel.classList.add('open');
        }
        e.stopPropagation();
        return;
      }

      /* Card menu toggle */
      const cardMenuBtn = e.target.closest('[data-card-menu]');
      if (cardMenuBtn) {
        const card = cardMenuBtn.closest('[data-kanban-card]');
        const panel = card ? qs('[data-card-menu-panel]', card) : null;
        if (panel) {
          const open = panel.classList.contains('open');
          closeAllMenuPanels();
          if (!open) panel.classList.add('open');
        }
        e.stopPropagation();
        return;
      }

      /* Close all on outside click */
      closeAllMenuPanels();
    });
  }

  function closeAllMenuPanels() {
    qsa('[data-col-menu-panel].open, [data-card-menu-panel].open').forEach(p => p.classList.remove('open'));
  }

  /* ══════════════════════════════════════
     6. ADD COLUMN placeholder
  ══════════════════════════════════════ */
  function initAddColumn() {
    qsa('[data-add-col]').forEach(btn => {
      on(btn, 'click', function () {
        const name = prompt('Nama kolom baru:');
        if (!name || !name.trim()) return;
        const board = qs('[data-kanban-board]');
        if (!board) return;
        const colId = 'col-' + Date.now();
        const col = buildNewCol(name.trim(), colId);
        /* insert before the add-col placeholder */
        board.insertBefore(col, this.closest('[data-add-col-wrap]') || null);
        bindZone(qs('[data-kanban-col-cards]', col));
        initQuickAddFor(col, colId);
      });
    });
  }

  function buildNewCol(name, colId) {
    const wrap = document.createElement('div');
    wrap.className = 'nt-kanban-col nt-col-todo';
    wrap.setAttribute('data-kanban-col-id', colId);
    wrap.innerHTML = `
      <div class="nt-kanban-col-header">
        <span class="nt-kanban-col-dot"></span>
        <span class="nt-kanban-col-name">${escHtml(name)}</span>
        <span class="nt-kanban-col-count" data-col-count>0</span>
        <button class="nt-kanban-col-menu-btn" data-col-menu="${colId}" aria-label="Menu kolom">
          <i class="fa-solid fa-ellipsis"></i>
        </button>
      </div>
      <div class="nt-kanban-col-cards" data-kanban-col-cards data-col-for="${colId}"></div>
      <div class="nt-kanban-col-footer">
        <button class="nt-kanban-add-card-btn" data-quick-add-trigger="${colId}">
          <i class="fa-solid fa-plus"></i> Tambah kartu
        </button>
        <div class="nt-kanban-quick-add" data-quick-add-form="${colId}" hidden>
          <textarea placeholder="Judul kartu…"></textarea>
          <div class="nt-kanban-quick-add-actions">
            <button class="nt-kanban-quick-save" data-quick-save="${colId}">Simpan</button>
            <button class="nt-kanban-quick-cancel" data-quick-cancel="${colId}">Batal</button>
          </div>
        </div>
      </div>`;
    return wrap;
  }

  function initQuickAddFor(ctx, colId) {
    /* bind trigger */
    const trigger = qs(`[data-quick-add-trigger="${colId}"]`, ctx);
    on(trigger, 'click', function () {
      const form = qs(`[data-quick-add-form="${colId}"]`, ctx);
      if (!form) return;
      form.hidden = false;
      this.hidden = true;
      qs('textarea', form)?.focus();
    });
    const saveBtn = qs(`[data-quick-save="${colId}"]`, ctx);
    on(saveBtn, 'click', function () {
      const form = qs(`[data-quick-add-form="${colId}"]`, ctx);
      const zone = qs(`[data-kanban-col-cards][data-col-for="${colId}"]`, ctx);
      const ta = qs('textarea', form);
      const text = ta ? ta.value.trim() : '';
      if (text && zone) {
        const card = buildNewCard(text, colId);
        zone.appendChild(card);
        bindCards(zone);
        updateColCounts();
      }
      closeQuickAdd(colId);
    });
    const cancelBtn = qs(`[data-quick-cancel="${colId}"]`, ctx);
    on(cancelBtn, 'click', () => closeQuickAdd(colId));
  }

  /* ══════════════════════════════════════
     BOOT
  ══════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', function () {
    initViewSwitcher();
    initDragDrop();
    initQuickAdd();
    initListCollapse();
    initContextMenus();
    initAddColumn();
  });

  /* expose untuk re-init manual (Livewire, AJAX) */
  window.NetraKanban = {
    initDragDrop,
    initQuickAdd,
    initListCollapse,
    updateColCounts,
  };

})();
