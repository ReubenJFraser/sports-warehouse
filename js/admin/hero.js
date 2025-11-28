/* ============================================================
   ADMIN HERO IMAGE TOOLING
   - PhotoSwipe fullscreen preview
   - Mobile-friendly swipe + zoom
   - Select override image (hero-edit.php)
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {

  /* ------------------------------------------------------------
     1. PhotoSwipe Fullscreen Viewer
     ------------------------------------------------------------ */

  // Collect all images that the admin helpers generated
  const thumbs = document.querySelectorAll("img[data-pswp-src]");
  if (thumbs.length > 0) {

      // Build PhotoSwipe data
      const galleryItems = Array.from(thumbs).map(img => ({
          src: img.dataset.pswpSrc,
          w: parseInt(img.dataset.pswpWidth, 10) || 800,
          h: parseInt(img.dataset.pswpHeight, 10) || 1000
      }));

      // Attach listener to each thumb
      thumbs.forEach((img, index) => {
          img.style.cursor = "zoom-in";

          img.addEventListener("click", () => {
              const lightbox = new PhotoSwipeLightbox({
                  dataSource: galleryItems,
                  index: index,
                  showHideAnimationType: "fade",
                  preloaderDelay: 0,
                  pswpModule: () => PhotoSwipe
              });

              lightbox.init();
          });
      });
  }


  /* ------------------------------------------------------------
     2. Hero Selection (only used in hero-edit.php)
     ------------------------------------------------------------ */

  document.querySelectorAll("[data-select-hero]").forEach(btn => {
    btn.addEventListener("click", () => {

      const imagePath = btn.getAttribute("data-select-hero");
      const input = document.querySelector("#overrideImageInput");
      if (!input) return;

      // write input for form submission
      input.value = imagePath;

      // highlight selection
      document.querySelectorAll(".candidate--selected")
        .forEach(el => el.classList.remove("candidate--selected"));

      btn.closest(".candidate").classList.add("candidate--selected");
    });
  });

});



