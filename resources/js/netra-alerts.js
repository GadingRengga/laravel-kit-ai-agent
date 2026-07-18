/* ═══════════════════════════════════════════════════════
   Netra UI — Alert & Toast Manager (ntAlert)  (modular split)
   Membutuhkan netra-base.js. Aman dimuat sendiri
   (window.NetraUI di-guard otomatis).
   ═══════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════
   NetraUI.Alert  —  Inline dismiss + Toast Manager

   INLINE ALERT (statis di markup):
     <div class="nt-alert nt-alert-soft nt-alert-tone-success">
       <div class="nt-alert-icon"><i class="fa-solid fa-circle-check"></i></div>
       <div class="nt-alert-content">
         <p class="nt-alert-title">Berhasil disimpan</p>
         <p class="nt-alert-desc">Perubahan kamu sudah tersimpan.</p>
       </div>
       <button class="nt-alert-close" onclick="ntAlert.dismiss(this)"><i class="fa-solid fa-xmark"></i></button>
     </div>

   TOAST (dinamis via JS):
     ntAlert.toast({
       tone: 'success' | 'error' | 'warning' | 'info' | 'primary' | 'accent' | 'neutral',
       mode: 'soft' | 'solid' | 'outline' | 'glass'   (default: 'solid'),
       icon: 'fa-solid fa-circle-check',
       title: 'Tersimpan',
       message: 'Data berhasil diperbarui.',
       duration: 4000,        // ms, 0 = sticky (tidak auto-dismiss)
       position: 'top-right', // top-right | top-left | bottom-right | bottom-left | top-center
       actions: [{ label: 'Undo', onClick: () => {...}, ghost: true }]
     });

     ntAlert.dismiss(idOrEl)   — tutup satu alert/toast
     ntAlert.clearAll()        — tutup semua toast aktif

   Shorthand:
     ntAlert.success(message, title?)
     ntAlert.error(message, title?)
     ntAlert.warning(message, title?)
     ntAlert.info(message, title?)
   ═══════════════════════════════════════════════════════ */
const ntAlert = (() => {
  const DEFAULT_ICONS = {
    primary: 'fa-solid fa-bell',
    accent: 'fa-solid fa-sparkles',
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-exclamation',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info',
    neutral: 'fa-solid fa-comment-dots',
  };

  const stackCache = {};
  let seq = 0;

  function getStack(position) {
    const pos = position || 'top-right';
    const key = 'nt-toast-stack-' + pos;
    if (stackCache[pos]) return stackCache[pos];

    let el = document.getElementById(key);
    if (!el) {
      el = document.createElement('div');
      el.id = key;
      el.className = 'nt-toast-stack pos-' + pos;
      document.body.appendChild(el);
    }
    stackCache[pos] = el;
    return el;
  }

  function buildAlertEl(opts) {
    const tone = opts.tone || 'primary';
    const mode = opts.mode || 'solid';
    const icon = opts.icon || DEFAULT_ICONS[tone] || DEFAULT_ICONS.primary;
    const id = 'nt-alert-' + (++seq);

    const el = document.createElement('div');
    el.id = id;
    el.className = `nt-alert nt-alert-${mode} nt-alert-tone-${tone}`;
    el.setAttribute('role', 'status');

    let actionsHtml = '';
    if (Array.isArray(opts.actions) && opts.actions.length) {
      actionsHtml = `<div class="nt-alert-actions">${opts.actions.map((a, i) =>
        `<button type="button" class="nt-alert-action-btn${a.ghost ? ' is-ghost' : ''}" data-action-idx="${i}">${a.label}</button>`
      ).join('')}</div>`;
    }

    const durationMs = opts.duration === 0 ? 0 : (opts.duration || 4200);
    const progressHtml = durationMs > 0
      ? `<div class="nt-alert-progress" style="animation-duration:${durationMs}ms"><span></span></div>`
      : '';

    el.innerHTML = `
      <div class="nt-alert-icon"><i class="${icon}"></i></div>
      <div class="nt-alert-content">
        ${opts.title ? `<p class="nt-alert-title">${opts.title}</p>` : ''}
        ${opts.message ? `<p class="nt-alert-desc">${opts.message}</p>` : ''}
        ${actionsHtml}
      </div>
      ${opts.dismissible === false ? '' : `<button type="button" class="nt-alert-close" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>`}
      ${progressHtml}
    `;

    // Wire close button
    const closeBtn = el.querySelector('.nt-alert-close');
    if (closeBtn) closeBtn.addEventListener('click', () => dismiss(el));

    // Wire action buttons
    if (Array.isArray(opts.actions)) {
      el.querySelectorAll('[data-action-idx]').forEach(btn => {
        const idx = Number(btn.getAttribute('data-action-idx'));
        btn.addEventListener('click', () => {
          const action = opts.actions[idx];
          if (action && typeof action.onClick === 'function') action.onClick();
          if (!action || action.closeOnClick !== false) dismiss(el);
        });
      });
    }

    return { el, durationMs };
  }

  function toast(opts = {}) {
    const stack = getStack(opts.position);
    const { el, durationMs } = buildAlertEl(opts);

    if (opts.position && opts.position.indexOf('bottom') === 0) {
      stack.insertBefore(el, stack.firstChild);
    } else {
      stack.appendChild(el);
    }

    if (durationMs > 0) {
      el._ntAlertTimer = setTimeout(() => dismiss(el), durationMs);
    }
    return el.id;
  }

  function dismiss(target) {
    const el = (typeof target === 'string')
      ? document.getElementById(target)
      : (target instanceof Element ? target.closest('.nt-alert') : null);
    if (!el) return;
    if (el._ntAlertTimer) clearTimeout(el._ntAlertTimer);
    el.classList.add('nt-alert-leaving');
    setTimeout(() => el.remove(), 240);
  }

  function clearAll() {
    document.querySelectorAll('.nt-toast-stack .nt-alert').forEach(dismiss);
  }

  function shorthand(tone) {
    return (message, title, opts = {}) => toast({ tone, message, title, ...opts });
  }

  return {
    toast,
    dismiss,
    clearAll,
    success: shorthand('success'),
    error: shorthand('error'),
    warning: shorthand('warning'),
    info: shorthand('info'),
    primary: shorthand('primary'),
    accent: shorthand('accent'),
    neutral: shorthand('neutral'),
  };
})();

window.ntAlert = ntAlert;

if (!window.NetraUI) window.NetraUI = {};
window.NetraUI.alert = ntAlert;
