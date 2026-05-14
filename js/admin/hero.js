/* ============================================================
   ADMIN HERO IMAGE TOOLING
   - PhotoSwipe fullscreen preview
   - Mobile-friendly swipe + zoom
   - Select override image (hero-edit.php)
   - Candidate images panel (hero-manager.php, read-only)
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = String(window.BASE_URL || "").replace(/\/+$/, "");
  const shortlistByItem = new Map();

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

      const wrap = btn.closest(".hero-candidates");
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

  /* ------------------------------------------------------------
     4. Shortlist Preview Panel (hero-manager.php, read-only)
     ------------------------------------------------------------ */

  const shortlistNodes = document.querySelectorAll("[data-shortlist-item-id]");

  if (shortlistNodes.length > 0) {
    const clearNode = node => {
      while (node.firstChild) {
        node.removeChild(node.firstChild);
      }
    };

    const safeText = value => {
      return (value === null || value === undefined || value === "") ? "—" : String(value);
    };

    const resolveImageUrl = path => {
      const cleanPath = String(path || "").replace(/^\/+/, "");

      return `${baseUrl}/${cleanPath}`;
    };

    const resolveChallengeUrl = (endpoint, itemId) => {
      const fallback = `admin/hero-candidates.php?item_id=${encodeURIComponent(itemId)}&include_shortlist=1`;
      const raw = String(endpoint || fallback).trim();

      if (raw === "") {
        return `${baseUrl}/${fallback}`;
      }

      if (/^https?:\/\//i.test(raw)) {
        return raw;
      }

      if (raw.startsWith("/")) {
        return raw;
      }

      const normalized = raw.replace(/^\/+/, "");

      if (normalized.startsWith("admin/")) {
        return `${baseUrl}/${normalized}`;
      }

      if (normalized.startsWith("hero-candidates.php")) {
        return `${baseUrl}/admin/${normalized}`;
      }

      return `${baseUrl}/${fallback}`;
    };

    const makeEl = (tag, className, text) => {
      const el = document.createElement(tag);

      if (className) {
        el.className = className;
      }

      if (text !== undefined) {
        el.textContent = text;
      }

      return el;
    };

    const renderShortlistState = (node, message) => {
      clearNode(node);
      node.appendChild(makeEl("div", "hero-shortlist-preview__state", message));
    };

    fetch(`${String(window.BASE_URL || "").replace(/\/+$/, "")}/admin/hero-shortlists.php?limit=100`)
      .then(res => {
        if (!res.ok) {
          throw new Error("endpoint");
        }

        return res.json();
      })
      .then(data => {
        const products = Array.isArray(data.products) ? data.products : [];
        const byItem = new Map(products.map(product => [String(product.item_id), product]));

        shortlistNodes.forEach(node => {
          const itemId = String(node.dataset.shortlistItemId || "");
          const product = byItem.get(itemId);
          shortlistByItem.set(itemId, product || null);

          if (!product) {
            renderShortlistState(node, "Shortlist unavailable");
            return;
          }

          const candidates = Array.isArray(product.recommended_candidates)
            ? product.recommended_candidates.slice(0, 3)
            : [];

          const currentHero = product.current_hero || null;
          const outsideTopThree = !!(currentHero && currentHero.current_hero_outside_top_three);
          const profile = product.active_criteria_profile || "—";
          const basis = product.shortlist_basis || "legacy_rank_placeholder";
          const challengeEndpoint = resolveChallengeUrl(product.challenge_endpoint, itemId);

          clearNode(node);

          const head = makeEl("div", "hero-shortlist-preview__head");
          head.appendChild(makeEl(
            "strong",
            "hero-shortlist-preview__top",
            candidates.length === 0 ? "Shortlist preview" : "Top candidates"
          ));
          head.appendChild(makeEl(
            "span",
            "hero-shortlist-preview__meta",
            candidates.length === 0 ? "No candidates" : safeText(product.shortlist_status || "unavailable")
          ));
          node.appendChild(head);

          const makeShortlistFoot = () => {
            const foot = makeEl("div", "hero-shortlist-preview__foot");

            foot.appendChild(makeEl("span", "hero-shortlist-preview__pill", `Profile: ${safeText(profile)}`));
            foot.appendChild(makeEl("span", "hero-shortlist-preview__pill", `Basis: ${safeText(basis)}`));

            const review = makeEl("a", "hero-shortlist-preview__action", "Review candidates");
            review.href = challengeEndpoint;
            foot.appendChild(review);

            return foot;
          };

          if (candidates.length === 0) {
            node.appendChild(makeShortlistFoot());
            return;
          }

          const thumbsWrap = makeEl("div", "hero-shortlist-preview__thumbs");

          candidates.forEach((candidate, idx) => {
            const rank = candidate.recommendation_rank || (idx + 1);
            const path = candidate.path || "";
            const thumb = makeEl("div", "hero-shortlist-thumb");

            thumb.appendChild(makeEl("span", "hero-shortlist-thumb__rank", `#${rank}`));

            const imgWrap = makeEl("div", "hero-shortlist-thumb__imgwrap");

            if (path) {
              const img = document.createElement("img");
              img.src = resolveImageUrl(path);
              img.alt = `Shortlist candidate #${rank}`;
              imgWrap.appendChild(img);
            } else {
              imgWrap.appendChild(makeEl("span", "", "—"));
            }

            thumb.appendChild(imgWrap);
            thumbsWrap.appendChild(thumb);
          });

          node.appendChild(thumbsWrap);

          const current = makeEl(
            "div",
            "hero-shortlist-preview__current",
            `Current hero: ${currentHero && currentHero.path ? "available" : "none"}`
          );

          if (outsideTopThree) {
            current.appendChild(makeEl(
              "span",
              "hero-shortlist-preview__flag",
              "Current hero outside shortlist"
            ));
          }

          node.appendChild(current);
          node.appendChild(makeShortlistFoot());
        });
      })
      .catch(() => {
        shortlistNodes.forEach(node => {
          shortlistByItem.set(String(node.dataset.shortlistItemId || ""), null);
          renderShortlistState(node, "Endpoint error / unable to load shortlist");
        });
      });
  }

  const reasonOptions = [
    ["rear_facing_unsuitable_angle", "Rear-facing / unsuitable angle"],
    ["side_facing_insufficiently_clear", "Side-facing / insufficiently clear"],
    ["product_visible_but_not_primary_hero_suitable", "Visible but not primary-hero suitable"],
    ["product_focus_conflicts_with_editorial_presentation", "Focus conflicts with editorial presentation"],
    ["full_body_model_presentation_preferred", "Full-body model presentation preferred"],
    ["face_or_model_context_needed", "Face or model context needed"],
    ["criteria_profile_probably_wrong", "Criteria profile probably wrong"],
    ["product_or_category_metadata_may_be_wrong", "Product/category metadata may be wrong"],
    ["diagnostics_or_ranking_appear_wrong", "Diagnostics/ranking appear wrong"],
    ["no_ideal_image_exists", "No ideal image exists"],
    ["human_editorial_judgement_override", "Human editorial judgement override"]
  ];

  const signalOptions = [
    ["criteria_refinement_signal", "Criteria review signal"],
    ["image_set_limitation_signal", "Image-set limitation"],
    ["metadata_issue_signal", "Metadata/category issue"],
    ["diagnostics_issue_signal", "Diagnostics/ranking issue"]
  ];

  const rationaleNodes = document.querySelectorAll("[data-rationale-item-id]");
  const closeAllRationalePanels = current => {
    rationaleNodes.forEach(node => {
      if (node !== current) {
        const panel = node.querySelector("[data-rationale-panel]");
        if (panel) panel.hidden = true;
      }
    });
  };

  const statusClasses = ["is-neutral", "is-needed", "is-saved"];
  const setStatus = (node, text, className) => {
    const badge = node.querySelector("[data-rationale-status]");
    if (!badge) return;
    badge.textContent = text;
    badge.classList.remove(...statusClasses);
    badge.classList.add(className || "is-neutral");
  };

  const createForm = node => {
    const panel = node.querySelector("[data-rationale-panel]");
    if (!panel || panel.dataset.formBuilt) return panel;

    panel.innerHTML = `
      <div class="hero-rationale__panel-state" data-rationale-feedback></div>
      <div class="hero-rationale__group" data-reason-group></div>
      <label class="hero-rationale__field">
        <span>Optional note</span>
        <textarea data-rationale-note rows="3" placeholder="Explain why the selected/current hero is preferred despite the shortlist result."></textarea>
      </label>
      <div class="hero-rationale__signals" data-signal-group></div>
      <div class="hero-rationale__actions">
        <button type="button" class="btn btn-primary btn-sm" data-rationale-save>Save rationale</button>
      </div>
    `;

    const reasonWrap = panel.querySelector("[data-reason-group]");
    reasonOptions.forEach(([code, label]) => {
      const row = document.createElement("label");
      row.className = "hero-rationale__check";
      row.innerHTML = `<input type="checkbox" data-reason-code="${code}"> <span>${label}</span>`;
      reasonWrap.appendChild(row);
    });

    const signalWrap = panel.querySelector("[data-signal-group]");
    signalOptions.forEach(([code, label]) => {
      const row = document.createElement("label");
      row.className = "hero-rationale__check hero-rationale__check--signal";
      row.innerHTML = `<input type="checkbox" data-signal-code="${code}"> <span>${label}</span>`;
      signalWrap.appendChild(row);
    });

    panel.dataset.formBuilt = "1";
    return panel;
  };

  const hydrateForm = (node, rationale) => {
    const panel = createForm(node);
    if (!panel) return;
    const reasonCodes = new Set(Array.isArray(rationale?.selected_reason_codes) ? rationale.selected_reason_codes : []);
    panel.querySelectorAll("[data-reason-code]").forEach(input => {
      input.checked = reasonCodes.has(input.dataset.reasonCode);
    });
    panel.querySelector("[data-rationale-note]").value = rationale?.optional_note || "";
    panel.querySelectorAll("[data-signal-code]").forEach(input => {
      input.checked = !!rationale?.[input.dataset.signalCode];
    });
  };

  const fetchRationale = async node => {
    const itemId = node.dataset.rationaleItemId;
    const panel = createForm(node);
    const feedback = panel.querySelector("[data-rationale-feedback]");
    feedback.textContent = "Loading rationale…";

    const res = await fetch(`${baseUrl}/admin/hero-rationale.php?item_id=${encodeURIComponent(itemId)}`);
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.error || "Failed to read rationale");

    node._rationale = data.rationale || null;
    hydrateForm(node, node._rationale);
    feedback.textContent = node._rationale ? "Loaded saved rationale." : "No saved rationale yet.";
    const toggle = node.querySelector("[data-rationale-toggle]");
    if (toggle) toggle.textContent = node._rationale ? "View / edit rationale" : "Record rationale";
  };

  const applyStatusFromState = node => {
    const itemId = String(node.dataset.rationaleItemId || "");
    const shortlist = shortlistByItem.get(itemId);
    const saved = !!node._rationale;
    const outside = !!(shortlist && shortlist.current_hero && shortlist.current_hero.current_hero_outside_top_three);

    if (saved) {
      setStatus(node, "Rationale saved", "is-saved");
      return;
    }
    if (outside) {
      setStatus(node, "Rationale needed · Current hero outside shortlist", "is-needed");
      return;
    }
    setStatus(node, "No rationale saved", "is-neutral");
  };

  const saveRationale = async node => {
    const itemId = Number(node.dataset.rationaleItemId || 0);
    const panel = createForm(node);
    const feedback = panel.querySelector("[data-rationale-feedback]");
    const shortlist = shortlistByItem.get(String(itemId)) || null;

    const selectedReasonCodes = Array.from(panel.querySelectorAll("[data-reason-code]:checked")).map(input => input.dataset.reasonCode);
    const signals = {};
    panel.querySelectorAll("[data-signal-code]").forEach(input => {
      signals[input.dataset.signalCode] = input.checked;
    });

    const currentHero = shortlist?.current_hero || {};
    const payload = {
      itemId,
      selected_hero_image: String(node.dataset.currentHeroImage || currentHero.path || "").trim(),
      current_hero_image: String(node.dataset.currentHeroImage || currentHero.path || "").trim(),
      active_criteria_profile: shortlist?.active_criteria_profile || null,
      shortlist_basis: shortlist?.shortlist_basis || null,
      current_hero_rank: currentHero.current_hero_rank ?? null,
      current_hero_outside_top_three: !!currentHero.current_hero_outside_top_three,
      selected_reason_codes: selectedReasonCodes,
      optional_note: panel.querySelector("[data-rationale-note]").value.trim(),
      ...signals
    };

    feedback.textContent = "Saving rationale…";
    const res = await fetch(`${baseUrl}/admin/hero-rationale.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.error || "Failed to save rationale");
    await fetchRationale(node);
    applyStatusFromState(node);
    feedback.textContent = "Rationale saved.";
  };

  rationaleNodes.forEach(node => {
    createForm(node);
    fetchRationale(node).catch(err => {
      const panel = node.querySelector("[data-rationale-panel]");
      const feedback = panel ? panel.querySelector("[data-rationale-feedback]") : null;
      if (feedback) feedback.textContent = err.message || "Unable to load rationale.";
    }).finally(() => {
      applyStatusFromState(node);
    });

    const toggle = node.querySelector("[data-rationale-toggle]");
    if (toggle) {
      toggle.addEventListener("click", () => {
        const panel = node.querySelector("[data-rationale-panel]");
        if (!panel) return;
        const willOpen = panel.hidden;
        closeAllRationalePanels(node);
        panel.hidden = !willOpen;
      });
    }

    node.addEventListener("click", event => {
      const saveBtn = event.target.closest("[data-rationale-save]");
      if (!saveBtn) return;
      saveRationale(node).catch(err => {
        const panel = node.querySelector("[data-rationale-panel]");
        const feedback = panel ? panel.querySelector("[data-rationale-feedback]") : null;
        if (feedback) feedback.textContent = err.message || "Unable to save rationale.";
      });
    });
  });

});
