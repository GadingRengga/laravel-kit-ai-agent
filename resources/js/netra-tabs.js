/* ═══════════════════════════════════════════════════════
   Netra UI — Tabs JS Engine
   Bagian dari Netra UI Design System (modular split)
   Wajib netra-base.css/js dimuat lebih dulu. Pasangan:
   netra-tabs.css (visual 4 varian: underline, pill, vertical,
   folder — semua pakai engine & markup yang sama).

   MODE NAVIGASI (data-mode di container [data-nt-tabs]):
     - "client" (default) : ganti panel di JS saja, URL tidak
                             berubah sama sekali.
     - "hash"              : URL berubah lewat location.hash
                              (#group=value), bisa di-share,
                              jalan dengan tombol back/forward,
                              tanpa reload halaman.
     - "query"             : URL berubah lewat ?param=value
                              (history.pushState), sama seperti
                              hash tapi pakai query string.
     - "href"              : tab adalah <a href="..."> asli
                              (halaman lain / anchor lain).
                              Browser yang navigasi (reload
                              normal); engine hanya menandai
                              tab aktif sesuai URL saat ini dan
                              menggerakkan indikator — sama
                              persis prinsipnya dengan
                              initNavActive() di netra-base.js.

   MARKUP:
   <div class="nt-tabs nt-tabs--underline" data-nt-tabs
        data-mode="hash" data-group="demo-1">
     <div class="nt-tabs-list">
       <button class="nt-tab" data-tab="overview">Overview</button>
       <button class="nt-tab" data-tab="usage">Usage</button>
       <span class="nt-tabs-indicator"></span>
     </div>
     <div class="nt-tabs-panels">
       <div class="nt-tab-panel" data-panel="overview">...</div>
       <div class="nt-tab-panel" data-panel="usage">...</div>
     </div>
   </div>

   Untuk style vertikal (nt-tabs--vertical) tambahkan
   data-orientation="vertical" di container supaya indikator
   dihitung berdasarkan offsetTop/offsetHeight, bukan
   offsetLeft/offsetWidth.

   API manual (dynamic content / modal / AJAX):
     window.NetraUI.initTabs(rootEl)
   ═══════════════════════════════════════════════════════ */

(function () {

  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function isVertical(root) {
    return root.dataset.orientation === 'vertical';
  }

  function positionIndicator(root, indicator, tab) {
    if (!indicator || !tab) return;
    if (isVertical(root)) {
      indicator.style.transform = 'translateY(' + tab.offsetTop + 'px)';
      indicator.style.height = tab.offsetHeight + 'px';
      indicator.style.width = '';
    } else {
      indicator.style.transform = 'translateX(' + tab.offsetLeft + 'px)';
      indicator.style.width = tab.offsetWidth + 'px';
      indicator.style.height = '';
    }
  }

  function readUrlValue(opts) {
    if (opts.mode === 'hash') {
      var h = location.hash.replace(/^#/, '');
      var parts = h.split('=');
      if (parts[0] === opts.group && parts[1]) return decodeURIComponent(parts[1]);
      return null;
    }
    if (opts.mode === 'query') {
      var url = new URL(location.href);
      return url.searchParams.get(opts.param);
    }
    return null;
  }

  function syncUrl(opts, value) {
    if (opts.mode === 'hash') {
      var hash = '#' + opts.group + '=' + encodeURIComponent(value);
      if (location.hash !== hash) history.pushState(null, '', hash);
    } else if (opts.mode === 'query') {
      var url = new URL(location.href);
      url.searchParams.set(opts.param, value);
      history.pushState(null, '', url);
    }
  }

  // Cocokkan tab <a href> dengan URL saat ini (dipakai mode "href").
  function findHrefMatch(tabs) {
    var currentFile = location.pathname.split('/').pop() || 'index.html';
    var currentFull = currentFile + location.search + location.hash;
    var match = null;
    tabs.forEach(function (t) {
      if (match) return;
      var href = t.getAttribute('href');
      if (!href) return;
      var hrefFile = href.split('/').pop();
      if (href === currentFull || hrefFile === currentFull || (hrefFile === currentFile && !location.search && !location.hash)) {
        match = t;
      }
    });
    return match;
  }

  function activateTab(ctx, targetTab, updateUrl) {
    if (!targetTab) return;
    var value = targetTab.dataset.tab;

    ctx.tabs.forEach(function (t) {
      var active = t === targetTab;
      t.setAttribute('aria-selected', active ? 'true' : 'false');
      t.tabIndex = active ? 0 : -1;
      t.classList.toggle('is-active', active);
    });

    ctx.panels.forEach(function (p) {
      var active = p.dataset.panel === value;
      p.hidden = !active;
      p.classList.toggle('is-active', active);
    });

    positionIndicator(ctx.root, ctx.indicator, targetTab);

    if (updateUrl !== false && ctx.opts.mode !== 'href') {
      syncUrl(ctx.opts, value);
    }

    ctx.root.dispatchEvent(new CustomEvent('nt-tabs:change', {
      detail: { value: value, tab: targetTab },
      bubbles: true
    }));
  }

  function initGroup(root) {
    if (root.dataset.ntTabsInit) return;
    root.dataset.ntTabsInit = '1';

    var list = root.querySelector('.nt-tabs-list');
    if (!list) return;
    var tabs = qsa('.nt-tab', list);
    var panelsWrap = root.querySelector('.nt-tabs-panels');
    var panels = panelsWrap ? qsa('.nt-tab-panel', panelsWrap) : [];
    var indicator = root.querySelector('.nt-tabs-indicator');

    var opts = {
      mode: root.dataset.mode || 'client',
      group: root.dataset.group || root.id || 'nt-tabs',
      param: root.dataset.param || 'tab'
    };

    var ctx = { root: root, tabs: tabs, panels: panels, indicator: indicator, opts: opts };

    list.setAttribute('role', 'tablist');
    tabs.forEach(function (t, i) {
      t.setAttribute('role', 'tab');
      if (!t.id) t.id = opts.group + '-tab-' + (t.dataset.tab || i);
      var panel = panels.filter(function (p) { return p.dataset.panel === t.dataset.tab; })[0];
      if (panel) {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', t.id);
      }
    });

    // Tentukan tab aktif awal
    var initial = null;
    if (opts.mode === 'href') {
      initial = findHrefMatch(tabs);
    } else {
      var urlVal = readUrlValue(opts);
      if (urlVal) initial = tabs.filter(function (t) { return t.dataset.tab === urlVal; })[0];
    }
    if (!initial) {
      initial = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
    }

    activateTab(ctx, initial, false);
    // Posisikan ulang setelah layout stabil (font/webfont load dsb).
    requestAnimationFrame(function () { positionIndicator(root, indicator, initial); });

    tabs.forEach(function (t) {
      t.addEventListener('click', function (e) {
        if (opts.mode === 'href') return; // navigasi normal oleh browser
        e.preventDefault();
        activateTab(ctx, t);
      });
    });

    list.addEventListener('keydown', function (e) {
      var idx = tabs.indexOf(document.activeElement);
      if (idx === -1) return;
      var vertical = isVertical(root);
      var next = null;
      if ((!vertical && e.key === 'ArrowRight') || (vertical && e.key === 'ArrowDown')) {
        next = tabs[(idx + 1) % tabs.length];
      } else if ((!vertical && e.key === 'ArrowLeft') || (vertical && e.key === 'ArrowUp')) {
        next = tabs[(idx - 1 + tabs.length) % tabs.length];
      } else if (e.key === 'Home') {
        next = tabs[0];
      } else if (e.key === 'End') {
        next = tabs[tabs.length - 1];
      }
      if (next) {
        e.preventDefault();
        next.focus();
        if (opts.mode !== 'href') activateTab(ctx, next);
      }
    });

    window.addEventListener('resize', function () {
      var active = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
      positionIndicator(root, indicator, active);
    });

    if (opts.mode === 'hash') {
      window.addEventListener('hashchange', function () {
        var v = readUrlValue(opts);
        var t = tabs.filter(function (x) { return x.dataset.tab === v; })[0];
        if (t) activateTab(ctx, t, false);
      });
    }
    if (opts.mode === 'query') {
      window.addEventListener('popstate', function () {
        var v = readUrlValue(opts);
        var t = tabs.filter(function (x) { return x.dataset.tab === v; })[0] || tabs[0];
        activateTab(ctx, t, false);
      });
    }
  }

  function init(scope) {
    qsa('[data-nt-tabs]', scope || document).forEach(initGroup);
  }

  window.NetraUI = window.NetraUI || {};
  Object.assign(window.NetraUI, { initTabs: init });

  document.addEventListener('DOMContentLoaded', function () { init(); });

})();
