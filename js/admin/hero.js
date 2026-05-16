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
  const normalizeComparablePath = value => {
    const raw = String(value || "").trim();
    if (!raw) return "";

    let normalized = raw;
    try {
      const url = new URL(raw, "https://example.invalid");
      normalized = `${url.pathname || ""}${url.search || ""}${url.hash || ""}`;
    } catch (_) {
      normalized = raw;
    }

    normalized = normalized
      .replace(/^[a-z]+:\/\/[^/]+/i, "")
      .replace(/[#?].*$/, "")
      .replace(/^\.\//, "")
      .replace(/\\/g, "/")
      .replace(/^\/+/, "")
      .replace(/\/+/g, "/")
      .trim();

    try {
      normalized = decodeURIComponent(normalized);
    } catch (_) {
      // Keep non-decodable URI fragments unchanged.
    }

    return normalized;
  };
  const humanizeCriteriaProfile = profile => {
    const normalized = String(profile || "").trim();
    const labels = {
      object_only: "Object-focused",
      body_region_first: "Body-region first",
      product_first: "Product-first",
      full_outfit: "Full outfit"
    };

    return labels[normalized] || normalized || "";
  };
  const humanizeRankingBasis = basis => {
    const normalized = String(basis || "").trim();
    if (normalized === "legacy_rank_placeholder") {
      return "temporary legacy ranking";
    }
    return normalized;
  };

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

  const heroOverrideForm = document.querySelector("#heroOverrideForm");
  const overrideInput = document.querySelector("#overrideImageInput");
  const selectHeroButtons = document.querySelectorAll("[data-select-hero]");

  if (heroOverrideForm && overrideInput && selectHeroButtons.length > 0) {
    const summaryPath = document.querySelector("[data-override-path]");
    const summaryStatus = document.querySelector("[data-override-status]");
    const previewWrap = document.querySelector("[data-override-preview-wrap]");
    const saveBtn = document.querySelector("[data-save-override]");

    const setSaveEnabled = enabled => {
      if (saveBtn) {
        saveBtn.disabled = !enabled;
      }
    };

    const setStagedState = (imagePath, sourceCard) => {
      overrideInput.value = imagePath;

      document.querySelectorAll("[data-candidate-card]").forEach(card => {
        card.classList.remove("candidate--selected");
      });

      if (sourceCard) {
        sourceCard.classList.add("candidate--selected");
      }

      if (summaryPath) summaryPath.textContent = imagePath;
      if (summaryStatus) summaryStatus.textContent = "Ready to save";
      setSaveEnabled(true);

      if (!previewWrap) return;

      const selectedImg = sourceCard ? sourceCard.querySelector("img") : null;
      const selectedSrc = selectedImg ? selectedImg.getAttribute("src") : "";

      if (!selectedSrc) return;

      let stagedImg = previewWrap.querySelector("img");
      if (!stagedImg) {
        const empty = previewWrap.querySelector("[data-override-preview-empty]");
        if (empty) empty.remove();

        stagedImg = document.createElement("img");
        previewWrap.appendChild(stagedImg);
      }

      stagedImg.src = selectedSrc;
      stagedImg.alt = "Staged override candidate";
    };

    const hasInitialOverride = overrideInput.value.trim() !== "";
    setSaveEnabled(hasInitialOverride);

    selectHeroButtons.forEach(btn => {
      btn.addEventListener("click", event => {
        event.preventDefault();

        const imagePath = String(btn.getAttribute("data-select-hero") || "").trim();
        if (!imagePath) return;

        const card = btn.closest("[data-candidate-card]");
        setStagedState(imagePath, card);
      });
    });
  }

  const diagnosticsNode = document.querySelector("[data-shortlist-diagnostics]");
  if (diagnosticsNode) {
    const itemId = String(diagnosticsNode.dataset.itemId || "").trim();
    const rankNode = diagnosticsNode.querySelector("[data-diagnostic-rank]");
    const contextNode = diagnosticsNode.querySelector("[data-diagnostic-context]");
    const profileNode = diagnosticsNode.querySelector("[data-diagnostic-profile]");
    const basisNode = diagnosticsNode.querySelector("[data-diagnostic-basis]");

    if (itemId && rankNode) {
      const currentActiveHeroPath = normalizeComparablePath(
        document.querySelector('[data-candidate-card][data-is-active-hero="1"]')?.dataset?.candidatePath || ""
      );

      fetch(`${baseUrl}/admin/hero-candidates.php?item_id=${encodeURIComponent(itemId)}&include_shortlist=1`)
        .then(res => res.ok ? res.json() : Promise.reject(new Error("diagnostics_endpoint")))
        .then(shortlist => {
          const recommended = Array.isArray(shortlist?.recommended_candidates) ? shortlist.recommended_candidates : [];
          const allCandidates = Array.isArray(shortlist?.all_candidates) ? shortlist.all_candidates : [];
          const effectiveHeroPath = normalizeComparablePath(currentActiveHeroPath || shortlist?.current_hero?.path || "");
          let heroRankText = "Current hero rank: outside candidate set";

          if (!effectiveHeroPath) {
            heroRankText = "Current hero rank: none selected";
          } else {
            const shortlistIndex = recommended.findIndex(c => normalizeComparablePath(c?.path || "") === effectiveHeroPath);
            if (shortlistIndex >= 0) {
              const rank = Number(recommended[shortlistIndex]?.recommendation_rank || shortlistIndex + 1);
              heroRankText = `Current hero rank: #${rank}`;
            } else {
              const allIndex = allCandidates.findIndex(c => normalizeComparablePath(c?.path || "") === effectiveHeroPath);
              const fallbackRank = Number(shortlist?.current_hero?.rank || 0);
              const rank = allIndex >= 0 ? Number(allCandidates[allIndex]?.rank || allIndex + 1) : fallbackRank;
              if (rank > 0) {
                heroRankText = `Current hero rank: outside top 3 · ranked #${rank}`;
              }
            }
          }

          rankNode.textContent = heroRankText;

          if (contextNode) {
            const contextParts = [];
            if (effectiveHeroPath) contextParts.push("Current hero in use");
            if (shortlist?.current_hero?.is_manual_override) contextParts.push("Manual override saved");
            if (shortlist?.current_hero?.is_in_recommended_candidates) contextParts.push("Included in current top 3");
            contextNode.textContent = contextParts.length > 0 ? contextParts.join(" · ") : "No active hero context found";
          }

          const profileLabel = humanizeCriteriaProfile(shortlist?.active_criteria_profile || "");
          if (profileNode && profileLabel) {
            profileNode.hidden = false;
            profileNode.textContent = `Criteria profile: ${profileLabel}`;
          }

          const basisLabel = humanizeRankingBasis(shortlist?.shortlist_basis || "");
          if (basisNode && basisLabel) {
            basisNode.hidden = false;
            basisNode.textContent = `Ranking basis: ${basisLabel}`;
          }
        })
        .catch(() => {
          rankNode.textContent = "Current hero rank: unavailable";
        });
    }
  }

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
        btn.textContent = "▸ Candidate images";
        return;
      }

      // toggle open
      panel.hidden = false;
      btn.textContent = "▾ Candidate images";

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

    const getHeroRankStatus = (itemId, product) => {
      const currentHeroNode = document.querySelector(`[data-item-id="${CSS.escape(String(itemId || ""))}"]`);
      const effectivePath = normalizeComparablePath(currentHeroNode?.dataset?.currentHero || product?.current_hero?.path || "");
      const topCandidates = Array.isArray(product?.recommended_candidates) ? product.recommended_candidates : [];
      const allCandidates = Array.isArray(product?.all_candidates) ? product.all_candidates : [];

      if (!effectivePath) {
        return { message: "Current hero: none", outsideTopThree: false, rank: null };
      }

      const shortlistIndex = topCandidates.findIndex(c => normalizeComparablePath(c?.path || "") === effectivePath);
      if (shortlistIndex >= 0) {
        const rank = Number(topCandidates[shortlistIndex]?.recommendation_rank || shortlistIndex + 1);
        return { message: `Current hero is shortlist #${rank}`, outsideTopThree: false, rank };
      }

      const allIndex = allCandidates.findIndex(c => normalizeComparablePath(c?.path || "") === effectivePath);
      if (allIndex >= 0) {
        const rank = Number(allCandidates[allIndex]?.rank || allIndex + 1);
        return { message: `Current hero outside top 3 · ranked #${rank}`, outsideTopThree: true, rank };
      }

      const fallbackRank = Number(product?.current_hero?.rank || 0);
      if (fallbackRank > 0) {
        return { message: `Current hero outside top 3 · ranked #${fallbackRank}`, outsideTopThree: true, rank: fallbackRank };
      }

      return { message: "Current hero outside candidate set", outsideTopThree: true, rank: null };
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

          const rankStatus = getHeroRankStatus(itemId, product);
          const currentHero = product.current_hero || null;
          product.current_hero = {
            ...(currentHero || {}),
            current_hero_outside_top_three: !!rankStatus.outsideTopThree,
            current_hero_rank: rankStatus.rank
          };

          const rationaleNode = document.querySelector(`[data-rationale-item-id="${CSS.escape(itemId)}"]`);
          if (rationaleNode && typeof applyStatusFromState === "function") {
            applyStatusFromState(rationaleNode);
          }
          const challengeEndpoint = resolveChallengeUrl(product.challenge_endpoint, itemId);
          const reviewHref = `${baseUrl}/admin/hero-edit.php?id=${encodeURIComponent(itemId)}`;

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
            const review = makeEl("a", "hero-shortlist-preview__action", "Review / change hero");
            review.href = reviewHref;
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

          const current = makeEl("div", "hero-shortlist-preview__current", rankStatus.message);

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
  const decisionTypeOptions = [
    ["", "— Select decision type —"],
    ["accepted_top_candidate", "Accepted top candidate"],
    ["corrected_old_stored_hero", "Corrected old stored hero"],
    ["manual_override_against_top_candidate", "Manual override against top candidate"],
    ["paired_product_differentiation", "Paired product differentiation"],
    ["product_detail_closeup_preferred", "Product detail close-up preferred"],
    ["model_personality_hero_preferred", "Model personality hero preferred"],
    ["campaign_background_context_preferred", "Campaign/background context preferred"],
    ["missing_image_data_failure", "Missing image data failure"],
    ["temporary_best_available_image", "Temporary best available image"]
  ];

  const rationaleNodes = document.querySelectorAll("[data-rationale-item-id]");
  const closeAllRationalePanels = current => {
    rationaleNodes.forEach(node => {
      if (node !== current) {
        const panel = node.querySelector("[data-rationale-panel]");
        if (panel) panel.hidden = true;
        const toggle = node.querySelector("[data-rationale-toggle]");
        if (toggle) toggle.textContent = node._rationale ? "View / edit rationale" : "Record rationale";
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
      <div class="hero-rationale__compare" data-rationale-compare></div>
      <div class="hero-rationale__panel-state hero-rationale__panel-note" data-rationale-compare-note></div>
      <label class="hero-rationale__field">
        <span>Decision type</span>
        <select data-rationale-decision-type></select>
      </label>
      <label class="hero-rationale__check hero-rationale__check--signal">
        <input type="checkbox" data-rationale-counts-refinement> <span>Counts toward criteria refinement</span>
      </label>
      <label class="hero-rationale__check hero-rationale__check--signal">
        <input type="checkbox" data-rationale-data-quality-only> <span>Data-quality only / missing image issue</span>
      </label>
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
    const decisionSelect = panel.querySelector("[data-rationale-decision-type]");
    decisionTypeOptions.forEach(([value, label]) => {
      const opt = document.createElement("option");
      opt.value = value;
      opt.textContent = label;
      decisionSelect.appendChild(opt);
    });

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
    panel.hidden = true;
    return panel;
  };

  const getCompactSummaryNode = node => {
    let summary = node.querySelector("[data-rationale-compact]");
    if (summary) return summary;

    const statusRow = node.querySelector(".hero-rationale__status-row");
    if (!statusRow) return null;

    summary = document.createElement("div");
    summary.className = "hero-rationale__compact";
    summary.setAttribute("data-rationale-compact", "");
    statusRow.insertAdjacentElement("afterend", summary);
    return summary;
  };

  const updateToggleLabel = node => {
    const toggle = node.querySelector("[data-rationale-toggle]");
    const panel = node.querySelector("[data-rationale-panel]");
    if (!toggle || !panel) return;

    if (!panel.hidden) {
      toggle.textContent = "Hide rationale";
      return;
    }
    toggle.textContent = node._rationale ? "View / edit rationale" : "Record rationale";
  };

  const updateCompactSummary = node => {
    const summary = getCompactSummaryNode(node);
    if (!summary) return;

    if (!node._rationale) {
      summary.innerHTML = "";
      summary.hidden = true;
      return;
    }

    const savedReasons = Array.isArray(node._rationale.selected_reason_codes) ? node._rationale.selected_reason_codes.length : 0;
    const tags = [`<span class="hero-rationale__compact-pill">Reasons: ${savedReasons}</span>`];

    if (node._rationale.criteria_refinement_signal) tags.push('<span class="hero-rationale__compact-pill">Criteria review</span>');
    if (node._rationale.image_set_limitation_signal) tags.push('<span class="hero-rationale__compact-pill">Image-set limitation</span>');
    if (node._rationale.metadata_issue_signal) tags.push('<span class="hero-rationale__compact-pill">Metadata/category issue</span>');
    if (node._rationale.diagnostics_issue_signal) tags.push('<span class="hero-rationale__compact-pill">Diagnostics/ranking issue</span>');

    summary.innerHTML = tags.join("");
    summary.hidden = false;
  };

  const setPanelOpen = (node, open) => {
    const panel = node.querySelector("[data-rationale-panel]");
    if (!panel) return;
    panel.hidden = !open;
    updateToggleLabel(node);
  };

  const canonicalizeRationaleState = state => {
    const reasonCodes = Array.isArray(state?.selected_reason_codes) ? state.selected_reason_codes : [];
    const normalizedCodes = Array.from(new Set(reasonCodes.map(code => String(code || "").trim()).filter(Boolean))).sort();

    return {
      itemId: Number(state?.itemId || 0),
      selected_hero_image: String(state?.selected_hero_image || "").trim(),
      current_hero_image: String(state?.current_hero_image || "").trim(),
      active_criteria_profile: String(state?.active_criteria_profile || "").trim(),
      shortlist_basis: String(state?.shortlist_basis || "").trim(),
      current_hero_rank: state?.current_hero_rank === null || state?.current_hero_rank === undefined || state?.current_hero_rank === "" ? null : Number(state.current_hero_rank),
      current_hero_outside_top_three: !!state?.current_hero_outside_top_three,
      selected_reason_codes: normalizedCodes,
      optional_note: String(state?.optional_note || "").trim(),
      criteria_refinement_signal: !!state?.criteria_refinement_signal,
      image_set_limitation_signal: !!state?.image_set_limitation_signal,
      metadata_issue_signal: !!state?.metadata_issue_signal,
      diagnostics_issue_signal: !!state?.diagnostics_issue_signal
      ,decision_type: String(state?.decision_type || "").trim()
      ,counts_toward_criteria_refinement: !!state?.counts_toward_criteria_refinement
      ,data_quality_only: !!state?.data_quality_only
      ,comparison_target_role: String(state?.comparison_target_role || "").trim()
      ,cross_cutting_signal_codes: String(state?.cross_cutting_signal_codes || "").trim()
      ,product_specific_reason_codes: String(state?.product_specific_reason_codes || "").trim()
      ,reviewer_note: String(state?.reviewer_note || "").trim()
      ,selected_image_path: String(state?.selected_image_path || "").trim()
      ,selected_image_rank_snapshot: state?.selected_image_rank_snapshot === null || state?.selected_image_rank_snapshot === undefined || state?.selected_image_rank_snapshot === "" ? null : Number(state.selected_image_rank_snapshot)
      ,selected_image_score_snapshot: state?.selected_image_score_snapshot === null || state?.selected_image_score_snapshot === undefined || state?.selected_image_score_snapshot === "" ? null : String(state.selected_image_score_snapshot).trim()
      ,ranked_1_image_path_snapshot: String(state?.ranked_1_image_path_snapshot || "").trim()
    };
  };
  const normalizePath = value => normalizeComparablePath(value);
  const findRankedTop = shortlist => (Array.isArray(shortlist?.recommended_candidates) && shortlist.recommended_candidates[0]) || (Array.isArray(shortlist?.all_candidates) && shortlist.all_candidates[0]) || null;
  const findCandidateByPath = (shortlist, path) => {
    const all = Array.isArray(shortlist?.all_candidates) ? shortlist.all_candidates : [];
    return all.find(c => normalizePath(c?.path) === normalizePath(path)) || null;
  };
  const buildSnapshotContext = node => {
    const itemId = String(node.dataset.rationaleItemId || "");
    const shortlist = shortlistByItem.get(itemId) || null;
    const currentPath = normalizePath(node.dataset.currentHeroImage || shortlist?.current_hero?.path || "");
    const rankedTop = findRankedTop(shortlist);
    const rankedPath = normalizePath(rankedTop?.path);
    const selectedCandidate = findCandidateByPath(shortlist, currentPath);
    const matchTop = !!(currentPath && rankedPath && currentPath === rankedPath);
    return { shortlist, currentPath, rankedTop, rankedPath, selectedCandidate, matchTop };
  };
  const renderComparePanel = (node, rationale) => {
    const panel = createForm(node);
    const box = panel.querySelector("[data-rationale-compare]");
    const note = panel.querySelector("[data-rationale-compare-note]");
    const cx = buildSnapshotContext(node);
    const selectedPath = normalizePath(rationale?.selected_image_path || cx.currentPath);
    const rankedPath = normalizePath(rationale?.ranked_1_image_path_snapshot || cx.rankedPath);
    const dispPath = normalizePath(rationale?.displaced_current_hero_path_snapshot || "");
    const selectedRank = rationale?.selected_image_rank_snapshot ?? cx.selectedCandidate?.rank ?? cx.selectedCandidate?.recommendation_rank ?? null;
    const selectedScore = rationale?.selected_image_score_snapshot ?? cx.selectedCandidate?.score ?? null;
    const rankedScore = rationale?.ranked_1_image_score_snapshot ?? cx.rankedTop?.score ?? null;
    const cards = [];
    const makeCard = (title, path, meta) => `<div class="hero-rationale__compare-card"><div class="hero-rationale__compare-title">${title}</div>${path ? `<img src="${resolveImageUrl(path)}" alt="${title}">` : '<div class="hero-rationale__compare-empty">No image</div>'}<div class="hero-rationale__compare-path">${path || "—"}</div><div class="hero-rationale__compare-meta">${meta}</div></div>`;
    cards.push(makeCard("System ranked #1", rankedPath, `Rank #1${rankedScore !== null && rankedScore !== undefined ? ` · Score ${rankedScore}` : ""}`));
    cards.push(makeCard("Human selected/current hero", selectedPath, `${selectedRank ? `Rank #${selectedRank}` : "Rank unknown"} · ${cx.matchTop ? "Matches #1" : "Differs from #1"}`));
    if (dispPath && dispPath !== selectedPath && dispPath !== rankedPath) cards.push(makeCard("Displaced stored/current", dispPath, `${rationale?.displaced_current_hero_rank_snapshot ? `Rank #${rationale.displaced_current_hero_rank_snapshot}` : "Context only"}`));
    box.innerHTML = cards.join("");
    const noSnap = !(rationale && (rationale.selected_image_path || rationale.ranked_1_image_path_snapshot));
    note.textContent = cx.matchTop
      ? "Current hero matches the system-ranked #1 candidate. A rationale is optional and normally should not count toward criteria refinement unless there is another reason to record it."
      : (rankedPath ? "Current hero differs from the system-ranked #1 candidate. This rationale can explain why the human-selected image should beat the ranked #1 image." : "No ranked candidate available. Use data-quality-only if ranking inputs are missing/broken.");
    if (noSnap && rationale) note.textContent += " Snapshot fields not yet saved for this rationale.";
  };

  const setSaveButtonState = (node, state) => {
    const panel = createForm(node);
    const btn = panel ? panel.querySelector("[data-rationale-save]") : null;
    if (!btn) return;

    btn.classList.remove("is-saving", "is-saved");
    btn.disabled = false;

    if (state === "saving") {
      btn.textContent = "Saving...";
      btn.disabled = true;
      btn.classList.add("is-saving");
      return;
    }
    if (state === "saved") {
      btn.textContent = "Saved ✓";
      btn.classList.add("is-saved");
      return;
    }
    if (state === "changes") {
      btn.textContent = "Save changes";
      return;
    }
    btn.textContent = "Save rationale";
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
    const decisionSelect = panel.querySelector("[data-rationale-decision-type]");
    if (rationale?.decision_type) {
      decisionSelect.value = rationale.decision_type;
    } else {
      const cx = buildSnapshotContext(node);
      decisionSelect.value = !cx.rankedPath || !cx.currentPath ? "missing_image_data_failure" : (cx.matchTop ? "accepted_top_candidate" : "manual_override_against_top_candidate");
    }
    panel.querySelector("[data-rationale-counts-refinement]").checked = !!rationale?.counts_toward_criteria_refinement;
    panel.querySelector("[data-rationale-data-quality-only]").checked = !!rationale?.data_quality_only;
    renderComparePanel(node, rationale);
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
    node._rationaleBaseline = canonicalizeRationaleState({ ...(node._rationale || {}), itemId: Number(itemId || 0) });
    node._rationaleSaving = false;
    feedback.textContent = node._rationale ? "Loaded saved rationale." : "No saved rationale yet.";
    setSaveButtonState(node, node._rationale ? "saved" : "idle");
    updateCompactSummary(node);
    setPanelOpen(node, !node._rationale);
  };

  const applyStatusFromState = node => {
    const itemId = String(node.dataset.rationaleItemId || "");
    const shortlist = shortlistByItem.get(itemId);
    const saved = !!node._rationale;
    const cx = buildSnapshotContext(node);
    const outside = !!(shortlist && shortlist.current_hero && shortlist.current_hero.current_hero_outside_top_three);

    if (saved) {
      setStatus(node, "Rationale saved", "is-saved");
      return;
    }
    if (!shortlist) {
      setStatus(node, "Rationale status unavailable · shortlist unavailable", "is-neutral");
      return;
    }
    if (cx.currentPath && cx.rankedPath && cx.matchTop) {
      setStatus(node, "Rationale optional · Current hero matches ranked #1", "is-neutral");
      return;
    }
    if (!cx.rankedPath) {
      setStatus(node, "Rationale status unavailable · ranked #1 unavailable", "is-neutral");
      return;
    }
    if (outside) {
      setStatus(node, "Rationale needed · Current hero outside shortlist", "is-needed");
      return;
    }
    setStatus(node, "Rationale needed · Current hero differs from ranked #1", "is-needed");
  };

  const saveRationale = async node => {
    if (node._rationaleSaving) return;

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
      decision_type: panel.querySelector("[data-rationale-decision-type]").value || null,
      counts_toward_criteria_refinement: !!panel.querySelector("[data-rationale-counts-refinement]").checked,
      data_quality_only: !!panel.querySelector("[data-rationale-data-quality-only]").checked,
      ...signals
    };
    const cx = buildSnapshotContext(node);
    const signalCodes = [];
    if (signals.criteria_refinement_signal) signalCodes.push("criteria_review_signal");
    if (signals.image_set_limitation_signal) signalCodes.push("image_set_limitation");
    if (signals.metadata_issue_signal) signalCodes.push("metadata_category_issue");
    if (signals.diagnostics_issue_signal) signalCodes.push("diagnostics_ranking_issue");
    if (payload.data_quality_only) signalCodes.push("data_quality_blocker");
    Object.assign(payload, {
      product_name_snapshot: cx.shortlist?.item_name || null,
      brand_snapshot: cx.shortlist?.brand || null,
      selected_image_path: cx.currentPath || null,
      selected_image_rank_snapshot: cx.selectedCandidate?.rank ?? cx.selectedCandidate?.recommendation_rank ?? currentHero.current_hero_rank ?? null,
      selected_image_score_snapshot: cx.selectedCandidate?.score ?? null,
      selected_image_role: "human_selected_current",
      ranked_1_image_path_snapshot: cx.rankedPath || null,
      ranked_1_image_score_snapshot: cx.rankedTop?.score ?? null,
      ranked_1_image_role: cx.rankedPath ? "top_ranked_candidate" : null,
      ranked_1_reason_snapshot: cx.rankedTop?.score_reason || null,
      criteria_profile_snapshot: shortlist?.active_criteria_profile || null,
      shortlist_basis_snapshot: shortlist?.shortlist_basis || null,
      comparison_target_role: cx.rankedPath ? "top_ranked_candidate" : "missing_broken_image",
      product_specific_reason_codes: selectedReasonCodes.length ? JSON.stringify(selectedReasonCodes) : null,
      cross_cutting_signal_codes: signalCodes.length ? JSON.stringify(signalCodes) : null,
      reviewer_note: payload.optional_note || null,
      candidate_snapshot_json: shortlist ? JSON.stringify({recommended_candidates: shortlist.recommended_candidates || [], all_candidates: shortlist.all_candidates || []}) : null
    });

    const normalizedCurrent = canonicalizeRationaleState(payload);
    const baseline = node._rationaleBaseline || canonicalizeRationaleState({ itemId });
    if (JSON.stringify(normalizedCurrent) === JSON.stringify(baseline)) {
      feedback.textContent = "No changes to save.";
      setSaveButtonState(node, "saved");
      return;
    }

    node._rationaleSaving = true;
    setSaveButtonState(node, "saving");
    feedback.textContent = "Saving rationale...";

    try {
      const res = await fetch(`${baseUrl}/admin/hero-rationale.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error || "Failed to save rationale");

      await fetchRationale(node);
      applyStatusFromState(node);
      feedback.textContent = data.message || "Rationale saved.";
      setSaveButtonState(node, "saved");
      setTimeout(() => {
        if (node._rationaleSaving) return;
        const btn = panel.querySelector("[data-rationale-save]");
        if (btn && btn.textContent === "Save changes") return;
        setPanelOpen(node, false);
      }, 800);
    } finally {
      node._rationaleSaving = false;
    }
  };

  rationaleNodes.forEach(node => {
    createForm(node);
    const panel = node.querySelector("[data-rationale-panel]");
    if (panel) {
      const applyDecisionDefaults = () => {
        const decision = panel.querySelector("[data-rationale-decision-type]")?.value || "";
        const dataQ = panel.querySelector("[data-rationale-data-quality-only]");
        const counts = panel.querySelector("[data-rationale-counts-refinement]");
        if (!dataQ || !counts) return;
        if (decision === "missing_image_data_failure") {
          dataQ.checked = true; counts.checked = false;
        } else if (decision === "manual_override_against_top_candidate" && !dataQ.checked) {
          counts.checked = true;
        } else if (decision === "corrected_old_stored_hero" || decision === "accepted_top_candidate") {
          counts.checked = false;
        }
      };
      panel.querySelector("[data-rationale-decision-type]")?.addEventListener("change", applyDecisionDefaults);
      const markDirty = () => {
        if (node._rationaleSaving) return;
        const shortlist = shortlistByItem.get(String(node.dataset.rationaleItemId || "")) || null;
        const currentHero = shortlist?.current_hero || {};
        const currentPayload = {
          itemId: Number(node.dataset.rationaleItemId || 0),
          selected_hero_image: String(node.dataset.currentHeroImage || currentHero.path || "").trim(),
          current_hero_image: String(node.dataset.currentHeroImage || currentHero.path || "").trim(),
          active_criteria_profile: shortlist?.active_criteria_profile || null,
          shortlist_basis: shortlist?.shortlist_basis || null,
          current_hero_rank: currentHero.current_hero_rank ?? null,
          current_hero_outside_top_three: !!currentHero.current_hero_outside_top_three,
          selected_reason_codes: Array.from(panel.querySelectorAll("[data-reason-code]:checked")).map(input => input.dataset.reasonCode),
          optional_note: panel.querySelector("[data-rationale-note]").value,
          criteria_refinement_signal: !!panel.querySelector('[data-signal-code="criteria_refinement_signal"]')?.checked,
          image_set_limitation_signal: !!panel.querySelector('[data-signal-code="image_set_limitation_signal"]')?.checked,
          metadata_issue_signal: !!panel.querySelector('[data-signal-code="metadata_issue_signal"]')?.checked,
          diagnostics_issue_signal: !!panel.querySelector('[data-signal-code="diagnostics_issue_signal"]')?.checked
          ,decision_type: panel.querySelector("[data-rationale-decision-type]").value || null
          ,counts_toward_criteria_refinement: !!panel.querySelector("[data-rationale-counts-refinement]").checked
          ,data_quality_only: !!panel.querySelector("[data-rationale-data-quality-only]").checked
        };
        const current = canonicalizeRationaleState(currentPayload);
        const baseline = node._rationaleBaseline || canonicalizeRationaleState({ itemId: current.itemId });
        setSaveButtonState(node, JSON.stringify(current) === JSON.stringify(baseline) ? "saved" : "changes");
      };
      panel.addEventListener("input", markDirty);
      panel.addEventListener("change", markDirty);
    }
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
        setPanelOpen(node, willOpen);
      });
    }

    node.addEventListener("click", event => {
      const saveBtn = event.target.closest("[data-rationale-save]");
      if (!saveBtn) return;
      saveRationale(node).catch(err => {
        const panel = node.querySelector("[data-rationale-panel]");
        const feedback = panel ? panel.querySelector("[data-rationale-feedback]") : null;
        if (feedback) feedback.textContent = err.message || "Unable to save rationale.";
        setSaveButtonState(node, "changes");
        node._rationaleSaving = false;
      });
    });
  });

});
