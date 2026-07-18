/* ═══════════════════════════════════════════════════════
   Netra UI — Loading Manager (ntLoading)  (modular split)
   Membutuhkan netra-base.js. Aman dimuat sendiri
   (window.NetraUI di-guard otomatis).
   ═══════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════
   NetraUI.Loading  —  Overlay, Button state, Progress bar

   FULL-PAGE OVERLAY:
     ntLoading.show('Menyimpan data…')   // buka overlay
     ntLoading.hide()                    // tutup overlay

   CONTAINED OVERLAY (di dalam card/section tertentu):
     <div class="relative">
       ...content...
       <div id="card-loader"></div>
     </div>
     ntLoading.showIn('card-loader', 'Memuat…')
     ntLoading.hideIn('card-loader')

   BUTTON LOADING STATE:
     ntLoading.button(btnEl, true)   // aktifkan spinner, disable tombol
     ntLoading.button(btnEl, false)  // kembalikan tombol seperti semula

   PROGRESS BAR (determinate):
     <div class="nt-progress" id="upload-progress">
       <div class="nt-progress-bar" style="width:0%"></div>
     </div>
     ntLoading.setProgress('upload-progress', 62)   // set ke 62%
   ═══════════════════════════════════════════════════════ */
const ntLoading = (() => {
  let overlayEl = null;

  function ensureOverlay() {
    if (overlayEl) return overlayEl;
    overlayEl = document.getElementById('nt-loading-overlay');
    if (!overlayEl) {
      overlayEl = document.createElement('div');
      overlayEl.id = 'nt-loading-overlay';
      overlayEl.className = 'nt-loading-overlay';
      overlayEl.innerHTML = `
        <div class="nt-loading-card">
          <span class="nt-spinner-dual nt-spin-lg"></span>
          <p class="nt-loading-text" data-nt-loading-text>Memuat…</p>
        </div>`;
      document.body.appendChild(overlayEl);
    }
    return overlayEl;
  }

  function show(message) {
    const el = ensureOverlay();
    const textEl = el.querySelector('[data-nt-loading-text]');
    if (textEl) {
      if (message) { textEl.textContent = message; textEl.style.display = ''; }
      else { textEl.style.display = 'none'; }
    }
    requestAnimationFrame(() => el.classList.add('is-open'));
    document.body.style.overflow = 'hidden';
  }

  function hide() {
    if (!overlayEl) return;
    overlayEl.classList.remove('is-open');
    setTimeout(() => { document.body.style.overflow = ''; }, 220);
  }

  function showIn(containerId, message) {
    const host = document.getElementById(containerId);
    if (!host) { console.warn('[ntLoading] Elemen tidak ditemukan:', containerId); return; }
    host.classList.add('nt-loading-overlay', 'is-contained');
    host.innerHTML = `
      <div class="nt-loading-card">
        <span class="nt-spinner-dual nt-spin-md"></span>
        ${message ? `<p class="nt-loading-text">${message}</p>` : ''}
      </div>`;
    requestAnimationFrame(() => host.classList.add('is-open'));
  }

  function hideIn(containerId) {
    const host = document.getElementById(containerId);
    if (!host) return;
    host.classList.remove('is-open');
    setTimeout(() => { host.innerHTML = ''; }, 220);
  }

  function button(btnEl, isLoading) {
    if (!btnEl) return;
    if (isLoading) {
      if (!btnEl.dataset.ntOrigDisabled) {
        btnEl.dataset.ntOrigDisabled = btnEl.disabled ? '1' : '0';
      }
      btnEl.classList.add('is-loading');
      btnEl.disabled = true;
    } else {
      btnEl.classList.remove('is-loading');
      btnEl.disabled = btnEl.dataset.ntOrigDisabled === '1';
      delete btnEl.dataset.ntOrigDisabled;
    }
  }

  function setProgress(containerId, percent) {
    const track = document.getElementById(containerId);
    if (!track) { console.warn('[ntLoading] Elemen tidak ditemukan:', containerId); return; }
    const bar = track.querySelector('.nt-progress-bar');
    const label = track.parentElement?.querySelector('[data-nt-progress-label]');
    const clamped = Math.max(0, Math.min(100, percent));
    if (bar) bar.style.width = clamped + '%';
    if (label) label.textContent = Math.round(clamped) + '%';
  }

  function setCircleProgress(svgId, percent, radius = 26) {
    const circle = document.querySelector('#' + svgId + ' .nt-pc-fill');
    const valueEl = document.querySelector('#' + svgId + ' + .nt-pc-value, #' + svgId + ' ~ .nt-pc-value');
    if (!circle) { console.warn('[ntLoading] Circle tidak ditemukan:', svgId); return; }
    const circumference = 2 * Math.PI * radius;
    const clamped = Math.max(0, Math.min(100, percent));
    const offset = circumference - (clamped / 100) * circumference;
    circle.style.strokeDasharray = `${circumference}`;
    circle.style.strokeDashoffset = `${offset}`;
    if (valueEl) valueEl.textContent = Math.round(clamped) + '%';
  }

  return { show, hide, showIn, hideIn, button, setProgress, setCircleProgress };
})();

window.ntLoading = ntLoading;

if (!window.NetraUI) window.NetraUI = {};
window.NetraUI.loading = ntLoading;
