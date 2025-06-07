// script.js

document.addEventListener("DOMContentLoaded", () => {
    // Toggle mobile product categories
    const pill = document.querySelector(".pill-toggle");
    const mobileCats = document.querySelector(".mobile-product-categories");
    pill?.addEventListener("click", () => {
      mobileCats?.classList.toggle("open");
    });
  
    // (If you also have a mobile nav drawer…)
    const menuBtn = document.querySelector(".mobile-header .icon-button");
    const navDrawer = document.querySelector(".mobile-nav-drawer");
    menuBtn?.addEventListener("click", () => {
      navDrawer?.classList.toggle("open");
    });
  
    // Update all cart badges:
    const cartCount = 0;
    document.querySelectorAll(".cart-badge").forEach(badge => {
        badge.textContent = cartCount;
        badge.parentElement.setAttribute("aria-label", `Cart, ${cartCount} items`);
    });
});
  