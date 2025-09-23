# Pack A — Rendering (Implementation Request)

> **Patches format:** When implementation is requested, reply with **unified diffs** (git style) with file + line anchors. If functionality already exists, deliver incremental improvements with diffs rather than prose.

## Objective
Implement (or improve, if present) **4:5 portrait product cards** with **split‑click behavior**, preserving accessibility and performance.

---

## Scope (Do Now)
**Files that may be edited:**
- `css/components/card.css`
- `inc/product-grid.php`
- *(optionally)* `index.php` (card markup only, if absolutely necessary)

**Out of scope (Do NOT change now):**
- Database schema, SQL, migrations
- Modal carousel logic (`js/site-ui.js`) beyond adding a trigger hook
- Business logic or routing

---

## Requirements

### 1. 4:5 Frames
- Add/ensure a dedicated frame class, e.g. `.card-media`:
  - `aspect-ratio: 4 / 5;`
  - `overflow: hidden;`
- Images inside the frame:
  - `width: 100%; height: 100%; object-fit: cover;`
  - Keep `loading="lazy"` and `decoding="async"` if already present.

### 2. Split‑Click Behavior
- **Image area** = modal trigger only (no navigation).
- **Name/price** = link to `product.php`.
- If no suitable wrapper exists, add one of the following:

```html
<button type="button" class="card-media-trigger" aria-controls="product-modal" aria-expanded="false">
  <img ... />
</button>
```

or

```html
<div class="card-media">
  <img ... />
</div>
```

A separate trigger element may be used if preferred.

### 3. Accessibility
- Preserve/assign meaningful `alt` text (fallback: "{Product Name} — product image").
- If using a `button`, it must be keyboard reachable and have a visible focus style.

### 4. Performance & Stability
- No layout shift (CLS) from frames.
- Retain existing hover/ripple and cursor styles.
- No JS required for aspect ratio; CSS only.

### 5. Data‑Images Attribute (leave as‑is but safe)
- If touched, ensure JSON is HTML‑safe:

```php
htmlspecialchars(json_encode($images, JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
```

---

## Implementation Notes
- Make minimal markup edits.
- If the image anchor currently links to `product.php`, move that link to the **name/price** only.
- Add new classes rather than renaming existing ones to avoid breaking current CSS.

---

## Deliverables
1. **Unified diffs (git style)** with file + line anchors for:
   - `css/components/card.css`
   - `inc/product-grid.php`
   - (`index.php` only if strictly necessary)
2. A **short note (≤5 lines)** stating whether 4:5 was implemented or improved, and why.
3. A **smoke‑test checklist (≤6 bullets)** for visual verification (desktop, mobile, keyboard focus, lazy load intact).

---

## Acceptance Criteria
- Product cards render in consistent **4:5 portrait** frames.
- **Image area** triggers modal (no navigation).
- **Name/price** link to `product.php`.
- Alt text preserved or has fallback.
- No CLS; hover/focus styles work.
- No DB or modal implementation changes beyond trigger hooks.

