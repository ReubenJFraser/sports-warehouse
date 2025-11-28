// js/card-slider.js
// Inline, per-card Swiper initializer (desktop/tablet only)
//
// Expectations per product card:
//  - <article class="product-card" data-pswp-gallery="prod-123" data-images='[{"src": "...", "w": 1600, "h": 2000, "alt":"..."}]'>
//  - First/hero anchor exists: <a class="card-media card-media-trigger" href="..." data-pswp ...><img ...></a>
//  - Optional Zoom button: <button type="button" class="card-zoom" aria-label="View full size">Zoom</button>
//
// Behavior:
//  - ≥768px: build a per-card Swiper (prev/next + bullets), emit events, keep PhotoSwipe available via Zoom button.
//  - <768px: do NOT build Swiper; PhotoSwipe remains primary on image anchor taps.
//  - Lazy init via IntersectionObserver. Reponds to resize/orientationchange.
//  - Per-card isolation: all DOM is scoped to each .product-card.
//
// Notes:
//  - Matches Swiper **v10** ESM import below. If you upgrade CSS to v11, switch the ESM URL accordingly.

import Swiper, {
  Navigation,
  Pagination,
  A11y,
  Keyboard,
} from 'https://unpkg.com/swiper@10/swiper-bundle.esm.min.js';

Swiper.use([Navigation, Pagination, A11y, Keyboard]);

const BP_DESKTOP = 768;

const qs  = (sel, root = document) => root.querySelector(sel);
const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
const isDesktop = () => window.matchMedia(`(min-width:${BP_DESKTOP}px)`).matches;

function debounce(fn, wait = 200) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

/**
 * Create/find the Swiper shell inside a product card.
 * Returns {container, wrapper, paginationEl, prevBtn, nextBtn, heroAnchor}
 */
function ensureSwiperDOM(card) {
  const existing = qs('.card-media-swiper.swiper', card);
  if (existing) {
    return {
      container: existing,
      wrapper: qs('.swiper-wrapper', existing),
      paginationEl: qs('.swiper-pagination', existing),
      prevBtn: qs('.swiper-button-prev', existing),
      nextBtn: qs('.swiper-button-next', existing),
    };
  }

  // The server-rendered hero anchor (first image)
  const heroAnchor = qs('.card-media.card-media-trigger', card);
  if (!heroAnchor) return null;

  const container = document.createElement('div');
  container.className = 'card-media-swiper swiper';
  container.setAttribute('role', 'region');
  container.setAttribute('aria-label', 'Product images');

  const wrapper = document.createElement('div');
  wrapper.className = 'swiper-wrapper';

  const pagination = document.createElement('div');
  pagination.className = 'swiper-pagination';

  const prev = document.createElement('button');
  prev.type = 'button';
  prev.className = 'swiper-button-prev';
  prev.setAttribute('aria-label', 'Previous image');

  const next = document.createElement('button');
  next.type = 'button';
  next.className = 'swiper-button-next';
  next.setAttribute('aria-label', 'Next image');

  container.appendChild(wrapper);
  container.appendChild(pagination);
  container.appendChild(prev);
  container.appendChild(next);

  // Insert before the hero anchor; we’ll move the anchor inside as slide #1
  heroAnchor.parentNode.insertBefore(container, heroAnchor);

  return { container, wrapper, paginationEl: pagination, prevBtn: prev, nextBtn: next, heroAnchor };
}

/**
 * Populate slides from:
 *  - the existing hero anchor (first slide)
 *  - optional JSON array in card.dataset.images (extra slides)
 */
function populateSlides(card, wrapper, heroAnchor) {
  const makeSlideFromAnchor = (a) => {
    const slide = document.createElement('div');
    slide.className = 'swiper-slide';
    slide.appendChild(a);
    return slide;
  };

  // 1) First slide = existing hero anchor (move into swiper)
  if (heroAnchor && !heroAnchor.closest('.swiper-slide')) {
    heroAnchor.classList.add('swiper-origin'); // marker (optional)
    wrapper.appendChild(makeSlideFromAnchor(heroAnchor));
  }

  // 2) Additional slides from data-images (if provided)
  let gallery = [];
  try {
    gallery = JSON.parse(card.dataset.images || '[]');
    if (!Array.isArray(gallery)) gallery = [];
  } catch (_e) {
    gallery = [];
  }

  const heroHref = heroAnchor?.getAttribute('href') || '';

  for (const item of gallery) {
    const src = item.src || item.url || '';
    if (!src || src === heroHref) continue; // avoid duping hero

    const w   = item.w || item.width  || null;
    const h   = item.h || item.height || null;
    const alt = item.alt || card.getAttribute('data-alt') || card.getAttribute('title') || 'Product image';

    const a = document.createElement('a');
    a.className = heroAnchor?.className || 'card-media card-media-trigger';
    a.setAttribute('href', src);
    a.setAttribute('data-pswp', '');
    if (w && h) {
      a.setAttribute('data-pswp-width',  String(w));
      a.setAttribute('data-pswp-height', String(h));
    }

    const img = document.createElement('img');
    img.className = 'lazy';
    img.setAttribute('data-src', src);
    img.setAttribute('alt', alt);
    img.setAttribute('width',  '300');
    img.setAttribute('height', '300');
    img.setAttribute('loading', 'lazy');
    img.setAttribute('decoding', 'async');
    img.onerror = () => {
      img.onerror = null;
      img.src = '/images/placeholders/product_missing.svg';
      img.classList.add('is-placeholder');
    };

    a.appendChild(img);
    wrapper.appendChild(makeSlideFromAnchor(a));
  }
}

/**
 * Initialize Swiper on a given card (if not already).
 * Returns the Swiper instance or null if skipped (≤1 slide).
 */
function initSwiperOnCard(card) {
  if (card.__swiper) return card.__swiper;

  const dom = ensureSwiperDOM(card);
  if (!dom) return null;

  // Build slides the first time (if wrapper empty)
  if (!qsa('.swiper-slide', dom.wrapper).length && dom.heroAnchor) {
    populateSlides(card, dom.wrapper, dom.heroAnchor);
  }

  const slides = qsa('.swiper-slide', dom.wrapper);
  if (slides.length <= 1) {
    // If only one slide, keep static display; remove unused controls
    dom.paginationEl?.remove();
    dom.prevBtn?.remove();
    dom.nextBtn?.remove();
    return null;
  }

  const swiper = new Swiper(dom.container, {
    slidesPerView: 1,
    spaceBetween: 8,
    speed: 260,
    loop: false,
    centeredSlides: false,
    allowTouchMove: true,

    a11y: { enabled: true },
    keyboard: { enabled: true, onlyInViewport: true },

    navigation: { prevEl: dom.prevBtn, nextEl: dom.nextBtn },
    pagination: { el: dom.paginationEl, clickable: true, bulletElement: 'button' },

    // perf
    preloadImages: false,
    lazy: false,
    watchSlidesProgress: true,

    breakpoints: {
      1024: { spaceBetween: 12 },
    },

    on: {
      init(s) {
        card.classList.add('has-slider');
        card.dataset.activeIndex = String(s.realIndex || 0);
        card.dispatchEvent(new CustomEvent('sw:active-index', {
          bubbles: true,
          detail: { index: s.realIndex || 0, swiper: s, card },
        }));
      },
      slideChange(s) {
        card.dataset.activeIndex = String(s.realIndex || 0);
        card.dispatchEvent(new CustomEvent('sw:active-index', {
          bubbles: true,
          detail: { index: s.realIndex || 0, swiper: s, card },
        }));
      },
    },
  });

  card.__swiper = swiper;
  return swiper;
}

/**
 * Lazy-init swipers on cards as they approach the viewport (desktop only).
 */
function setupLazyInit() {
  const cards = qsa('.product-card[data-pswp-gallery]');
  if (!cards.length) return;

  if (!isDesktop()) {
    // Mobile: keep PhotoSwipe-first UX. No Swiper init.
    return;
  }

  // Prebuild slides DOM for each card so anchors remain intact for PSWP
  for (const card of cards) {
    const dom = ensureSwiperDOM(card);
    if (dom && dom.heroAnchor && !qsa('.swiper-slide', dom.wrapper).length) {
      populateSlides(card, dom.wrapper, dom.heroAnchor);
    }
  }

  const io = new IntersectionObserver((entries, obs) => {
    for (const e of entries) {
      if (e.isIntersecting) {
        initSwiperOnCard(e.target);
        obs.unobserve(e.target);
      }
    }
  }, { root: null, rootMargin: '200px 0px', threshold: 0.01 });

  for (const card of cards) {
    io.observe(card);

    // Hook up per-card Zoom button → PhotoSwipe launcher
    const zoomBtn = qs('.card-zoom', card);
    if (zoomBtn) {
      zoomBtn.addEventListener('click', () => {
        const idx = card.__swiper ? card.__swiper.realIndex : 0;
        card.dispatchEvent(new CustomEvent('sw:zoom', {
          bubbles: true,
          detail: { index: idx, card },
        }));
      });
    }
  }
}

/**
 * Rebuild/destroy when crossing the desktop breakpoint.
 */
function setupResizeWatcher() {
  const reflow = debounce(() => {
    const desktop = isDesktop();
    const cards = qsa('.product-card[data-pswp-gallery]');

    for (const card of cards) {
      const hasSwiper = !!card.__swiper;

      if (desktop && !hasSwiper) {
        const dom = ensureSwiperDOM(card);
        if (dom && dom.heroAnchor && !qsa('.swiper-slide', dom.wrapper).length) {
          populateSlides(card, dom.wrapper, dom.heroAnchor);
        }
        initSwiperOnCard(card);
      } else if (!desktop && hasSwiper) {
        try { card.__swiper.destroy(true, true); } catch (_e) {}
        delete card.__swiper;
        card.classList.remove('has-slider');
      }
    }
  }, 150);

  window.addEventListener('resize', reflow, { passive: true });
  window.addEventListener('orientationchange', reflow, { passive: true });
}

/* -------- Boot -------- */
document.addEventListener('DOMContentLoaded', () => {
  if (!qs('.product-card[data-pswp-gallery]')) return;
  setupLazyInit();
  setupResizeWatcher();
});



