/* ═══════════════════════════════════════════════════════
   Netra UI — Auth JS (Login, Register, Forgot Password)
   Tidak bergantung pada netra-base.js (no sidebar).
   ═══════════════════════════════════════════════════════ */

/* ── Dark mode init ──
   Sync theme attribute saat modul dimuat.
   (Inline <script> di <head> sudah set class,
    ini hanya memastikan data-theme sinkron.) */
(function syncTheme() {
  const isDark = document.documentElement.classList.contains('dark');
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
})();

/* ── Toggle dark mode ── */
function toggleDark() {
  const isDark = document.documentElement.classList.toggle('dark');
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');

  const icon = document.getElementById('dark-toggle-icon');
  if (icon) {
    icon.className = isDark
      ? 'fa-solid fa-sun'
      : 'fa-solid fa-moon';
  }
}

/* ── Password visibility toggle ── */
function toggleAuthPassword(inputId, btnId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(btnId);
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'fa-regular fa-eye-slash text-sm';
  } else {
    input.type = 'password';
    icon.className = 'fa-regular fa-eye text-sm';
  }
}

/* ── Show auth alert ──
   type: 'error' | 'success'
   message: string
   containerId: id of the alert container element */
function showAuthAlert(containerId, type, message) {
  const el = document.getElementById(containerId);
  if (!el) return;

  const iconClass = type === 'error'
    ? 'fa-solid fa-circle-exclamation'
    : 'fa-solid fa-circle-check';

  el.className = `auth-alert auth-alert-${type}`;
  el.innerHTML = `<i class="${iconClass}"></i><span>${message}</span>`;
  el.style.display = 'flex';

  // Auto hide success after 4 s
  if (type === 'success') {
    setTimeout(() => { el.style.display = 'none'; }, 4000);
  }
}

function hideAuthAlert(containerId) {
  const el = document.getElementById(containerId);
  if (el) el.style.display = 'none';
}

/* ── Login form submit handler ──
   Replace dengan Fetch / Axios / Livewire sesuai kebutuhan. */
function handleLoginSubmit(e) {
  e.preventDefault();

  const btn   = document.getElementById('login-btn');
  const email = document.getElementById('login-email').value.trim();
  const pass  = document.getElementById('login-password').value;

  hideAuthAlert('login-alert');

  // Basic client-side validation
  if (!email) {
    showAuthAlert('login-alert', 'error', 'Email tidak boleh kosong.');
    document.getElementById('login-email').focus();
    return;
  }
  if (!pass) {
    showAuthAlert('login-alert', 'error', 'Password tidak boleh kosong.');
    document.getElementById('login-password').focus();
    return;
  }

  // Loading state
  btn.classList.add('loading');
  btn.disabled = true;

  /* ── Simulasi API call (ganti dengan fetch() asli) ──
     Hapus blok setTimeout ini dan ganti dengan logic backend. */
  setTimeout(() => {
    btn.classList.remove('loading');
    btn.disabled = false;

    // Contoh respons gagal (demo):
    showAuthAlert('login-alert', 'error', 'Email atau password salah. Silakan coba lagi.');

    // Contoh respons sukses — uncomment baris di bawah untuk demo sukses:
    // showAuthAlert('login-alert', 'success', 'Login berhasil! Mengalihkan...');
    // setTimeout(() => { window.location.href = 'admin-dashboard.html'; }, 1200);
  }, 1600);
}

/* ── DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
  // Attach login form
  const form = document.getElementById('login-form');
  // if (form) form.addEventListener('submit', handleLoginSubmit);

  // Sync dark toggle icon on load
  const icon = document.getElementById('dark-toggle-icon');
  if (icon) {
    const isDark = document.documentElement.classList.contains('dark');
    icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  }
});

window.toggleAuthPassword = toggleAuthPassword;
