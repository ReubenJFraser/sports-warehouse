# CSV-as-Source-of-Truth MySQL Alignment Plan (No-Write)

## Scope and constraints
- Objective: align MySQL runtime schema and PHP field usage with `docs/data/SportWarehouse_ProductDB.csv` as the current source of truth.
- This is planning only; no SQL writes, no migrations applied, no importer execution.
- Protected image fields must be preserved during any later execution:
  - `item.hero_image`
  - `item.chosen_image`
  - `hero_override.chosen_image`
- No Hero Manager / Hero Editor behavior changes in this phase.

## Baseline inputs used
- CSV header source: `docs/data/SportWarehouse_ProductDB.csv`.
- MySQL `item` schema source: `db/sportswh_dump.sql` (`CREATE TABLE item`).
- Runtime/read-path scan source: PHP and tooling query surfaces under `inc/`, `admin/`, `tools/`, `scripts/`.
- Readiness audit reference: `tools/reports/generate_db_itemid_modelid_readiness_audit.php` and generated output in `docs/operations/generated/2026-05-18-db_itemid-model_id-readiness-audit.md`.

Known readiness facts to preserve:
- CSV rows: **120**
- Existing products confidently linked by `db_itemId`: **54**
- Likely new insert candidates with blank `db_itemId`: **66**
- Duplicate `model_id`: `nike_female_leggings` appears **2** times

---

## 1) CSV columns vs repository snapshot `item` columns

Note: this section compares CSV headers to the repository SQL snapshot (`db/sportswh_dump.sql`), not a guaranteed live-production schema snapshot.

### CSV columns (45)
`brand, gender, itemName, itemName_fully_derived, model_id, product_domain, collection, model_family, subCategory, fabric, construction, seamless, scrunchFlag, invisibleFlag, neckline, strap_configuration, support_level, rise, length, variant, usage_category, usage_subtype, categoryName, parentCategory, ageGroup, sizeType, fitStyle, activityTags, price, salePrice, description, featured, images, thumbnails_json, external_item_id, campaign_or_series, altText, ariaText, videoAltText, videos, images2, CropAllowed, db_itemId, assignment_source, _images_helper_normalize`

### Repository snapshot `item` columns (23 in `db/sportswh_dump.sql`)
`itemId, itemName, brand, gender, subcategory, price, salePrice, description, featured, categoryId, categoryName, parentCategory, activity_tags, age_group, size_type, fit_style, images, orientation, thumbnails_json, altText, ariaText, videoAltText, videos`

---

## 2) CSV column classification

### A) Already present in snapshot with same name
- `brand`
- `gender`
- `itemName`
- `categoryName`
- `parentCategory`
- `price`
- `salePrice`
- `description`
- `featured`
- `images`
- `thumbnails_json`
- `altText`
- `ariaText`
- `videoAltText`
- `videos`

### B) Present with naming difference (semantic match; normalize via mapping)
- CSV `subCategory` -> MySQL `subcategory`
- CSV `ageGroup` -> MySQL `age_group`
- CSV `sizeType` -> MySQL `size_type`
- CSV `fitStyle` -> MySQL `fit_style`
- CSV `activityTags` -> MySQL `activity_tags`

### C) Missing from repository snapshot `item` and candidates for future runtime columns
- `itemName_fully_derived`
- `model_id`
- `product_domain`
- `collection`
- `model_family`
- `fabric`
- `construction`
- `seamless`
- `scrunchFlag`
- `invisibleFlag`
- `neckline`
- `strap_configuration`
- `support_level`
- `rise`
- `length`
- `variant`
- `usage_category`
- `usage_subtype`
- `external_item_id`
- `campaign_or_series`
- `CropAllowed`
- `db_itemId`

### D) Helper/import-only fields (do NOT become runtime `item` columns)
- `images2` (helper image set / staging helper)
- `assignment_source` (import trace/provenance)
- `_images_helper_normalize` (import normalization helper)

Recommendation: keep helper/import-only fields in staging/audit tables, not in production `item`.

---

## 3) Recommended final MySQL column names

Naming convention: snake_case for DB columns, with targeted compatibility bridging in importer/UI mapping.

- Keep existing snake_case runtime columns: `subcategory`, `age_group`, `size_type`, `fit_style`, `activity_tags`.
- Add new columns using snake_case:
  - `item_name_fully_derived`
  - `model_id`
  - `product_domain`
  - `collection`
  - `model_family`
  - `fabric`
  - `construction`
  - `seamless`
  - `scrunch_flag`
  - `invisible_flag`
  - `neckline`
  - `strap_configuration`
  - `support_level`
  - `rise`
  - `length`
  - `variant`
  - `usage_category`
  - `usage_subtype`
  - `external_item_id`
  - `campaign_or_series`
  - `crop_allowed`
  - `db_item_id`

Compatibility guidance:
- Preserve CSV header vocabulary as ingestion aliases (e.g., `subCategory` maps to `subcategory`; `db_itemId` maps to canonical linkage naming).
- Readiness audit evidence indicates live MySQL already uses `item.db_itemId`; do not treat that live column as missing.
- If future standardization to snake_case `db_item_id` is desired, plan it as an explicit rename/compatibility rollout (or view-layer alias), not as a blind duplicate-column add.
- `CropAllowed` vs `crop_allowed` should be treated as a naming-normalization decision that requires live-schema verification before choosing canonical runtime naming.
- Keep `itemId` as PK; DB linkage field naming should remain backward-compatible across importer/query paths.
- Add a unique index on `model_id` only after duplicate `nike_female_leggings` is resolved.

---

## 4) PHP files / query paths likely impacted by field alignment

### Customer-facing catalog and filters
- `inc/catalog-query.php` (main item select, filters, projection).
- `inc/filters/color-facets.php` (age/size filtering and item joins).
- `inc/cards/product-grid.php` and `inc/cards/utils.php` (item projection assumptions and image fallback behavior).

### Product/admin hero surfaces (must preserve protected image behavior)
- `admin/hero-edit.php`
- `admin/hero-rationale-report.php`
- `admin/image-integrity.php`
- `inc/hero/candidates.php`
- `inc/hero/authority.php`

### Import/audit/report tooling
- `tools/reports/image-sync-reconciliation.php` (already performs column-presence adaptation and protected-field messaging).
- `tools/reports/generate_db_itemid_modelid_readiness_audit.php`
- `tools/importers/import_productdb_to_db.php`
- `tools/importers/import_external_item_id.php`
- `tools/importers/diagnostic_external_item_id.php`
- `tools/importers/regenerate_derived_system_fields.php`

### Image/orientation scripts (read impact only in this phase)
- `scripts/update-orientations.php`
- `scripts/generate-item-orientations.php`
- `scripts/rebuild-image-meta.php`

---

## 5) Admin forms/reports affected
- Hero Editor: taxonomy/meta display currently relies on existing naming (`subcategory`, `age_group`) and must continue to work after CSV/DB mapping normalization.
- Hero rationale report: depends on `subcategory` and category fields in joins.
- Image Integrity admin page: reads `chosen_image`, `hero_image`, `thumbnails_json`, and `hero_override.chosen_image`; must not be altered by schema rollout.
- Reconciliation/report tooling: consumes `db_itemId`/`model_id` semantics and should be updated to support canonical DB names via explicit mapping layer.

---

## 6) Protected-field preservation contract (hard guard)
For all future import/update SQL paths:
- Never blind-overwrite:
  - `item.hero_image`
  - `item.chosen_image`
  - `hero_override.chosen_image`
- Enforce column-level allowlist updates so protected fields are excluded by default.
- Any change to these fields must stay in dedicated hero workflows, not catalog import flow.

---

## 7) Staged implementation plan (no-write execution sequence)

### Stage A — Schema migration design (draft only)
1. Draft DDL to add missing runtime columns with nullable defaults.
2. Normalize new runtime names to snake_case.
3. Do not rename legacy columns yet; use compatibility mapping in importer/query layer first.
4. Defer `UNIQUE(model_id)` until duplicate remediation is approved.

### Stage B — Staging/import design (draft only)
1. Define `item_csv_staging` with near-raw CSV header compatibility (including helper fields).
2. Load CSV raw values into staging in future execution phase.
3. Add deterministic mapping view/step from staging headers to runtime DB names.

### Stage C — Dry-run validation design
1. Validation gates:
   - row count sanity (expect 120 rows),
   - required fields nonblank (`model_id`, `itemName`, `brand`),
   - duplicate detection (`model_id`),
   - linkage diagnostics (`db_itemId` against `itemId` / `db_item_id`).
2. Classification outputs (expected):
   - 54 confidently linked existing rows,
   - 66 likely inserts,
   - explicit duplicate queue for `nike_female_leggings`.
3. Produce diff artifacts only (no write SQL execution).

### Stage D — Reviewed execution plan (future gated phase)
1. Human review and sign-off of dry-run outputs.
2. Execute schema migration first, then controlled import/update batches.
3. Enforce protected-field exclusion in all update statements.
4. Post-run reconciliation and rollback plan pre-authored before execution.

---

## 8) Explicit non-goals for this planning pass
- No database schema change execution.
- No data import execution.
- No repair SQL.
- No image mutation.
- No Hero Manager / Hero Editor behavior changes.
