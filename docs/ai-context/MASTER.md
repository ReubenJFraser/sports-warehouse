# Sports Warehouse — Context & Current Status (MASTER)

---

## What We’re Building
A PHP/MySQL **e-commerce prototype** (“Sports Warehouse”) progressing in phases:
1. **Current status**: We are now building a PHP/MySQL e-commerce prototype with DB-backed catalog and product detail views.
2. **Cards** must render **4:5 portrait thumbnails**.  
   - *Image click* → modal carousel  
   - *Name/price click* → `product.php`
3. **Accessibility & performance are first-class**: alt text, ARIA roles, focus trap, keyboard navigation, lazy loading.

---

## Work Already Done (Code & Assets)
- **Helpers (`inc/card-utils.php`)**
  - Filesystem MVP for image discovery: `get_product_images`, `get_primary_image`, `build_card_data_images`.
  - Plan to migrate to DB-first `item_image` table.
- **Grid (`inc/product-grid.php`)**
  - Uses `get_primary_image()`
  - Embeds `data-images` JSON
  - Split click behavior (image → modal; product info → `product.php`)
- **Modal/Carousel (`js/site-ui.js`)**
  - Vanilla JS modal scaffold (Pack C groundwork)
  - Focus trap + keyboard navigation
- **CSS (`css/components/card.css`)**
  - Cursor hints and 4:5 frame styling
  - Hover/ripple behavior retained
- **Docs converted to Markdown**
  - Mixed-Aspect-Ratio Product Cards & Detail View → `.md`
  - Content Planning (Adidas) → `.md` (DB is source of truth for images)
  - DDL extract → `ddl.md` (current: `item`, `category`; proposed: `item_image`)
  - Site structure → `site-structure.md`

---

## Repo Structure (3 Levels)
```
/ (workspace root)
├─ index.php
├─ db.php
├─ inc/
│  ├─ card-utils.php
│  ├─ product-grid.php
│  └─ site-config.php
├─ css/
│  ├─ components/
│  │  └─ card.css
│  └─ main.css
├─ js/
│  └─ site-ui.js
├─ docs/
│  └─ ai-context/
│     ├─ MASTER.md
│     ├─ README.md
│     ├─ 2025-8-17-Mixed-Aspect-Ratio_Product_Cards_and_Detail_View.md
│     ├─ content-planning-document.md
│     ├─ ddl.md
│     └─ site-structure.md
└─ images/
   └─ brands/...
```
> Adjust paths if your actual workspace differs; this is a 3-level snapshot, not a toy tree.

---

## Database

### Current Tables
```sql
-- item
CREATE TABLE IF NOT EXISTS item (
  item_id       INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255) NOT NULL,
  price         DECIMAL(10,2) NOT NULL,
  category_id   INT NOT NULL,
  images        TEXT NULL,  -- serialized, being deprecated
  CONSTRAINT fk_item_category
    FOREIGN KEY (category_id) REFERENCES category(category_id)
);

-- category
CREATE TABLE IF NOT EXISTS category (
  category_id   INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255) NOT NULL
);
```

### Proposed Table (DB = source of truth for images)
```sql
CREATE TABLE IF NOT EXISTS item_image (
  image_id      INT AUTO_INCREMENT PRIMARY KEY,
  item_id       INT NOT NULL,
  src           VARCHAR(255) NOT NULL,
  alt           VARCHAR(255),
  width         INT,
  height        INT,
  sort_order    INT DEFAULT 0,
  CONSTRAINT fk_item_image__item
    FOREIGN KEY (item_id) REFERENCES item(item_id)
);

CREATE INDEX IF NOT EXISTS idx_item_image__item_id ON item_image(item_id);
```

---

## Design & UX Rules
- **Card ratio:** All product cards use **4:5 portrait frames** (no stretching/cropping that breaks aspect).  
- **Split click behavior:**  
  - Image → modal carousel  
  - Name/price → `product.php`
- **Accessibility:** alt text required; ARIA roles on modal and controls; focus trap; fully keyboard-operable (←/→/Esc/Tab/Shift+Tab).  
- **Performance:** lazy load images; minimize DOM thrash; avoid per-card synchronous filesystem scans in production.

---

## Adidas Showcase Notes
- Banners, grid sizing, sidebar, and video modules for Adidas are part of the content plan.  
- **Images are DB-first** (resolve via `item_image`), not filesystem heuristics.  
- Content is brand-story driven (e.g., Jennie × Adidas, Originals, Athleisure) while still following the 4:5 cards and split-click rules.

---

## Pack Definitions
### Pack A — Rendering
**Goal:** Enforce 4:5 frames and split-click behavior with minimal HTML changes.  
**Files:** `inc/product-grid.php`, `inc/card-utils.php`, `css/components/card.css`, `index.php`.

### Pack B — Data
**Goal:** Shift image handling to DB-first `item_image`; helpers fetch primary + gallery via SQL; deprecate serialized `item.images`.  
**Files:** `db.php`, `inc/card-utils.php`, migration SQL/scripts.

### Pack C — Modal
**Goal:** Wire accessible modal carousel in `site-ui.js` to consume `data-images`; focus trap, ARIA, ←/→/Esc.  
**Files:** `js/site-ui.js`, minimal template hooks.

---

## Render/Data Flow
1. **Grid → Card → Product**
   - `inc/product-grid.php` renders the grid using helpers from `inc/card-utils.php`.
2. **Helpers**
   - `get_product_images(item_id)` → array of `{ src, alt, w, h }`
   - `get_primary_image(item_id)` → single `{ src, alt, w, h }`
   - `build_card_data_images(images)` → JSON string embedded in `data-images`

---

## Risk Register
- **FS vs DB mismatches:** Divergent sources if filesystem fallback lingers after DB migration.  
- **Performance:** N+1 SQL or per-card globbing; mitigate with joins + prefetch.  
- **Security/Escaping:** Ensure safe JSON in `data-images`; escape attributes; validate paths.  
- **Accessibility:** Verify ARIA roles/states; tab order; focus restoration on close.  
- **Indexes:** `item_image(item_id)` must be indexed for gallery lookups.

---

## Key Files for Audit
- `inc/card-utils.php`  
- `inc/product-grid.php`  
- `product.php` (rendering section)  
- `css/components/card.css`  
- `js/site-ui.js`  
- `index.php`  
- Any constants/config related to image handling

---

## Next-Step Plan (Per Pack)

### Pack A (Rendering)
- Verify 4:5 enforcement in CSS card frames.
- Confirm split-click wiring (image → modal; info → `product.php`).
- Validate `data-images` is present and escaped.

### Pack B (Data)
- Create `item_image` table; add `idx_item_image__item_id`.
- Update helpers to fetch primary + gallery via SQL (no filesystem globbing in production).
- Migrate legacy `item.images` to `item_image`; remove serialized field when complete.

### Pack C (Modal)
- Connect modal to `data-images` JSON.
- Confirm focus trap, ARIA roles, keyboard controls (←/→/Esc/Tab).
- Restore focus to the triggering element on close.
