// /js/site-ui.js

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
  btn?.addEventListener('click', () => {
    const isOpen = drawer.classList.toggle('open');
    page?.classList.toggle('hidden', isOpen);

    btn.setAttribute('aria-expanded', isOpen);
    btn.setAttribute(
      'aria-label',
      isOpen ? 'Close menu' : 'Open menu'
    );

    btn.querySelector('.hamburger-icon').innerHTML = isOpen
      ? '<svg aria-label="Close icon" width="24" height="24" viewBox="…"><path d="M6 6L18 18M6 18L18 6"/></svg>'
      : '<svg aria-label="Menu icon" width="24" height="24" viewBox="…"><path d="M3 6h18M3 12h18M3 18h18"/></svg>';
  });

  // — Update all cart-badge counts —
  const cartCount = 0;  // placeholder; swap in your real cart logic later
  document.querySelectorAll(".cart-badge").forEach(badge => {
    badge.textContent = cartCount;
    badge.parentElement.setAttribute("aria-label", `Cart, ${cartCount} items`);
  });

  // — Kick off orientation tracking —
  updateOrientationClass();
  trackOrientationChange();
});



