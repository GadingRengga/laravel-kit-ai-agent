/* ═══════════════════════════════════════════════════════
   NetraUI — Shared JavaScript  v5
   Includes: Sidebar, Submenu, Dropdowns, Dark mode,
             TomSelect (nt-select),
             Vanilla Datepicker (nt-datepicker),
             Vanilla Timepicker (nt-timepicker),
             FileUpload (nt-fileupload) — STATIC HTML,
             ColorPicker (nt-colorpicker) — STATIC HTML

   Auto-init via data attributes on DOMContentLoaded.
   Manual re-init via window.NetraUI.init*(root) untuk
   dynamic content (Livewire, modals, AJAX).

   STATIC HTML MODE:
   FileUpload dan ColorPicker kini menggunakan HTML statis
   (tidak lagi inject innerHTML). Tinggal tulis markup-nya
   di Blade/HTML, JS hanya menempelkan event listeners.
═══════════════════════════════════════════════════════ */

/* Dark mode init handled by inline <script> in <head> of each page */

/* ── Sidebar ── */
let isCollapsed = false;

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const collapseIcon = document.getElementById('collapse-icon');
  if (!sidebar) return;
  isCollapsed = !isCollapsed;
  sidebar.classList.toggle('collapsed', isCollapsed);
  if (collapseIcon) collapseIcon.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
}
function openMobileSidebar() {
  const sidebar = document.getElementById('sidebar');
  const ov = document.getElementById('mob-overlay');
  if (!sidebar || !ov) return;
  sidebar.classList.add('mobile-open');
  ov.classList.remove('hidden');
  requestAnimationFrame(() => ov.style.opacity = '1');
  document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
  const sidebar = document.getElementById('sidebar');
  const ov = document.getElementById('mob-overlay');
  if (!sidebar || !ov) return;
  sidebar.classList.remove('mobile-open');
  ov.style.opacity = '0';
  setTimeout(() => { ov.classList.add('hidden'); document.body.style.overflow = ''; }, 250);
}
window.addEventListener('resize', () => {
  if (window.innerWidth >= 1024) {
    const ov = document.getElementById('mob-overlay');
    ov.style.opacity = '0'; ov.classList.add('hidden');
    document.body.style.overflow = '';
  }
});

/* ── Submenu ── */
function toggleSubmenu(el, e) {
  e.preventDefault();
  const ni = el.closest('.nav-item');
  const sm = ni.querySelector(':scope > .submenu');
  if (!sm) return;
  const ch = el.querySelector('.chevron');
  const open = sm.classList.contains('open');
  sm.classList.toggle('open', !open);
  if (ch) ch.classList.toggle('open', !open);
}

/* ── Mark active nav items (termasuk parent dari sub-item aktif) ──
   Dipanggil sekali saat page load. Cocokkan URL saat ini dengan href nav. */
function initNavActive() {
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';

  document.querySelectorAll('#sidebar .nav-link[href]').forEach(link => {
    const linkFile = link.getAttribute('href').split('/').pop();
    if (!linkFile || link.getAttribute('onclick')) return;

    if (linkFile === currentPath) {
      // Mark this item active
      const navItem = link.closest('.nav-item');
      if (navItem) navItem.classList.add('active');

      // If inside submenu, open parent submenu and mark parent has-active
      const parentSubmenu = navItem?.closest('.submenu');
      if (parentSubmenu) {
        parentSubmenu.classList.add('open');
        const parentNavItem = parentSubmenu.closest('.nav-item');
        if (parentNavItem) {
          parentNavItem.classList.add('has-active');
          const ch = parentNavItem.querySelector(':scope > .nav-link .chevron');
          if (ch) ch.classList.add('open');
        }
      }
    }
  });
}

/* ── Topbar dropdowns ── */
const dropdownMap = { notif: 'notif-dropdown', msg: 'msg-dropdown', acct: 'acct-dropdown' };
function toggleDropdown(key) {
  const tid = dropdownMap[key];
  Object.values(dropdownMap).forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('open', id === tid ? !el.classList.contains('open') : false);
  });
}
document.addEventListener('click', e => {
  const inside = ['notif-wrap', 'msg-wrap', 'acct-wrap'].some(id => document.getElementById(id)?.contains(e.target));
  if (!inside) Object.values(dropdownMap).forEach(id => document.getElementById(id)?.classList.remove('open'));
});

/* ── Dark mode toggle ──
   Menggunakan class .dark di <html> untuk Tailwind compat,
   dan data-theme="dark" sebagai hook CSS netra-ui.css.
   Kedua atribut selalu sinkron otomatis. */
function toggleDark() {
  const isDark = document.documentElement.classList.toggle('dark');
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

/* ── Nav active state (click) ── */
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', function () {
    if (this.getAttribute('onclick')) return;
    // Clear all active + has-active
    document.querySelectorAll('.nav-item').forEach(i => {
      i.classList.remove('active', 'has-active');
    });
    const navItem = this.closest('.nav-item');
    navItem?.classList.add('active');
    // If inside submenu, mark parent has-active
    const parentSubmenu = navItem?.closest('.submenu');
    if (parentSubmenu) {
      parentSubmenu.closest('.nav-item')?.classList.add('has-active');
    }
  });
});

/* ── Auto-init dark sync ── */
(function syncDarkThemeAttr() {
  const isDark = document.documentElement.classList.contains('dark');
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
})();

/* ── Auto-init: tandai menu sidebar aktif sesuai URL (semua halaman) ── */
document.addEventListener('DOMContentLoaded', () => {
  initNavActive();
});


/* ═══════════════════════════════════════════════════════
   NetraUI.initSelect  —  TomSelect wrapper

   Usage (HTML):
     <!-- Single select -->
     <select class="nt-select" name="category"
             data-placeholder="Pilih kategori…">
       <option value="">Pilih…</option>
       <option value="1">Electronics</option>
     </select>

     <!-- Multiple select -->
     <select class="nt-select" name="tags[]" multiple
             data-placeholder="Pilih tags…"
             data-max-items="5">
       <option value="1" selected>Laravel</option>
       <option value="2">Vue.js</option>
     </select>

   data-* attributes:
     data-placeholder  — placeholder text
     data-max-items    — max selections (multiple only)
     data-searchable   — "false" to disable search

   JS API:
     const ts = el._tomselect;    // native TomSelect instance
     window.NetraUI.initSelect(root); // re-init in scope
   ═══════════════════════════════════════════════════════ */
function initSelect(root) {
  root = root || document;
  root.querySelectorAll('select.nt-select').forEach(el => {
    if (el._tomselect) return; // already init
    const isMultiple = el.hasAttribute('multiple');
    const placeholder = el.dataset.placeholder || (isMultiple ? 'Pilih…' : '');
    const maxItems = el.dataset.maxItems ? parseInt(el.dataset.maxItems) : (isMultiple ? null : 1);
    const searchable = el.dataset.searchable !== 'false';
    const isDark = document.documentElement.classList.contains('dark');

    new TomSelect(el, {
      maxItems,
      placeholder,
      allowEmptyOption: true,
      selectOnTab: true,
      plugins: isMultiple ? ['remove_button', 'no_active_items'] : [],
      searchField: ['text'],
      create: false,
      controlInput: searchable ? undefined : null,
      render: {
        option(data, escape) {
          return `<div class="option">${escape(data.text)}</div>`;
        },
        item(data, escape) {
          return `<div>${escape(data.text)}</div>`;
        },
        no_results() {
          return `<div class="no-results"><i class="fa-regular fa-face-frown-open" style="display:block;font-size:20px;margin-bottom:6px;opacity:.35"></i>Tidak ada hasil.</div>`;
        }
      },
      onInitialize() {
        // Sync dark mode class to dropdown
        const wrapper = this.wrapper;
        if (isDark) wrapper.classList.add('dark-mode-active');
      }
    });
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initDatepicker  —  Vanilla JS Calendar

   Usage (HTML):
     <input type="text" class="nt-datepicker"
            name="birth_date"
            data-date-format="d/m/Y"
            data-default-date="2026-06-08"
            data-min-date="2000-01-01"
            data-max-date="today"
            data-mode="range"
            placeholder="Pilih tanggal…">

   data-* attributes:
     data-date-format   — display format tokens: d D l dd j F M Y (default "d/m/Y")
     data-default-date  — pre-selected date (YYYY-MM-DD or "today")
     data-min-date      — min selectable date (YYYY-MM-DD or "today")
     data-max-date      — max selectable date (YYYY-MM-DD or "today")
     data-mode          — "single"|"range" (default "single")

   JS API:
     el._akdp             — instance object
     window.NetraUI.initDatepicker(root);
   ═══════════════════════════════════════════════════════ */

(function () {

  const MONTHS_LONG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const DAYS_SHORT = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const DAYS_LONG = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  /* Parse "today" or "YYYY-MM-DD" → Date (midnight local) */
  function parseDate(str) {
    if (!str) return null;
    if (str === 'today') return startOfDay(new Date());
    const m = str.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;
    return new Date(+m[1], +m[2] - 1, +m[3]);
  }

  function startOfDay(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
  function sameDay(a, b) { return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); }

  /* Format a Date → string by token pattern */
  function formatDate(d, fmt) {
    if (!d) return '';
    const day = d.getDate();
    const month = d.getMonth(); // 0-based
    const year = d.getFullYear();
    const dow = d.getDay();
    return fmt
      .replace(/\bdd\b/g, String(dow))
      .replace(/\bl\b/g, DAYS_LONG[dow])
      .replace(/\bD\b/g, DAYS_SHORT[dow])
      .replace(/\bF\b/g, MONTHS_LONG[month])
      .replace(/\bM\b/g, MONTHS_SHORT[month])
      .replace(/\bY\b/g, year)
      .replace(/\bm\b/g, String(month + 1).padStart(2, '0'))
      .replace(/\bn\b/g, String(month + 1))
      .replace(/\bd\b/g, String(day).padStart(2, '0'))
      .replace(/\bj\b/g, String(day));
  }

  /* ── Single Datepicker instance ── */
  function AkDatepicker(input) {
    const self = this;
    self.input = input;
    self.fmt = input.dataset.dateFormat || input.dataset.altFormat || 'd/m/Y';
    self.mode = input.dataset.mode || 'single';
    self.minDate = parseDate(input.dataset.minDate || null);
    self.maxDate = parseDate(input.dataset.maxDate || null);
    self.selectedStart = null;
    self.selectedEnd = null;
    self.rangePhase = 0;
    self.view = 'days';
    self.cal = null;
    self.currentYear = new Date().getFullYear();
    self.currentMonth = new Date().getMonth();

    const def = parseDate(input.dataset.defaultDate || null);
    if (def) {
      self.selectedStart = def;
      input.value = formatDate(def, self.fmt);
    }

    self.cal = document.createElement('div');
    self.cal.className = 'nt-dp-calendar';
    self.cal.style.display = 'none';
    if (document.documentElement.classList.contains('dark')) self.cal.classList.add('dark');
    document.body.appendChild(self.cal);

    self.open = function () {
      if (input.disabled) return;
      const isDark = document.documentElement.classList.contains('dark');
      self.cal.classList.toggle('dark', isDark);
      const ref = self.selectedStart || new Date();
      self.currentYear = ref.getFullYear();
      self.currentMonth = ref.getMonth();
      self.view = 'days';
      self.render();
      self.cal.style.display = 'block';
      self.positionCal();
      input.classList.add('nt-dp-active');
    };

    self.close = function () {
      self.cal.style.display = 'none';
      input.classList.remove('nt-dp-active');
      self.view = 'days';
    };

    self.positionCal = function () {
      const r = input.getBoundingClientRect();
      const cw = self.cal.offsetWidth || 288;
      const ch = self.cal.offsetHeight || 300;
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      let top = r.bottom + 4;
      let left = r.left;
      if (left + cw > vw - 8) left = vw - cw - 8;
      if (top + ch > vh - 8) top = r.top - ch - 4;
      if (left < 8) left = 8;
      self.cal.style.top = top + 'px';
      self.cal.style.left = left + 'px';
    };

    self.render = function () {
      if (self.view === 'days') self.renderDays();
      if (self.view === 'months') self.renderMonthPicker();
      if (self.view === 'years') self.renderYearPicker();
    };

    self.renderDays = function () {
      const y = self.currentYear, m = self.currentMonth;
      const firstDay = new Date(y, m, 1).getDay();
      const daysInMonth = new Date(y, m + 1, 0).getDate();
      const daysInPrev = new Date(y, m, 0).getDate();
      const today = startOfDay(new Date());

      const wdOrder = [1, 2, 3, 4, 5, 6, 0];
      const wdHtml = wdOrder.map(d => `<div class="nt-dp-wd">${DAYS_SHORT[d]}</div>`).join('');

      const offset = (firstDay === 0) ? 6 : firstDay - 1;

      let cells = '';
      for (let i = offset; i > 0; i--) {
        const day = daysInPrev - i + 1;
        cells += `<button class="nt-dp-day nt-dp-day--other" data-prev="${day}" type="button">${day}</button>`;
      }
      for (let day = 1; day <= daysInMonth; day++) {
        const d = new Date(y, m, day);
        const sd = startOfDay(d);
        let cls = 'nt-dp-day';
        if (sameDay(sd, today)) cls += ' nt-dp-day--today';
        if (sameDay(sd, self.selectedStart)) cls += ' nt-dp-day--selected nt-dp-day--range-start';
        if (self.mode === 'range' && self.selectedEnd) {
          if (sameDay(sd, self.selectedEnd)) cls += ' nt-dp-day--selected nt-dp-day--range-end';
          if (sd > self.selectedStart && sd < self.selectedEnd) cls += ' nt-dp-day--in-range';
        }
        const disabled = (self.minDate && sd < self.minDate) || (self.maxDate && sd > self.maxDate);
        if (disabled) cls += ' nt-dp-day--disabled';
        cells += `<button class="${cls}" data-date="${y}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}" type="button"${disabled ? ' disabled' : ''}>${day}</button>`;
      }
      const totalCells = offset + daysInMonth;
      const remainder = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
      for (let day = 1; day <= remainder; day++) {
        cells += `<button class="nt-dp-day nt-dp-day--other" data-next="${day}" type="button">${day}</button>`;
      }

      self.cal.innerHTML = `
      <div class="nt-dp-header">
        <button class="nt-dp-nav-btn" id="nt-dp-prev" type="button"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="nt-dp-header-center">
          <button class="nt-dp-month-btn" id="nt-dp-month-btn" type="button">${MONTHS_LONG[m]}</button>
          <button class="nt-dp-year-btn"  id="nt-dp-year-btn"  type="button">${y}</button>
        </div>
        <button class="nt-dp-nav-btn" id="nt-dp-next" type="button"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="nt-dp-weekdays">${wdHtml}</div>
      <div class="nt-dp-days" id="nt-dp-days-grid">${cells}</div>
    `;

      self.cal.querySelector('#nt-dp-prev').addEventListener('click', () => {
        self.currentMonth--;
        if (self.currentMonth < 0) { self.currentMonth = 11; self.currentYear--; }
        self.renderDays();
      });
      self.cal.querySelector('#nt-dp-next').addEventListener('click', () => {
        self.currentMonth++;
        if (self.currentMonth > 11) { self.currentMonth = 0; self.currentYear++; }
        self.renderDays();
      });
      self.cal.querySelector('#nt-dp-month-btn').addEventListener('click', () => {
        self.view = 'months'; self.render();
      });
      self.cal.querySelector('#nt-dp-year-btn').addEventListener('click', () => {
        self.view = 'years'; self.render();
      });
      self.cal.querySelector('#nt-dp-days-grid').addEventListener('click', e => {
        const btn = e.target.closest('.nt-dp-day');
        if (!btn || btn.disabled || btn.classList.contains('nt-dp-day--other') || btn.classList.contains('nt-dp-day--disabled')) return;
        const picked = parseDate(btn.dataset.date);
        self.selectDate(picked);
      });
    };

    self.selectDate = function (picked) {
      if (self.mode === 'single') {
        self.selectedStart = picked;
        input.value = formatDate(picked, self.fmt);
        input.dispatchEvent(new Event('change', { bubbles: true }));
        self.close();
      } else {
        if (self.rangePhase === 0 || (self.selectedStart && self.selectedEnd)) {
          self.selectedStart = picked;
          self.selectedEnd = null;
          self.rangePhase = 1;
          input.value = formatDate(picked, self.fmt) + ' — …';
          self.renderDays();
        } else {
          if (picked < self.selectedStart) {
            self.selectedEnd = self.selectedStart;
            self.selectedStart = picked;
          } else {
            self.selectedEnd = picked;
          }
          self.rangePhase = 0;
          input.value = formatDate(self.selectedStart, self.fmt) + ' — ' + formatDate(self.selectedEnd, self.fmt);
          input.dispatchEvent(new Event('change', { bubbles: true }));
          self.close();
        }
      }
    };

    self.renderMonthPicker = function () {
      const items = MONTHS_LONG.map((name, idx) => {
        const active = idx === self.currentMonth ? ' nt-dp-month-item--active' : '';
        return `<button class="nt-dp-month-item${active}" data-m="${idx}" type="button">${MONTHS_SHORT[idx]}</button>`;
      }).join('');
      self.cal.innerHTML = `
      <div class="nt-dp-header">
        <button class="nt-dp-nav-btn" id="nt-dp-prev" type="button"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="nt-dp-header-center">
          <button class="nt-dp-year-btn" id="nt-dp-year-btn" type="button">${self.currentYear}</button>
        </div>
        <button class="nt-dp-nav-btn" id="nt-dp-next" type="button"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="nt-dp-month-grid">${items}</div>
    `;
      self.cal.querySelector('#nt-dp-prev').addEventListener('click', () => { self.currentYear--; self.renderMonthPicker(); });
      self.cal.querySelector('#nt-dp-next').addEventListener('click', () => { self.currentYear++; self.renderMonthPicker(); });
      self.cal.querySelector('#nt-dp-year-btn').addEventListener('click', () => { self.view = 'years'; self.render(); });
      self.cal.querySelectorAll('.nt-dp-month-item').forEach(btn => {
        btn.addEventListener('click', () => {
          self.currentMonth = parseInt(btn.dataset.m);
          self.view = 'days';
          self.renderDays();
        });
      });
    };

    self.renderYearPicker = function () {
      const base = Math.floor(self.currentYear / 20) * 20;
      let items = '';
      for (let yr = base; yr < base + 20; yr++) {
        const active = yr === self.currentYear ? ' nt-dp-year-item--active' : '';
        items += `<button class="nt-dp-year-item${active}" data-yr="${yr}" type="button">${yr}</button>`;
      }
      self.cal.innerHTML = `
      <div class="nt-dp-header">
        <button class="nt-dp-nav-btn" id="nt-dp-prev" type="button"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="nt-dp-header-center">
          <span style="font-size:14px;font-weight:600;color:#111827" class="dark-year-label">${base}–${base + 19}</span>
        </div>
        <button class="nt-dp-nav-btn" id="nt-dp-next" type="button"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="nt-dp-year-grid">${items}</div>
    `;
      const lbl = self.cal.querySelector('.dark-year-label');
      if (document.documentElement.classList.contains('dark') || self.cal.classList.contains('dark'))
        lbl.style.color = '#f1f5f9';
      self.cal.querySelector('#nt-dp-prev').addEventListener('click', () => { self.currentYear -= 20; self.renderYearPicker(); });
      self.cal.querySelector('#nt-dp-next').addEventListener('click', () => { self.currentYear += 20; self.renderYearPicker(); });
      self.cal.querySelectorAll('.nt-dp-year-item').forEach(btn => {
        btn.addEventListener('click', () => {
          self.currentYear = parseInt(btn.dataset.yr);
          self.view = 'months';
          self.renderMonthPicker();
        });
      });
      const activeBtn = self.cal.querySelector('.nt-dp-year-item--active');
      if (activeBtn) setTimeout(() => activeBtn.scrollIntoView({ block: 'nearest' }), 0);
    };

    input.addEventListener('click', () => {
      if (self.cal.style.display === 'none') self.open(); else self.close();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Escape') self.close();
    });

    document.addEventListener('mousedown', function handler(e) {
      if (!self.cal.contains(e.target) && e.target !== input) {
        self.close();
      }
    }, true);

    window.addEventListener('scroll', () => { if (self.cal.style.display !== 'none') self.positionCal(); }, true);
    window.addEventListener('resize', () => { if (self.cal.style.display !== 'none') self.positionCal(); });

    input._akdp = self;
  }

  window._AkDatepicker = AkDatepicker;

})(); // end IIFE datepicker

function initDatepicker(root) {
  root = root || document;
  root.querySelectorAll('input.nt-datepicker').forEach(el => {
    if (el._akdp) return;
    if (el.disabled) return;
    new window._AkDatepicker(el);
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initTimepicker  —  Vanilla JS Time Spinner

   Usage (HTML):
     <input type="text" class="nt-timepicker"
            name="start_time"
            data-default-time="09:00"
            data-minute-increment="5"
            placeholder="Pilih waktu…">

   data-* attributes:
     data-default-time      — pre-selected time "HH:MM"
     data-minute-increment  — minute step, default 1

   JS API:
     el._aktp             — instance object
     window.NetraUI.initTimepicker(root);
   ═══════════════════════════════════════════════════════ */

(function () {

  const PRESETS = ['08:00', '09:00', '10:00', '12:00', '13:00', '17:00', '18:00', '20:00'];

  function pad2(n) { return String(n).padStart(2, '0'); }

  function AkTimepicker(input) {
    const self = this;
    self.input = input;
    self.minInc = parseInt(input.dataset.minuteIncrement || '1');
    self.hour = 0;
    self.minute = 0;
    self.panel = null;
    self._holdTimer = null;
    self._holdInterval = null;

    const def = input.dataset.defaultTime || (input.value ? input.value.trim() : '');
    if (def && /^\d{1,2}:\d{2}$/.test(def)) {
      const parts = def.split(':');
      self.hour = parseInt(parts[0]) % 24;
      self.minute = Math.round(parseInt(parts[1]) / self.minInc) * self.minInc % 60;
      input.value = pad2(self.hour) + ':' + pad2(self.minute);
    }

    self.panel = document.createElement('div');
    self.panel.className = 'nt-tp-panel';
    self.panel.style.display = 'none';
    if (document.documentElement.classList.contains('dark')) self.panel.classList.add('dark');
    document.body.appendChild(self.panel);

    self.open = function () {
      if (input.disabled) return;
      const isDark = document.documentElement.classList.contains('dark');
      self.panel.classList.toggle('dark', isDark);
      self.renderPanel();
      self.panel.style.display = 'block';
      self.positionPanel();
      input.classList.add('nt-tp-active');
    };

    self.close = function () {
      self.panel.style.display = 'none';
      input.classList.remove('nt-tp-active');
      self._stopHold();
    };

    self.positionPanel = function () {
      const r = input.getBoundingClientRect();
      const pw = self.panel.offsetWidth || 260;
      const ph = self.panel.offsetHeight || 280;
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      let top = r.bottom + 4;
      let left = r.left;
      if (left + pw > vw - 8) left = vw - pw - 8;
      if (top + ph > vh - 8) top = r.top - ph - 4;
      if (left < 8) left = 8;
      self.panel.style.top = top + 'px';
      self.panel.style.left = left + 'px';
    };

    self.applyValue = function () {
      input.value = pad2(self.hour) + ':' + pad2(self.minute);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    self.renderPanel = function () {
      const activePreset = PRESETS.find(p => {
        const [ph, pm] = p.split(':'); return parseInt(ph) === self.hour && parseInt(pm) === self.minute;
      });
      const presetsHtml = PRESETS.map(p => {
        const active = p === activePreset ? ' nt-tp-preset--active' : '';
        return `<button class="nt-tp-preset${active}" data-preset="${p}" type="button">${p}</button>`;
      }).join('');

      self.panel.innerHTML = `
      <div class="nt-tp-spinners">
        <div class="nt-tp-spinner">
          <button class="nt-tp-spin-btn" data-action="up" data-unit="hour" type="button"><i class="fa-solid fa-chevron-up"></i></button>
          <input class="nt-tp-input" id="nt-tp-hour" type="number" min="0" max="23" value="${pad2(self.hour)}" inputmode="numeric"/>
          <button class="nt-tp-spin-btn" data-action="down" data-unit="hour" type="button"><i class="fa-solid fa-chevron-down"></i></button>
          <span class="nt-tp-label">JAM</span>
        </div>
        <span class="nt-tp-sep">:</span>
        <div class="nt-tp-spinner">
          <button class="nt-tp-spin-btn" data-action="up" data-unit="minute" type="button"><i class="fa-solid fa-chevron-up"></i></button>
          <input class="nt-tp-input" id="nt-tp-minute" type="number" min="0" max="59" value="${pad2(self.minute)}" inputmode="numeric"/>
          <button class="nt-tp-spin-btn" data-action="down" data-unit="minute" type="button"><i class="fa-solid fa-chevron-down"></i></button>
          <span class="nt-tp-label">MENIT</span>
        </div>
      </div>
      <div class="nt-tp-presets">${presetsHtml}</div>
      <div class="nt-tp-footer">
        <button class="nt-tp-btn-now" id="nt-tp-now" type="button">Sekarang</button>
        <button class="nt-tp-btn-apply" id="nt-tp-apply" type="button">Terapkan</button>
      </div>
    `;

      self.panel.querySelectorAll('.nt-tp-spin-btn').forEach(btn => {
        btn.addEventListener('mousedown', e => { e.preventDefault(); self._startHold(btn.dataset.action, btn.dataset.unit); });
        btn.addEventListener('touchstart', e => { e.preventDefault(); self._startHold(btn.dataset.action, btn.dataset.unit); }, { passive: false });
        btn.addEventListener('mouseup', () => self._stopHold());
        btn.addEventListener('mouseleave', () => self._stopHold());
        btn.addEventListener('touchend', () => self._stopHold());
      });

      const hInput = self.panel.querySelector('#nt-tp-hour');
      const mInput = self.panel.querySelector('#nt-tp-minute');

      hInput.addEventListener('input', () => {
        let v = parseInt(hInput.value);
        if (isNaN(v)) return;
        self.hour = Math.max(0, Math.min(23, v));
        hInput.value = pad2(self.hour);
      });
      mInput.addEventListener('input', () => {
        let v = parseInt(mInput.value);
        if (isNaN(v)) return;
        self.minute = Math.max(0, Math.min(59, v));
        mInput.value = pad2(self.minute);
      });

      hInput.addEventListener('keydown', e => {
        if (e.key === 'ArrowUp') { e.preventDefault(); self._step('up', 'hour'); self._syncInputs(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); self._step('down', 'hour'); self._syncInputs(); }
      });
      mInput.addEventListener('keydown', e => {
        if (e.key === 'ArrowUp') { e.preventDefault(); self._step('up', 'minute'); self._syncInputs(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); self._step('down', 'minute'); self._syncInputs(); }
      });

      self.panel.querySelectorAll('.nt-tp-preset').forEach(btn => {
        btn.addEventListener('click', () => {
          const [ph, pm] = btn.dataset.preset.split(':');
          self.hour = parseInt(ph);
          self.minute = parseInt(pm);
          self.applyValue();
          self.close();
        });
      });

      self.panel.querySelector('#nt-tp-now').addEventListener('click', () => {
        const now = new Date();
        self.hour = now.getHours();
        self.minute = Math.round(now.getMinutes() / self.minInc) * self.minInc % 60;
        self.applyValue();
        self.close();
      });

      self.panel.querySelector('#nt-tp-apply').addEventListener('click', () => {
        self.applyValue();
        self.close();
      });
    };

    self._syncInputs = function () {
      const h = self.panel.querySelector('#nt-tp-hour');
      const m = self.panel.querySelector('#nt-tp-minute');
      if (h) h.value = pad2(self.hour);
      if (m) m.value = pad2(self.minute);
    };

    self._step = function (dir, unit) {
      if (unit === 'hour') {
        self.hour = (self.hour + (dir === 'up' ? 1 : -1) + 24) % 24;
      } else {
        const inc = self.minInc;
        self.minute = (self.minute + (dir === 'up' ? inc : -inc) + 60) % 60;
      }
    };

    self._startHold = function (dir, unit) {
      self._step(dir, unit);
      self._syncInputs();
      self._stopHold();
      self._holdTimer = setTimeout(() => {
        self._holdInterval = setInterval(() => {
          self._step(dir, unit);
          self._syncInputs();
        }, 80);
      }, 350);
    };

    self._stopHold = function () {
      clearTimeout(self._holdTimer);
      clearInterval(self._holdInterval);
    };

    input.addEventListener('click', () => {
      if (self.panel.style.display === 'none') self.open(); else self.close();
    });
    input.addEventListener('keydown', e => { if (e.key === 'Escape') self.close(); });

    document.addEventListener('mousedown', function (e) {
      if (!self.panel.contains(e.target) && e.target !== input) self.close();
    }, true);

    window.addEventListener('scroll', () => { if (self.panel.style.display !== 'none') self.positionPanel(); }, true);
    window.addEventListener('resize', () => { if (self.panel.style.display !== 'none') self.positionPanel(); });

    input._aktp = self;
  }

  window._AkTimepicker = AkTimepicker;

})(); // end IIFE timepicker

function initTimepicker(root) {
  root = root || document;
  root.querySelectorAll('input.nt-timepicker').forEach(el => {
    if (el._aktp) return;
    if (el.disabled) return;
    new window._AkTimepicker(el);
  });
}


/* ═══════════════════════════════════════════════════════
   NetraUI.initFileUpload  —  Vanilla JS File Upload
   STATIC HTML MODE — JS hanya attach event listeners,
   HTML ditulis secara statis di Blade/HTML.

   Markup yang diperlukan (WAJIB ada di dalam .nt-fileupload):
   ─────────────────────────────────────────────────────────
   <div class="nt-fileupload"
        data-max-size="5"
        data-max-files="3">
     <div class="nt-fu-zone">
       <input type="file" accept="image/*" multiple />
       <i class="nt-fu-icon fa-solid fa-cloud-arrow-up"></i>
       <p class="nt-fu-label"><span>Klik untuk upload</span> atau seret file ke sini</p>
       <p class="nt-fu-hint">JPG, PNG · Maks. 5 MB · Hingga 3 file</p>
     </div>
     <div class="nt-fu-list"></div>
   </div>

   Disabled:
     Tambahkan class "nt-fu-disabled" pada .nt-fu-zone
     dan attribute disabled pada <input type="file">

   data-* attributes on .nt-fileupload:
     data-max-size    — max file size in MB per file (default 10)
     data-max-files   — max number of files (default 1)

   JS API:
     container._akfu  — instance object
     window.NetraUI.initFileUpload(root);
   ═══════════════════════════════════════════════════════ */

(function () {

  const FILE_ICONS = {
    'image': 'fa-file-image',
    'video': 'fa-file-video',
    'audio': 'fa-file-audio',
    'application/pdf': 'fa-file-pdf',
    'application/zip': 'fa-file-zipper',
    'application/x-zip': 'fa-file-zipper',
    'text': 'fa-file-lines',
    'default': 'fa-file',
  };

  function getIcon(type) {
    if (!type) return FILE_ICONS.default;
    if (type.startsWith('image/')) return FILE_ICONS.image;
    if (type.startsWith('video/')) return FILE_ICONS.video;
    if (type.startsWith('audio/')) return FILE_ICONS.audio;
    if (type.startsWith('text/')) return FILE_ICONS.text;
    return FILE_ICONS[type] || FILE_ICONS.default;
  }

  function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  }

  function AkFileUpload(container) {
    const self = this;
    self.container = container;
    self.maxSizeMB = parseFloat(container.dataset.maxSize || '10');
    self.maxFiles = parseInt(container.dataset.maxFiles || '1');
    self.files = [];

    /* Gunakan elemen statis dari HTML */
    const zone = container.querySelector('.nt-fu-zone');
    const list = container.querySelector('.nt-fu-list');
    const input = zone ? zone.querySelector('input[type="file"]') : null;

    if (!zone || !list || !input) {
      console.warn('[NetraUI] .nt-fileupload: pastikan markup .nt-fu-zone, .nt-fu-list, dan input[type="file"] ada di dalam container.', container);
      return;
    }

    self.disabled = input.disabled || zone.classList.contains('nt-fu-disabled');

    /* Drag events */
    zone.addEventListener('dragover', e => { e.preventDefault(); if (!self.disabled) zone.classList.add('nt-fu-dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('nt-fu-dragover'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('nt-fu-dragover');
      if (!self.disabled) self.handleFiles(e.dataTransfer.files);
    });

    /* Click via native input */
    input.addEventListener('change', () => self.handleFiles(input.files));

    self.handleFiles = function (fileList) {
      const remaining = self.maxFiles - self.files.length;
      const toAdd = Array.from(fileList).slice(0, remaining);
      toAdd.forEach(file => {
        const errSize = file.size > self.maxSizeMB * 1024 * 1024;
        self.files.push({ file, error: errSize ? 'File terlalu besar' : null, progress: 0 });
      });
      self.renderList();
      // Simulate progress for demo
      self.files.forEach((entry, idx) => {
        if (entry.error) return;
        if (entry.progress >= 100) return;
        let prog = 0;
        const iv = setInterval(() => {
          prog = Math.min(prog + Math.random() * 25 + 10, 100);
          entry.progress = prog;
          const bar = list.querySelector(`[data-idx="${idx}"] .nt-fu-progress-bar`);
          if (bar) bar.style.width = prog + '%';
          if (prog >= 100) {
            clearInterval(iv);
            const status = list.querySelector(`[data-idx="${idx}"] .nt-fu-status`);
            if (status) { status.textContent = 'Selesai'; status.className = 'nt-fu-status nt-fu-status--ok'; }
            const pbar = list.querySelector(`[data-idx="${idx}"] .nt-fu-progress`);
            if (pbar) pbar.style.display = 'none';
          }
        }, 120);
      });
      input.value = '';
      if (self.files.length >= self.maxFiles) {
        input.disabled = true;
        zone.classList.add('nt-fu-disabled');
      }
    };

    self.renderList = function () {
      list.innerHTML = '';
      self.files.forEach((entry, idx) => {
        const isImage = entry.file.type.startsWith('image/');
        const iconCls = getIcon(entry.file.type);
        const thumbId = `nt-fu-thumb-${idx}-${Date.now()}`;

        const item = document.createElement('div');
        item.className = 'nt-fu-item';
        item.dataset.idx = idx;

        if (entry.error) {
          item.innerHTML = `
          <div class="nt-fu-file-icon"><i class="fa-solid ${iconCls}"></i></div>
          <div class="nt-fu-info">
            <div class="nt-fu-name">${entry.file.name}</div>
            <div class="nt-fu-size">${formatBytes(entry.file.size)}</div>
          </div>
          <span class="nt-fu-status nt-fu-status--err">${entry.error}</span>
          <button class="nt-fu-remove" data-remove="${idx}" type="button"><i class="fa-solid fa-xmark"></i></button>
        `;
        } else {
          item.innerHTML = `
          ${isImage
              ? `<img class="nt-fu-thumb" id="${thumbId}" src="" alt="${entry.file.name}"/>`
              : `<div class="nt-fu-file-icon"><i class="fa-solid ${iconCls}"></i></div>`}
          <div class="nt-fu-info">
            <div class="nt-fu-name">${entry.file.name}</div>
            <div class="nt-fu-size">${formatBytes(entry.file.size)}</div>
            <div class="nt-fu-progress"><div class="nt-fu-progress-bar" style="width:${entry.progress}%"></div></div>
          </div>
          ${entry.progress >= 100
              ? `<span class="nt-fu-status nt-fu-status--ok">Selesai</span>`
              : `<span class="nt-fu-status" style="font-size:10.5px;color:#9ca3af">${Math.round(entry.progress)}%</span>`}
          <button class="nt-fu-remove" data-remove="${idx}" type="button"><i class="fa-solid fa-xmark"></i></button>
        `;
          if (isImage) {
            const reader = new FileReader();
            reader.onload = e => {
              const img = document.getElementById(thumbId);
              if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(entry.file);
          }
          if (entry.progress >= 100) {
            const pbar = item.querySelector('.nt-fu-progress');
            if (pbar) pbar.style.display = 'none';
          }
        }
        list.appendChild(item);
      });

      list.querySelectorAll('.nt-fu-remove').forEach(btn => {
        btn.addEventListener('click', () => {
          const i = parseInt(btn.dataset.remove);
          self.files.splice(i, 1);
          if (self.files.length < self.maxFiles && !self.disabled) {
            input.disabled = false;
            zone.classList.remove('nt-fu-disabled');
          }
          self.renderList();
        });
      });
    };

    container._akfu = self;
  }

  window._AkFileUpload = AkFileUpload;

})(); // end IIFE fileupload

function initFileUpload(root) {
  root = root || document;
  root.querySelectorAll('.nt-fileupload').forEach(el => {
    if (el._akfu) return;
    new window._AkFileUpload(el);
  });
}

/* ═══════════════════════════════════════════════════════
   NetraUI.initColorPicker  —  Vanilla JS Color Picker
   STATIC HTML MODE — JS hanya attach event listeners,
   HTML ditulis secara statis di Blade/HTML.

   Markup yang diperlukan (WAJIB ada di dalam .nt-colorpicker):
   ─────────────────────────────────────────────────────────
   <div class="nt-colorpicker" data-default-color="#2d5aff">
     <div class="nt-colorpicker-wrap">
       <div class="nt-cp-swatch">
         <div class="nt-cp-swatch-inner" style="background:#2d5aff"></div>
       </div>
       <input class="nt-cp-text" type="text" value="#2d5aff"
              placeholder="#000000" readonly />
       <i class="nt-cp-icon fa-solid fa-chevron-down"></i>
     </div>
   </div>

   Disabled:
     Tambahkan class "nt-cp-disabled" pada .nt-colorpicker-wrap

   data-* attributes:
     data-default-color  — initial color (hex, default "#2d5aff")
     data-disabled       — "true" to disable

   JS API:
     container._akcp  — instance object
     window.NetraUI.initColorPicker(root);
   ═══════════════════════════════════════════════════════ */

(function () {

  const PALETTE = [
    '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
    '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6',
    '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
    '#64748b', '#475569', '#334155', '#1e293b', '#000000',
  ];

  /* HSV → RGB */
  function hsv2rgb(h, s, v) {
    h = h % 360;
    const c = v * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = v - c;
    let r = 0, g = 0, b = 0;
    if (h < 60) { r = c; g = x; } else if (h < 120) { r = x; g = c; }
    else if (h < 180) { g = c; b = x; } else if (h < 240) { g = x; b = c; }
    else if (h < 300) { r = x; b = c; } else { r = c; b = x; }
    return [Math.round((r + m) * 255), Math.round((g + m) * 255), Math.round((b + m) * 255)];
  }
  /* RGB → hex */
  function rgb2hex(r, g, b) { return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join(''); }
  /* hex → HSV */
  function hex2hsv(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.slice(0, 2), 16) / 255, g = parseInt(hex.slice(2, 4), 16) / 255, b = parseInt(hex.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
    let h = 0, s = max === 0 ? 0 : d / max, v = max;
    if (d !== 0) {
      if (max === r) h = ((g - b) / d + 6) % 6 * 60;
      else if (max === g) h = ((b - r) / d + 2) * 60;
      else h = ((r - g) / d + 4) * 60;
    }
    return [h, s, v];
  }
  function validHex(h) { return /^#[0-9a-fA-F]{6}$/.test(h); }

  function AkColorPicker(container) {
    const self = this;
    self.container = container;
    self.disabled = container.dataset.disabled === 'true';
    self.panel = null;
    self.canvas = null;
    self.ctx = null;

    /* Baca elemen statis dari HTML */
    const wrap = container.querySelector('.nt-colorpicker-wrap');
    const swInner = container.querySelector('.nt-cp-swatch-inner');
    const textInput = container.querySelector('.nt-cp-text');

    if (!wrap || !swInner || !textInput) {
      console.warn('[NetraUI] .nt-colorpicker: pastikan markup .nt-colorpicker-wrap, .nt-cp-swatch-inner, dan .nt-cp-text ada di dalam container.', container);
      return;
    }

    /* Ambil warna dari elemen statis atau data-default-color */
    const initHex = container.dataset.defaultColor || textInput.value || '#2d5aff';
    const [ih, is, iv] = hex2hsv(validHex(initHex) ? initHex : '#2d5aff');
    self.hue = ih;
    self.sat = is;
    self.val = iv;
    self.hex = rgb2hex(...hsv2rgb(self.hue, self.sat, self.val));
    self.pendingHex = self.hex;

    /* Sync tampilan awal jika nilai berbeda dari data-default-color */
    swInner.style.background = self.hex;
    textInput.value = self.hex;

    /* Build panel (tetap dinamis karena canvas + spectrum perlu render) */
    self.panel = document.createElement('div');
    self.panel.className = 'nt-cp-panel';
    self.panel.style.display = 'none';
    document.body.appendChild(self.panel);

    self.open = function () {
      if (self.disabled) return;
      const isDark = document.documentElement.classList.contains('dark');
      self.panel.classList.toggle('dark', isDark);
      self.pendingHex = self.hex;
      const [h, s, v] = hex2hsv(self.hex);
      self.hue = h; self.sat = s; self.val = v;
      self.renderPanel();
      self.panel.style.display = 'block';
      self.positionPanel();
      wrap.classList.add('nt-cp-active');
    };

    self.close = function () {
      self.panel.style.display = 'none';
      wrap.classList.remove('nt-cp-active');
    };

    self.applyColor = function (hex) {
      self.hex = hex;
      swInner.style.background = hex;
      textInput.value = hex;
      container.dispatchEvent(new CustomEvent('ak:color', { detail: { hex }, bubbles: true }));
    };

    self.positionPanel = function () {
      const r = container.getBoundingClientRect();
      const pw = self.panel.offsetWidth || 240;
      const ph = self.panel.offsetHeight || 360;
      const vw = window.innerWidth, vh = window.innerHeight;
      let top = r.bottom + 4, left = r.left;
      if (left + pw > vw - 8) left = vw - pw - 8;
      if (top + ph > vh - 8) top = r.top - ph - 4;
      if (left < 8) left = 8;
      self.panel.style.top = top + 'px';
      self.panel.style.left = left + 'px';
    };

    /* ── Render panel (spectrum panel tetap dinamis karena canvas) ── */
    self.renderPanel = function () {
      const swatchesHtml = PALETTE.map(c => {
        const active = c.toLowerCase() === self.pendingHex.toLowerCase() ? ' nt-cp-sw--active' : '';
        return `<div class="nt-cp-sw${active}" style="background:${c}" data-color="${c}" title="${c}"></div>`;
      }).join('');

      self.panel.innerHTML = `
      <div class="nt-cp-spectrum" id="nt-cp-spectrum">
        <canvas id="nt-cp-canvas"></canvas>
        <div class="nt-cp-cursor" id="nt-cp-cursor"></div>
      </div>
      <div class="nt-cp-hue" id="nt-cp-hue">
        <div class="nt-cp-hue-thumb" id="nt-cp-hue-thumb"></div>
      </div>
      <div class="nt-cp-inputs">
        <div style="flex:1">
          <input class="nt-cp-hex-input" id="nt-cp-hex-inp" type="text" maxlength="7" value="${self.pendingHex}" spellcheck="false"/>
          <div class="nt-cp-hex-label">HEX</div>
        </div>
      </div>
      <div class="nt-cp-swatches">${swatchesHtml}</div>
      <div class="nt-cp-footer">
        <button class="nt-cp-btn-clear" id="nt-cp-clear" type="button">Reset</button>
        <button class="nt-cp-btn-apply" id="nt-cp-apply" type="button">Terapkan</button>
      </div>
    `;

      self.canvas = self.panel.querySelector('#nt-cp-canvas');
      self.ctx = self.canvas.getContext('2d');
      const spectrum = self.panel.querySelector('#nt-cp-spectrum');
      const cursor = self.panel.querySelector('#nt-cp-cursor');
      const hueBar = self.panel.querySelector('#nt-cp-hue');
      const hueThumb = self.panel.querySelector('#nt-cp-hue-thumb');
      const hexInp = self.panel.querySelector('#nt-cp-hex-inp');

      function drawSpectrum() {
        const w = self.canvas.width = spectrum.offsetWidth || 216;
        const h = self.canvas.height = spectrum.offsetHeight || 130;
        const [r, g, b] = hsv2rgb(self.hue, 1, 1);
        const gradH = self.ctx.createLinearGradient(0, 0, w, 0);
        gradH.addColorStop(0, '#fff');
        gradH.addColorStop(1, `rgb(${r},${g},${b})`);
        self.ctx.fillStyle = gradH;
        self.ctx.fillRect(0, 0, w, h);
        const gradV = self.ctx.createLinearGradient(0, 0, 0, h);
        gradV.addColorStop(0, 'rgba(0,0,0,0)');
        gradV.addColorStop(1, '#000');
        self.ctx.fillStyle = gradV;
        self.ctx.fillRect(0, 0, w, h);
        cursor.style.left = (self.sat * w) + 'px';
        cursor.style.top = ((1 - self.val) * h) + 'px';
      }

      function drawHue() {
        hueThumb.style.left = (self.hue / 360 * 100) + '%';
      }

      function syncHex() {
        self.pendingHex = rgb2hex(...hsv2rgb(self.hue, self.sat, self.val));
        hexInp.value = self.pendingHex;
        self.panel.querySelectorAll('.nt-cp-sw').forEach(sw => {
          sw.classList.toggle('nt-cp-sw--active', sw.dataset.color.toLowerCase() === self.pendingHex.toLowerCase());
        });
      }

      drawSpectrum();
      drawHue();

      function onSpectrumMove(e) {
        const rect = spectrum.getBoundingClientRect();
        const cx = e.touches ? e.touches[0].clientX : e.clientX;
        const cy = e.touches ? e.touches[0].clientY : e.clientY;
        self.sat = Math.max(0, Math.min(1, (cx - rect.left) / rect.width));
        self.val = Math.max(0, Math.min(1, 1 - (cy - rect.top) / rect.height));
        drawSpectrum();
        syncHex();
      }
      spectrum.addEventListener('mousedown', e => { e.preventDefault(); onSpectrumMove(e); document.addEventListener('mousemove', onSpectrumMove); document.addEventListener('mouseup', () => document.removeEventListener('mousemove', onSpectrumMove), { once: true }); });
      spectrum.addEventListener('touchstart', e => { e.preventDefault(); onSpectrumMove(e); document.addEventListener('touchmove', onSpectrumMove); document.addEventListener('touchend', () => document.removeEventListener('touchmove', onSpectrumMove), { once: true }); }, { passive: false });

      function onHueMove(e) {
        const rect = hueBar.getBoundingClientRect();
        const cx = e.touches ? e.touches[0].clientX : e.clientX;
        self.hue = Math.max(0, Math.min(360, (cx - rect.left) / rect.width * 360));
        drawHue();
        drawSpectrum();
        syncHex();
      }
      hueBar.addEventListener('mousedown', e => { e.preventDefault(); onHueMove(e); document.addEventListener('mousemove', onHueMove); document.addEventListener('mouseup', () => document.removeEventListener('mousemove', onHueMove), { once: true }); });
      hueBar.addEventListener('touchstart', e => { e.preventDefault(); onHueMove(e); document.addEventListener('touchmove', onHueMove); document.addEventListener('touchend', () => document.removeEventListener('touchmove', onHueMove), { once: true }); }, { passive: false });

      hexInp.addEventListener('input', () => {
        const v = hexInp.value.startsWith('#') ? hexInp.value : '#' + hexInp.value;
        if (validHex(v)) {
          const [h, s, val] = hex2hsv(v);
          self.hue = h; self.sat = s; self.val = val;
          self.pendingHex = v;
          drawSpectrum(); drawHue();
          self.panel.querySelectorAll('.nt-cp-sw').forEach(sw => {
            sw.classList.toggle('nt-cp-sw--active', sw.dataset.color.toLowerCase() === v.toLowerCase());
          });
        }
      });

      self.panel.querySelectorAll('.nt-cp-sw').forEach(sw => {
        sw.addEventListener('click', () => {
          const [h, s, v] = hex2hsv(sw.dataset.color);
          self.hue = h; self.sat = s; self.val = v;
          self.pendingHex = sw.dataset.color;
          drawSpectrum(); drawHue(); syncHex();
        });
      });

      self.panel.querySelector('#nt-cp-clear').addEventListener('click', () => {
        const initHex = container.dataset.defaultColor || '#2d5aff';
        const [h, s, v] = hex2hsv(validHex(initHex) ? initHex : '#2d5aff');
        self.hue = h; self.sat = s; self.val = v;
        self.pendingHex = rgb2hex(...hsv2rgb(h, s, v));
        drawSpectrum(); drawHue(); syncHex();
      });

      self.panel.querySelector('#nt-cp-apply').addEventListener('click', () => {
        self.applyColor(self.pendingHex);
        self.close();
      });
    };

    /* Toggle open/close */
    wrap.addEventListener('click', () => {
      if (self.panel.style.display === 'none') self.open(); else self.close();
    });

    document.addEventListener('mousedown', e => {
      if (!self.panel.contains(e.target) && !container.contains(e.target)) self.close();
    }, true);

    window.addEventListener('scroll', () => { if (self.panel.style.display !== 'none') self.positionPanel(); }, true);
    window.addEventListener('resize', () => { if (self.panel.style.display !== 'none') self.positionPanel(); });

    container._akcp = self;
  }

  window._AkColorPicker = AkColorPicker;

})(); // end IIFE colorpicker

function initColorPicker(root) {
  root = root || document;
  root.querySelectorAll('.nt-colorpicker').forEach(el => {
    if (el._akcp) return;
    new window._AkColorPicker(el);
  });
}


/* ═══════════════════════════════════════════════════════
   Auto-init on DOMContentLoaded
   ═══════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  if (typeof TomSelect !== 'undefined') initSelect(document);
  initDatepicker(document);
  initTimepicker(document);
  initFileUpload(document);
  initColorPicker(document);
});

/* ── Public API ── */
if (!window.NetraUI) window.NetraUI = {};
Object.assign(window.NetraUI, {
  initSelect,
  initDatepicker,
  initTimepicker,
  initFileUpload,
  initColorPicker,
});

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

/* ════════════════════════════════════════════════════════════
   BASIC TABLE — Static HTML + Data-Attribute driven
   ────────────────────────────────────────────────────────────
   Markup contract (data attributes):
   • [data-nt-basic-table]          — wrapper <div> pada toolbar+table+footer
   • [data-nt-page-size]            — jumlah baris per halaman (default 8)
   • [data-nt-search-input]         — <input> pencarian
   • [data-nt-check-all]            — <input type="checkbox"> select-all
   • [data-nt-selected-count]       — elemen teks counter pilihan
   • [data-nt-table-body]           — <tbody> yang berisi baris statis
   • [data-nt-pagination]           — kontainer tombol pagination
   • [data-nt-table-info]           — teks "Menampilkan x–y dari z"
   • [data-nt-sort-col]             — pada <th class="nt-th-sort">
   • [data-label]                   — pada setiap <td> (untuk responsive)
   • [data-sort-value]              — (opsional) pada <td> override nilai sort
   ════════════════════════════════════════════════════════════ */
function initBasicTable(root = document) {
  root.querySelectorAll('[data-nt-basic-table]').forEach(wrapper => {

    /* ── Config ── */
    const PAGE_SIZE = parseInt(wrapper.dataset.ntPageSize || '8', 10);

    /* ── Refs ── */
    const searchInput    = wrapper.querySelector('[data-nt-search-input]');
    const checkAll       = wrapper.querySelector('[data-nt-check-all]');
    const selectedCount  = wrapper.querySelector('[data-nt-selected-count]');
    const tbody          = wrapper.querySelector('[data-nt-table-body]');
    const paginationEl   = wrapper.querySelector('[data-nt-pagination]');
    const tableInfoEl    = wrapper.querySelector('[data-nt-table-info]');
    if (!tbody) return;

    /* ── State ── */
    let currentPage = 1;
    let filterText  = '';
    let sortCol     = null;
    let sortDir     = 'asc';

    /* ── Snapshot semua baris statis (sekali saja) ── */
    const allRows = Array.from(tbody.querySelectorAll('tr'));

    /* ── Helper: nilai sel untuk sort/search ── */
    function cellText(tr, col) {
      const td = tr.querySelectorAll('td')[col];
      if (!td) return '';
      return (td.dataset.sortValue || td.innerText || td.textContent || '').trim().toLowerCase();
    }

    /* ── Filter ── */
    function getFiltered() {
      if (!filterText) return allRows.slice();
      return allRows.filter(tr =>
        Array.from(tr.querySelectorAll('td')).some(td =>
          (td.dataset.sortValue || td.innerText || td.textContent || '')
            .toLowerCase().includes(filterText)
        )
      );
    }

    /* ── Render ── */
    function render() {
      let rows = getFiltered();
      const total      = rows.length;
      const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
      if (currentPage > totalPages) currentPage = 1;
      const start = (currentPage - 1) * PAGE_SIZE;
      const slice = rows.slice(start, start + PAGE_SIZE);

      /* Sembunyikan semua, tampilkan slice */
      allRows.forEach(tr => { tr.style.display = 'none'; tr.classList.remove('nt-row-selected'); });
      const cb_all = checkAll;
      if (cb_all) cb_all.checked = false;

      slice.forEach(tr => {
        tr.style.display = '';
        const cb = tr.querySelector('.row-check');
        if (cb) cb.checked = false;
      });

      /* Info teks */
      if (tableInfoEl) {
        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(start + PAGE_SIZE, total);
        tableInfoEl.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
      }

      /* Selected count reset */
      updateSelected();

      /* Pagination */
      renderPagination(totalPages);
    }

    /* ── Pagination ── */
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

      /* Bind klik pagination */
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

    /* ── Selected count ── */
    function updateSelected() {
      const count = wrapper.querySelectorAll('.row-check:checked').length;
      if (selectedCount) {
        selectedCount.textContent = count + ' dipilih';
        selectedCount.classList.toggle('hidden', count === 0);
      }
    }

    /* ── Row click toggle select ── */
    tbody.addEventListener('click', e => {
      const tr = e.target.closest('tr');
      if (!tr) return;
      /* Jika klik langsung di checkbox, biarkan native change event */
      if (e.target.matches('.row-check')) return;
      tr.classList.toggle('nt-row-selected');
      const cb = tr.querySelector('.row-check');
      if (cb) cb.checked = tr.classList.contains('nt-row-selected');
      updateSelected();
    });

    /* Checkbox row change */
    tbody.addEventListener('change', e => {
      if (!e.target.matches('.row-check')) return;
      e.target.closest('tr').classList.toggle('nt-row-selected', e.target.checked);
      updateSelected();
    });

    /* ── Check-all ── */
    if (checkAll) {
      checkAll.addEventListener('change', () => {
        const visible = Array.from(tbody.querySelectorAll('tr')).filter(tr => tr.style.display !== 'none');
        visible.forEach(tr => {
          const cb = tr.querySelector('.row-check');
          if (cb) { cb.checked = checkAll.checked; }
          tr.classList.toggle('nt-row-selected', checkAll.checked);
        });
        updateSelected();
      });
    }

    /* ── Search ── */
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        filterText  = searchInput.value.trim().toLowerCase();
        currentPage = 1;
        render();
      });
    }

    /* ── Sort ── */
    wrapper.querySelectorAll('.nt-th-sort').forEach(th => {
      th.addEventListener('click', () => {
        const col = th.dataset.ntSortCol;

        /* Reset semua header */
        wrapper.querySelectorAll('.nt-th-sort').forEach(t => t.classList.remove('asc', 'desc'));

        if (sortCol === col) {
          sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          sortCol = col;
          sortDir = 'asc';
        }
        th.classList.add(sortDir);

        /* Tentukan index kolom dari posisi th */
        const thIndex = Array.from(th.closest('tr').querySelectorAll('th')).indexOf(th);

        allRows.sort((a, b) => {
          const va = cellText(a, thIndex);
          const vb = cellText(b, thIndex);
          return sortDir === 'asc' ? va.localeCompare(vb, 'id') : vb.localeCompare(va, 'id');
        });

        /* Reorder DOM */
        allRows.forEach(tr => tbody.appendChild(tr));
        currentPage = 1;
        render();
      });
    });

    /* ── Init render ── */
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
