/* ═══════════════════════════════════════════════════════
   Netra UI — Avatar Component JS  (netra-avatar.js)
   Depends on: netra-base.js
   Data-attribute driven — zero inline JS in HTML.
   ═══════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ════════════════════════════════════════════════
     1. AVATAR CAROUSEL
     Usage:
       <div data-nt-carousel="avatar-team" data-visible="4">
         <div data-nt-carousel-track>...</div>
         <button data-nt-carousel-prev>...</button>
         <button data-nt-carousel-next>...</button>
       </div>
     data-visible : number of items visible at once (default 4)
  ════════════════════════════════════════════════ */

  function initCarousels(root) {
    root.querySelectorAll('[data-nt-carousel]').forEach(wrap => {
      const track = wrap.querySelector('[data-nt-carousel-track]');
      const btnPrev = wrap.querySelector('[data-nt-carousel-prev]');
      const btnNext = wrap.querySelector('[data-nt-carousel-next]');
      if (!track) return;

      const items = Array.from(track.children);
      const visible = parseInt(wrap.dataset.visible || '4', 10);
      let current = 0;
      const total = items.length;

      function itemWidth() {
        if (!items[0]) return 0;
        const style = getComputedStyle(track);
        const gap = parseFloat(style.gap || style.columnGap || '16');
        return items[0].offsetWidth + gap;
      }

      function clamp(val) {
        return Math.max(0, Math.min(val, total - visible));
      }

      function render() {
        current = clamp(current);
        track.style.transform = `translateX(-${current * itemWidth()}px)`;
        if (btnPrev) btnPrev.disabled = current === 0;
        if (btnNext) btnNext.disabled = current >= total - visible;
      }

      if (btnPrev) btnPrev.addEventListener('click', () => { current--; render(); });
      if (btnNext) btnNext.addEventListener('click', () => { current++; render(); });

      // Keyboard navigation
      wrap.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') { current--; render(); }
        if (e.key === 'ArrowRight') { current++; render(); }
      });

      // Click to select
      items.forEach((item, idx) => {
        item.setAttribute('tabindex', '0');
        item.addEventListener('click', () => {
          items.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          const selectEvent = new CustomEvent('nt:avatar:select', {
            bubbles: true,
            detail: { index: idx, item, carousel: wrap.dataset.ntCarousel }
          });
          wrap.dispatchEvent(selectEvent);
        });
        item.addEventListener('keydown', e => {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); item.click(); }
        });
      });

      // Touch / swipe
      let touchStartX = 0;
      track.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
      track.addEventListener('touchend', e => {
        const dx = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(dx) > 40) { current += dx > 0 ? 1 : -1; render(); }
      });

      render();
    });
  }

  /* ════════════════════════════════════════════════
     2. AVATAR PICKER (horizontal chip-scroll)
     Usage:
       <div data-nt-avatar-picker data-name="assignee">
         <div class="nt-avatar-picker-item" data-value="user-1">...</div>
         ...
       </div>
     Fires CustomEvent 'nt:avatar:pick' on the container.
  ════════════════════════════════════════════════ */

  function initPickers(root) {
    root.querySelectorAll('[data-nt-avatar-picker]').forEach(picker => {
      const items = Array.from(picker.querySelectorAll('[data-value]'));
      items.forEach(item => {
        item.setAttribute('tabindex', '0');
        item.setAttribute('role', 'radio');
        item.setAttribute('aria-checked', item.classList.contains('selected') ? 'true' : 'false');

        item.addEventListener('click', () => {
          const multi = picker.dataset.multi !== undefined;
          if (!multi) {
            items.forEach(i => { i.classList.remove('selected'); i.setAttribute('aria-checked', 'false'); });
          }
          item.classList.toggle('selected');
          const isSelected = item.classList.contains('selected');
          item.setAttribute('aria-checked', String(isSelected));

          picker.dispatchEvent(new CustomEvent('nt:avatar:pick', {
            bubbles: true,
            detail: { value: item.dataset.value, selected: isSelected, picker: picker.dataset.ntAvatarPicker, name: picker.dataset.name }
          }));
        });

        item.addEventListener('keydown', e => {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); item.click(); }
        });
      });
    });
  }

  /* ════════════════════════════════════════════════
     3. AVATAR UPLOAD
     Usage:
       <div class="nt-avatar-upload" data-nt-avatar-upload>
         <div class="nt-avatar ...">...</div>
         <div class="nt-avatar-upload-overlay">...</div>
         <input type="file" accept="image/*" data-nt-upload-input>
       </div>
     Fires 'nt:avatar:upload' with { file, dataUrl } on the wrap.
     Developer handles Livewire/AJAX upload in that listener.
  ════════════════════════════════════════════════ */

  function initUploads(root) {
    root.querySelectorAll('[data-nt-avatar-upload]').forEach(wrap => {
      const input = wrap.querySelector('[data-nt-upload-input]');
      const img = wrap.querySelector('img');
      if (!input) return;

      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = e => {
          const dataUrl = e.target.result;
          // If there's an img tag inside avatar, preview it
          if (img) { img.src = dataUrl; img.style.display = 'block'; }

          wrap.dispatchEvent(new CustomEvent('nt:avatar:upload', {
            bubbles: true,
            detail: { file, dataUrl, wrap }
          }));
        };
        reader.readAsDataURL(file);
      });
    });
  }

  /* ════════════════════════════════════════════════
     4. AVATAR STATUS TOOLTIP
     Reads data-tip attribute; shown on hover via CSS,
     but JS adds aria-label for accessibility.
  ════════════════════════════════════════════════ */

  function initTooltips(root) {
    root.querySelectorAll('[data-tip]').forEach(el => {
      el.setAttribute('aria-label', el.dataset.tip);
    });
  }

  /* ════════════════════════════════════════════════
     5. PUBLIC API
     window.NetraAvatar.init(root?) — re-init for
     dynamic content (Livewire, AJAX, modals).
  ════════════════════════════════════════════════ */

  function init(root) {
    const r = root || document;
    initCarousels(r);
    initPickers(r);
    initUploads(r);
    initTooltips(r);
  }

  window.NetraAvatar = { init };

  /* Auto-init on DOM ready */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init());
  } else {
    init();
  }

})();
