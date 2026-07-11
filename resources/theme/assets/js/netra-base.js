/* ═══════════════════════════════════════════════════════
   Netra UI — Base JS (Sidebar, Submenu, Topbar Dropdown, Dark Mode)  (v5, modular split)
   Wajib dimuat di semua halaman, sebelum modul lain.
   ═══════════════════════════════════════════════════════ */

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


/* ── Auto-init: tandai menu sidebar aktif sesuai URL ──
   Dipindahkan ke base supaya berjalan di SEMUA halaman,
   tidak hanya halaman yang memuat netra-tables.js. */
document.addEventListener('DOMContentLoaded', () => {
  initNavActive();
});
