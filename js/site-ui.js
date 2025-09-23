// /js/site-ui.js

// If this file is loaded with <script type="module">, keep the import.
// If not using modules, remove this import and load orientation-utils.js before this script.
import { updateOrientationClass, trackOrientationChange } from "./orientation-utils.js";

document.addEventListener("DOMContentLoaded", () => {
  // — Mobile product categories toggle —
  const pill       = document.querySelector(".pill-toggle");
  const mobileCats = document.querySelector(".mobile-product-categories");
  pill?.addEventListener("click", () => {
    mobileCats?.classList.toggle("open");
  });

  // — Mobile nav drawer toggle (hamburger menu) —
  const btn    = document.querySelector('.hamburger-menu');
  const drawer = document.getElementById('mobileNavDrawer');
  const page   = document.querySelector('.page-content');

  if (btn && drawer) {
    btn.addEventListener('click', () => {
      const isOpen = drawer.classList.toggle('open');
      page?.classList.toggle('hidden', isOpen);

      btn.setAttribute('aria-expanded', String(isOpen));
      btn.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

      const icon = btn.querySelector('.hamburger-icon');
      if (icon) {
        icon.innerHTML = isOpen
          ? '<svg aria-label="Close icon" width="24" height="24" viewBox="0 0 24 24"><path d="M6 6L18 18M6 18L18 6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>'
          : '<svg aria-label="Menu icon" width="24" height="24" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>';
      }
    });
  }

  // — Update all cart-badge counts —
  const cartCount = 0;  // placeholder; swap in your real cart logic later
  document.querySelectorAll(".cart-badge").forEach(badge => {
    badge.textContent = String(cartCount);
    badge.parentElement?.setAttribute("aria-label", `Cart, ${cartCount} items`);
  });

  // — Kick off orientation tracking —
  if (typeof updateOrientationClass === "function") updateOrientationClass();
  if (typeof trackOrientationChange === "function") trackOrientationChange();
});

/* ============================================================================
   Vanilla Modal Carousel for product-card data-images
   Depends on markup from inc/product-grid.php:
     <a class="product-card" data-images='[{"src":"...","alt":"..."}]'>...</a>
   ========================================================================== */

(() => {
  // Guard: only run once
  if (window.__swModalCarouselInit) return;
  window.__swModalCarouselInit = true;

  // ---------------------------
  // Lightweight lazy loader
  // ---------------------------
  const lazyLoadImg = (img) => {
    if (!img) return;
    if (img.dataset.src) {
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
    }
  };

  // ---------------------------
  // Modal DOM (created once)
  // ---------------------------
  const modal = document.createElement('div');
  modal.className = 'sw-modal is-hidden';
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  modal.setAttribute('aria-label', 'Product image gallery');

  modal.innerHTML = `
    <div class="sw-modal__overlay" data-close="1"></div>
    <div class="sw-modal__dialog" role="document" tabindex="-1">
      <button class="sw-modal__close" type="button" aria-label="Close gallery" data-close="1">×</button>

      <div class="sw-carousel" aria-live="polite">
        <button class="sw-carousel__nav sw-carousel__prev" type="button" aria-label="Previous image">‹</button>
        <div class="sw-carousel__viewport">
          <div class="sw-carousel__track"></div>
        </div>
        <button class="sw-carousel__nav sw-carousel__next" type="button" aria-label="Next image">›</button>
      </div>

      <div class="sw-carousel__counter" aria-hidden="true">
        <span class="sw-carousel__index">1</span> / <span class="sw-carousel__total">1</span>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  const overlay   = modal.querySelector('.sw-modal__overlay');
  const dialog    = modal.querySelector('.sw-modal__dialog');
  const closeBtn  = modal.querySelector('.sw-modal__close');
  const track     = modal.querySelector('.sw-carousel__track');
  const vp        = modal.querySelector('.sw-carousel__viewport');
  const prevBtn   = modal.querySelector('.sw-carousel__prev');
  const nextBtn   = modal.querySelector('.sw-carousel__next');
  const idxEl     = modal.querySelector('.sw-carousel__index');
  const totalEl   = modal.querySelector('.sw-carousel__total');

  // State
  let slides = [];             // [{src, alt}]
  let current = 0;
  let lastActive = null;       // element that opened the modal (for focus restore)
  let isOpen = false;

  // ---------------------------
  // Helpers
  // ---------------------------
  const clamp = (n, min, max) => Math.min(Math.max(n, min), max);
  const total = () => slides.length;
  const setCounter = () => {
    if (idxEl) idxEl.textContent = String(current + 1);
    if (totalEl) totalEl.textContent = String(total());
  };

  const renderSlides = () => {
    track.innerHTML = '';
    slides.forEach((s, i) => {
      const item = document.createElement('div');
      item.className = 'sw-carousel__slide';
      item.setAttribute('role', 'group');
      item.setAttribute('aria-label', `${i + 1} of ${slides.length}`);

      const img = document.createElement('img');
      img.className = 'sw-carousel__img';
      // lazy: set data-src, assign src on-demand
      img.setAttribute('data-src', s.src);
      img.setAttribute('alt', s.alt || 'Product image');
      img.setAttribute('loading', 'lazy');

      item.appendChild(img);
      track.appendChild(item);
    });
    setCounter();
    // Jump to first slide
    goTo(0, true);
  };

  const ensureVisible = (index) => {
    const slideEl = track.children[index];
    if (!slideEl) return;
    // Lazy-load the image now
    const img = slideEl.querySelector('img[data-src]');
    if (img) lazyLoadImg(img);

    // Translate track to show this index
    const pct = -(index * 100);
    track.style.transform = `translateX(${pct}%)`;

    // Update selected classes
    [...track.children].forEach((el, i) => {
      if (i === index) el.classList.add('is-active');
      else el.classList.remove('is-active');
    });
  };

  const goTo = (index, jump = false) => {
    current = clamp(index, 0, Math.max(0, total() - 1));
    if (jump) {
      track.style.transition = 'none';
      ensureVisible(current);
      // Force reflow to reset transition after instant jump
      void track.offsetWidth;
      track.style.transition = '';
    } else {
      ensureVisible(current);
    }
    setCounter();
  };

  const next = () => goTo(current + 1);
  const prev = () => goTo(current - 1);

  const open = () => {
    if (isOpen || total() === 0) return;
    isOpen = true;
    modal.classList.remove('is-hidden');
    document.body.classList.add('sw-modal--open');
    // Focus trap: focus dialog
    dialog.focus();
    document.addEventListener('keydown', onKeydown);
    document.addEventListener('focus', onFocusTrap, true);
  };

  const close = () => {
    if (!isOpen) return;
    isOpen = false;
    modal.classList.add('is-hidden');
    document.body.classList.remove('sw-modal--open');
    document.removeEventListener('keydown', onKeydown);
    document.removeEventListener('focus', onFocusTrap, true);
    if (lastActive && typeof lastActive.focus === 'function') {
      lastActive.focus();
    }
  };

  const onKeydown = (e) => {
    if (!isOpen) return;
    switch (e.key) {
      case 'Escape':
        e.preventDefault();
        close();
        break;
      case 'ArrowRight':
        e.preventDefault();
        next();
        break;
      case 'ArrowLeft':
        e.preventDefault();
        prev();
        break;
    }
  };

  const onFocusTrap = (e) => {
    if (!isOpen) return;
    if (!modal.contains(e.target)) {
      // redirect focus back into dialog
      e.stopPropagation();
      dialog.focus();
    }
  };

  // ---------------------------
  // Event wiring
  // ---------------------------
  overlay.addEventListener('click', close);
  closeBtn.addEventListener('click', close);
  nextBtn.addEventListener('click', next);
  prevBtn.addEventListener('click', prev);

  // Delegate clicks on product cards
  document.addEventListener('click', (e) => {
    const a = e.target.closest && e.target.closest('a.product-card[data-images]');
    if (!a) return;

    // If the click occurred inside the product-info area (price/title),
    // allow the normal navigation to product.php.
    const inInfoBlock = e.target.closest('.product-info');
    if (inInfoBlock) return; // do NOT preventDefault → navigate as normal

    // Otherwise, treat it as a click on the media area → open modal
    e.preventDefault();

    // Parse image list
    let list = [];
    try {
      const raw = a.getAttribute('data-images') || '[]';
      list = JSON.parse(raw);
      if (!Array.isArray(list)) list = [];
    } catch (_err) {
      list = [];
    }
    if (list.length === 0) return;

    slides = list;
    renderSlides();
    lastActive = a;
    open();
  });
})();   // <-- CLOSE the IIFE







