/* ============================================================
   ADMIN HERO IMAGE TOOLING
   - PhotoSwipe fullscreen preview
   - Mobile-friendly swipe + zoom
   - Select override image (hero-edit.php)
   - Candidate images panel (hero-manager.php, read-only)
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

  /* ------------------------------------------------------------
     3. Candidate Images Panel (hero-manager.php)
     ------------------------------------------------------------ */

  document.querySelectorAll(".hero-candidates__toggle").forEach(btn => {
    btn.addEventListener("click", async () => {

      const wrap  = btn.closest(".hero-candidates");
      const currentHeroPath = wrap.dataset.currentHero || "";
      const currentHeroSource = wrap.dataset.currentHeroSource || "auto";
      const panel = wrap.querySelector(".hero-candidates__panel");
      const itemId = wrap.dataset.itemId;

      console.log("Candidate toggle clicked", itemId);

      // toggle closed
      if (!panel.hasAttribute("hidden")) {
        panel.hidden = true;
        btn.textContent = "▸ Candidate images (ranked, explainable)";
        return;
      }

      // toggle open
      panel.hidden = false;
      btn.textContent = "▾ Candidate images (ranked, explainable)";

      // already loaded
      if (panel.dataset.loaded) return;

      panel.textContent = "Loading…";

      try {
        const res = await fetch(`${window.BASE_URL}/admin/hero-candidates.php?item_id=${itemId}`);
        const data = await res.json();

        panel.textContent = "";
        panel.dataset.loaded = "1";

        data.candidates.forEach(c => {
          const isCurrentByDom =
            currentHeroPath &&
            c.path === currentHeroPath;
          const row = document.createElement("div");
          row.className = "candidate-row";

          row.innerHTML = `
            <div class="candidate-header">
              #${c.rank} · score ${c.score}

              ${isCurrentByDom
                ? '<span class="hero-badge">Current hero</span>'
                : ''
              }

              ${isCurrentByDom && currentHeroSource === 'manual'
                ? '<span class="hero-badge hero-badge--manual">Manual</span>'
                : ''
              }

              ${c.status.is_rejected
                ? '<span class="hero-badge hero-badge--rejections">Rejected</span>'
                : ''
              }
            </div>
            
            <div class="candidate-image">
              <img src="${window.BASE_URL}/${c.path}" alt="">
            </div>

            <div class="candidate-explain">
              <div>Orientation: ${c.analysis.orientation ?? "—"}</div>
              <div>Headroom: ${c.analysis.headroom_pct ?? "—"}%</div>
              <div>Faces detected: ${c.analysis.face_count ?? 0}</div>
              <div>Crop safe: ${c.analysis.crop_safe ? "Yes" : "No"}</div>
            </div>

            <div class="candidate-actions">
              ${c.actions.can_select
                ? `<a class="btn btn-primary btn-sm" href="hero-edit.php?id=${itemId}&select=${encodeURIComponent(c.path)}">Select as hero</a>`
                : ""
              }
              ${c.actions.can_reject
                ? `<a class="btn btn-ghost btn-sm" href="hero-edit.php?id=${itemId}&reject=${encodeURIComponent(c.basename)}">Reject</a>`
                : ""
              }
            </div>
          `;

          panel.appendChild(row);
        });

      } catch (err) {
        panel.textContent = "Failed to load candidate images.";
        console.error(err);
      }
    });
  });

});

