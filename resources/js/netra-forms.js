/* ═══════════════════════════════════════════════════════
   Netra UI — Forms JS (TomSelect, Datepicker, Timepicker, FileUpload, ColorPicker)  (v5, modular split)
   Membutuhkan netra-base.js. Inisialisasi window.NetraUI.
   ═══════════════════════════════════════════════════════ */

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
