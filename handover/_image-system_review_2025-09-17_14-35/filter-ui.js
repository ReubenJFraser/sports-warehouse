// js/filter-ui.js
// Bottom sheet filter: open/close + apply filters via URL params
// Requires markup from inc/filter-ui.php and CSS from css/components/filter.css

(function () {
  const modal         = document.getElementById('filterSheet');
  const openBtn       = document.getElementById('openFilter');
  const closeBtn      = document.getElementById('closeFilter');
  const kidsBtn       = document.getElementById('filterKids');
  const plusCheckbox  = document.getElementById('plusSizeCheck');
  const genderRadios  = Array.from(document.querySelectorAll('input[name="gender-filter"]'));

  if (!modal || !openBtn || !closeBtn) return; // fail-safe if partial not present

  // ---------- Helpers ----------
  const validGenders = new Set(['men', 'women', 'kids']);

  function getUrlParts() {
    const url = new URL(window.location.href);
    return { url, params: url.searchParams };
  }

  function setParam(params, key, value) {
    if (value === null || value === undefined || value === '') {
      params.delete(key);
    } else {
      params.set(key, value);
    }
  }

  // Drop a list of keys
  function deleteParams(params, keys) {
    keys.forEach(k => params.delete(k));
  }

  // Build destination to index.php to keep routing simple/consistent
  function navigateWith(params) {
    // Always go to index.php (unified router) to avoid pretty-route edge cases
    const dest = new URL('index.php', window.location.origin + window.location.pathname);
    // Preserve only desired params
    dest.search = params.toString();
    window.location.href = dest.toString();
  }

  // Initialize UI based on URL
  function syncUIFromUrl() {
    const { params } = getUrlParts();

    // Gender
    const g = (params.get('gender') || '').toLowerCase();
    let selected = 'all';
    if (validGenders.has(g)) {
      selected = g;
    }

    // Check the radio that matches current gender
    const toCheck = genderRadios.find(r => r.value === selected) || genderRadios.find(r => r.value === 'all');
    if (toCheck) toCheck.checked = true;

    // Plus-size
    plusCheckbox.checked = (params.get('size_type') || '').toLowerCase() === 'plus';
  }

  // Apply current control values to URL and navigate
  function applyFilters() {
    const { url, params } = getUrlParts();

    // Preserve existing relevant filters unless overridden:
    // brand, q, categoryID, sort, age_group (if you use it)
    // We'll modify gender + size_type below

    // Gender
    const chosenRadio = genderRadios.find(r => r.checked);
    const genderValue = chosenRadio ? chosenRadio.value : 'all';

    if (genderValue === 'all') {
      // In "All" (adult all) we remove gender. Ensure catalog section is explicit for clarity.
      params.delete('gender');
      // Make sure user lands in catalog context
      setParam(params, 'section', 'catalog');
    } else if (genderValue === 'kids') {
      // Kids as a distinct category
      setParam(params, 'gender', 'kids');
      // Optional: drop adult-only facets if needed
      // (We also drop plus-size for kids, see below)
    } else {
      // 'men' or 'women'
      setParam(params, 'gender', genderValue);
      // Clear section to avoid conflict with pretty routing
      params.delete('section');
    }

    // Plus-size
    if (genderValue === 'kids') {
      // Not applicable to kids; drop if present
      params.delete('size_type');
    } else {
      if (plusCheckbox.checked) {
        setParam(params, 'size_type', 'plus');
      } else {
        params.delete('size_type');
      }
    }

    // When clearing to "All", it's useful to also drop any stale paging
    if (!params.get('gender')) {
      params.delete('page');
    }

    navigateWith(params);
  }

  // ---------- Open / Close ----------
  function openSheet() {
    // display first so the animation can run
    modal.style.display = 'block';
    // force reflow to apply transition reliably
    // eslint-disable-next-line no-unused-expressions
    modal.offsetHeight;
    modal.classList.add('show');
    document.body.classList.add('modal-open');
  }

  function closeSheet() {
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300); // match CSS transition
  }

  // Backdrop click closes (only if click is directly on overlay, not content)
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeSheet();
  });

  // Esc key closes
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('show')) {
      closeSheet();
    }
  });

  // ---------- Events ----------
  openBtn.addEventListener('click', () => {
    syncUIFromUrl();
    openSheet();
  });

  closeBtn.addEventListener('click', closeSheet);

  // Apply immediately when gender changes
  genderRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      closeSheet();
      applyFilters();
    });
  });

  // Apply immediately when plus-size toggles
  plusCheckbox.addEventListener('change', () => {
    closeSheet();
    applyFilters();
  });

  // Kids button navigates to gender=kids (and drops plus-size)
  if (kidsBtn) {
    kidsBtn.addEventListener('click', () => {
      // Build from current params but force gender=kids, drop size_type
      const { params } = getUrlParts();
      setParam(params, 'gender', 'kids');
      params.delete('size_type');
      params.delete('page'); // reset paging when switching major facet
      navigateWith(params);
    });
  }

  // Optional: basic swipe-down to close on touch devices
  // (lightweight; can remove if not desired)
  let startY = null;
  modal.addEventListener('touchstart', (e) => {
    if (!modal.classList.contains('show')) return;
    startY = e.touches[0].clientY;
  }, { passive: true });

  modal.addEventListener('touchmove', (e) => {
    if (startY == null) return;
    const dy = e.touches[0].clientY - startY;
    // If user drags down > 60px on overlay, close
    if (dy > 60 && e.target === modal) {
      closeSheet();
      startY = null;
    }
  }, { passive: true });

  modal.addEventListener('touchend', () => { startY = null; });

  // Ensure UI reflects current URL on page load (useful if user navigates back)
  document.addEventListener('DOMContentLoaded', syncUIFromUrl);
})();

