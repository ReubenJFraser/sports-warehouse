# Adidas Showcase Content Planning Document (Updated, DB-First)

## Purpose
This document describes the content, layout, and special presentation rules for the Adidas showcase section of the Sports Warehouse website. It has been updated to reflect that **the online database is the source of truth for product images**. Any references to `/images/brands/adidas/...` should be understood as legacy development paths only.

---

## Image Source of Truth
- **Current canonical source**: Images are stored in the **online database**.
  - `item` table: contains primary product details.
  - `item_image` table (planned): supports multiple images per product (primary + gallery).
- **Legacy note**: During initial static development, images were referenced under `/images/brands/adidas/...`. These directories may still exist but are mirrors only, not authoritative.
- **Implementation rule**: All rendering logic (cards, product.php, modals) must pull from DB queries first. Filesystem should only be used as a fallback if DB records are missing.

---

## Grid and Layout Rules
- Products are displayed in a **4:5 portrait card frame** for consistency.
- Clicking on the **image area** opens a modal carousel of all product images.
- Clicking on the **name/price area** navigates to `product.php`.
- All grids are database-driven; categories, bundles, and flags are read from the DB.
- Accessibility: alt text and ARIA roles are derived from DB fields (item name, description).

---

## Sidebar and Video Logic
- Sidebar shows context-aware content (sliders, banners, videos).
- Videos may be linked to specific products, campaigns, or categories.
- For Adidas showcase, sidebar includes:
  - Branded banners
  - Optional promotional video modules
  - Special bundle rules (see below)

---

## Bundle Rules
- Some Adidas products are bundled (e.g., sports bra + tights sold together).
- Bundling logic is handled in PHP/JS but references DB fields for pricing and discount rules.
- Database flags (boolean or category-level metadata) should be used to track which items belong to bundles.

---

## Special Content
- Adidas Originals content may include:
  - Video banner featuring celebrity endorsements (e.g., Jennie from Blackpink)
  - 3-product collage (tube top, booty shorts, flared leggings) for retro streetwear theme
- These elements are defined at the **content planning level** but must still resolve all media through the DB.

---

## Notes
- **DB-first directive**: All media, images, and product info must be queried from the database.
- Filesystem images (`/images/...`) should only be used as fallback/testing resources.
- Future Packs (A/B/C) will ensure multi-image support, modal carousel wiring, and accessible rendering.
- Sidebar/video modules may differ per section, but must respect the same DB-backed rules.

---

## Next Steps
- Confirm `item_image` schema and migrate existing Adidas assets into DB.
- Audit front-end templates (`product-grid.php`, `product.php`) to ensure they query DB instead of filesystem.
- Prepare Pack A (rendering), Pack B (data schema/helpers), and Pack C (modal wiring) based on this updated DB-first content plan.
 

