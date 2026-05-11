# Collections_Metadata Schema Contract

## Purpose

This README defines the authoritative column schema for the `Collections_Metadata` worksheet.

Its purpose is to:

- separate collection-level identity from product-level data
- eliminate duplication of shared narrative content
- establish a stable join layer between collections and products
- prevent drift between collection messaging and product descriptions

This document governs only the structure and responsibilities of the `Collections_Metadata` sheet.

---

## Scope

### Covered

- Required and optional columns for `Collections_Metadata`
- Relationship between `Collections_Metadata` and `SportWarehouse_ProductDB`
- Narrative, structural, and SEO responsibilities at collection level

### Not Covered

- Product-level descriptions
- Slug generation rules
- Accessibility (altText / ariaText) for individual products
- Frontend rendering logic beyond responsibility boundaries

---

## Conceptual Roles

### Collections_Metadata

Responsible for:

- Shared collection identity
- Design philosophy narrative
- Structural set-level characteristics
- Collection landing page SEO content
- Collection-level hero media

It must not contain garment-specific functional detail.

---

### SportWarehouse_ProductDB

Responsible for:

- Individual product descriptions
- support_level
- construction
- variant
- subCategory
- altText
- ariaText

ProductDB remains the authoritative source for garment-level data.

---

## Required Columns

The following columns are mandatory in `Collections_Metadata`.

### 1. `collection`

- Type: TEXT
- Acts as Primary Key
- Must exactly match the `collection` column in `SportWarehouse_ProductDB`
- Uses underscore formatting (no spaces)
- Must be unique

This is the join key between sheets.

---

### 2. `collection_display_name`

- Type: TEXT
- Human-readable version of `collection`
- May contain spaces and punctuation
- Used for frontend headings and breadcrumbs
- Not used in slug generation

Example:
- `Lift_2_0` → `Lift 2.0`
- `Rib_Seamless` → `Rib Seamless`

---

### 3. `collection_identity`

- Type: LONG TEXT
- Shared narrative paragraph for the collection

Must describe:

- Design philosophy
- Silhouette identity
- Coordinated set logic
- Structural theme

Must not describe:

- Specific garment features
- Strap details
- support_level
- Length, rise, or cut specifics

This content is merged above product-level descriptions.

---

## Optional Columns (Recommended)

These columns may be introduced to support filtering and frontend logic without parsing narrative text.

### 4. `collection_positioning`

- Type: SHORT TEXT
- One-line positioning statement
- Used in side panels or hero overlays

---

### 5. `is_seamless`

- Type: BOOLEAN
- TRUE when seamless construction defines the collection

---

### 6. `has_rib_waistband`

- Type: BOOLEAN
- TRUE when rib waistband defines coordinated set identity

---

### 7. `has_glute_sculpt`

- Type: BOOLEAN
- TRUE when shaping panels define collection identity (e.g., BBL variants)

---

## SEO Columns (Optional)

These apply only to collection landing pages.

### 8. `seo_title`

- Type: TEXT
- Used for collection page title tag

---

### 9. `seo_meta_description`

- Type: TEXT
- 150–160 characters recommended
- Used only on collection landing pages

---

## Media Columns (Optional)

These apply only to collection-level media.

### 10. `collection_hero_image`

- Type: TEXT (path)
- Path to collection-level hero image

---

### 11. `collection_hero_alt`

- Type: TEXT
- Alt text describing only the hero image
- Must not duplicate product-level altText

---

## Data Separation Rules

The following rules are invariant:

1. Product descriptions remain in `SportWarehouse_ProductDB`.
2. altText and ariaText remain product-level only.
3. `collection_identity` must not duplicate garment-level detail.
4. No support_level data may appear in `Collections_Metadata`.
5. Slug generation does not reference `Collections_Metadata`.

---

## Merge Model (Descriptive)

When rendering a product page:

Final Description =
`collection_identity`
+
`product_description`

When rendering a collection page:

Display:
- `collection_display_name`
- `collection_identity`
- `collection_positioning` (if present)

This document does not define frontend implementation mechanics.

---

## Known Gaps

The following remain open:

- Whether boolean structural flags will be used for filtering
- Whether collection landing pages will require additional metadata
- Whether SEO columns will be mandatory in future phases

These may be formalized later without altering core responsibilities.

---

## Guiding Principles

- Collection identity is layered above garment functionality.
- No duplication of shared narrative across product rows.
- Structural identity must remain separated from marketing detail.
- Schema must remain stable once adopted.
- Collection metadata exists to reduce drift, not increase complexity.

This schema is authoritative for all collection-level content going forward.
