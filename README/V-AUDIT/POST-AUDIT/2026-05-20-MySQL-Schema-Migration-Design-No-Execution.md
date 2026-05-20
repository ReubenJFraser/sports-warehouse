# MySQL Schema Migration Design for CSV Alignment (No Execution)

## 1) Purpose and non-goals

### Purpose
This document defines a **planning-only** schema migration design to prepare live MySQL for alignment with the Excel-derived CSV source of truth at `docs/data/SportWarehouse_ProductDB.csv`.

### Non-goals / hard constraints for this phase
- No DB writes.
- No migration execution.
- No repair SQL execution.
- No importer execution.
- No image edits.
- No Hero Manager / Hero Editor behavior changes.

---

## 2) Source-of-truth model
- **CSV is the source of truth** for product catalogue payload and canonical per-row values.
- The MySQL `item` table is the **runtime implementation target**, not the authoring origin.
- A dedicated **CSV staging table** is the recommended bridge for controlled mapping and validation before any runtime upsert.
- `db_itemId` is the intended **database linkage field** where populated in CSV rows.
- `model_id` is a **formula-derived catalogue fingerprint** and should not be treated as a numeric DB primary key.

---

## 3) Current known baseline
Preserved readiness facts:
- CSV rows: **120**
- Existing products confidently linked by `db_itemId`: **54**
- Likely new insert candidates with blank `db_itemId`: **66**
- Duplicate `model_id`: **`nike_female_leggings` appears 2 times**
- Live MySQL already has `item.db_itemId` (per readiness audit)
- Repository snapshot DDL may differ from live MySQL and cannot be assumed authoritative for live schema shape

---

## 4) Proposed schema-alignment categories

### A. Already present runtime fields (keep)
Representative CSV/runtime fields already expected in runtime workflows:
`brand`, `gender`, `itemName`, `categoryName`, `parentCategory`, `price`, `salePrice`, `description`, `featured`, `images`, `thumbnails_json`, `altText`, `ariaText`, `videoAltText`, `videos`.

### B. Naming-drift fields requiring mapping
CSV-to-runtime compatibility mapping (examples):
- `subCategory` -> `subcategory`
- `ageGroup` -> `age_group`
- `sizeType` -> `size_type`
- `fitStyle` -> `fit_style`
- `activityTags` -> `activity_tags`
- `CropAllowed` <-> `crop_allowed` (**verification-first naming decision; do not duplicate blindly**)
- `db_itemId` <-> `db_item_id` (**compatibility rollout decision; do not auto-add duplicate**)

### C. Missing runtime fields to consider adding
Candidate product metadata fields not consistently guaranteed in runtime schema snapshots:
`itemName_fully_derived`, `model_id`, `product_domain`, `collection`, `model_family`, `fabric`, `construction`, `seamless`, `scrunchFlag`, `invisibleFlag`, `neckline`, `strap_configuration`, `support_level`, `rise`, `length`, `variant`, `usage_category`, `usage_subtype`, `external_item_id`, `campaign_or_series`.

### D. Helper/import-only fields (staging or audit only)
Fields that should remain out of production `item` as persistent runtime columns unless explicitly re-scoped:
- `images2`
- `assignment_source`
- `_images_helper_normalize`

### E. Protected runtime/editor fields excluded from CSV overwrite
- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

---

## 5) Candidate item-table column plan

> Action type legend: keep existing | map alias | add new column | verify live schema first | staging only / import only | protected / never overwritten by CSV

| CSV column | Proposed runtime DB column | Action type | Suggested data type | Null/default recommendation | Notes |
|---|---|---|---|---|---|
| db_itemId | db_itemId (current live) | verify live schema first; keep existing | BIGINT or INT (match live) | nullable for new rows | Do not auto-add `db_item_id`; treat snake_case standardisation as deliberate compatibility rollout. |
| model_id | model_id | add new column or verify existing first | VARCHAR(191) | nullable initially; no unique default | Add/verify first; defer `UNIQUE` until duplicate is resolved. |
| itemName | itemName | keep existing | VARCHAR(255) | NOT NULL (runtime expectation) | Display/runtime product name. |
| itemName_fully_derived | item_name_fully_derived (or mapped alias) | add new column or map alias | VARCHAR(255) or TEXT | nullable | Preserve derived-system source text separately from display name. |
| subCategory | subcategory | map alias | VARCHAR(120) | nullable | Maintain compatibility in importer/query projection. |
| ageGroup | age_group | map alias | VARCHAR(80) | nullable | Existing runtime naming convention is snake_case. |
| sizeType | size_type | map alias | VARCHAR(80) | nullable | Existing runtime naming convention is snake_case. |
| fitStyle | fit_style | map alias | VARCHAR(80) | nullable | Existing runtime naming convention is snake_case. |
| activityTags | activity_tags | map alias | TEXT | nullable | Keep parser/normalizer stable during rollout. |
| CropAllowed | crop_allowed (or CropAllowed if live) | verify live schema first; map alias | TINYINT(1) or BOOLEAN-like | nullable default NULL | Do not create both forms blindly. Select canonical name after live verification. |
| product_domain | product_domain | add new column | VARCHAR(120) | nullable | Domain taxonomy field. |
| collection | collection | add new column | VARCHAR(120) | nullable | Collection taxonomy field. |
| model_family | model_family | add new column | VARCHAR(120) | nullable | Family taxonomy field. |
| fabric | fabric | add new column | VARCHAR(120) | nullable | Material taxonomy field. |
| construction | construction | add new column | VARCHAR(120) | nullable | Construction taxonomy field. |
| seamless | seamless | add new column | TINYINT(1) or VARCHAR(16) | nullable | Type depends on CSV value normalization strategy. |
| scrunchFlag | scrunch_flag | map alias or add new column | TINYINT(1) | nullable | Normalize camelCase to snake_case through mapping layer first. |
| invisibleFlag | invisible_flag | map alias or add new column | TINYINT(1) | nullable | Same rollout approach as `scrunchFlag`. |
| neckline | neckline | add new column | VARCHAR(80) | nullable | Garment attribute. |
| strap_configuration | strap_configuration | add new column | VARCHAR(120) | nullable | Structured attribute; retain text first. |
| support_level | support_level | add new column | VARCHAR(80) | nullable | Structured attribute; retain text first. |
| rise | rise | add new column | VARCHAR(80) | nullable | Garment attribute. |
| length | length | add new column | VARCHAR(80) | nullable | Garment attribute. |
| variant | variant | add new column | VARCHAR(120) | nullable | Distinguishes product variants. |
| usage_category | usage_category | add new column | VARCHAR(120) | nullable | Taxonomy field. |
| usage_subtype | usage_subtype | add new column | VARCHAR(120) | nullable | Taxonomy field. |
| external_item_id | external_item_id | add new column | VARCHAR(191) | nullable | External linkage reference. |
| campaign_or_series | campaign_or_series | add new column | VARCHAR(191) | nullable | Campaign lineage metadata. |
| images | images | keep existing | LONGTEXT/TEXT | nullable | Runtime media pointer field. |
| thumbnails_json | thumbnails_json | keep existing | LONGTEXT/TEXT/JSON | nullable | Runtime thumbnail payload. |
| videos | videos | keep existing | LONGTEXT/TEXT/JSON | nullable | Runtime video payload. |
| images2 | (none in item) | staging only / import only | TEXT | nullable | Helper image source; avoid adding to runtime `item` by default. |
| assignment_source | (none in item) | staging only / import only | VARCHAR(120) | nullable | Provenance for audit/staging only. |
| _images_helper_normalize | (none in item) | staging only / import only | TEXT | nullable | Import helper diagnostic field only. |
| hero_image (runtime field) | hero_image | protected / never overwritten by CSV | existing live type | preserve current values | Importer allowlist must exclude from CSV updates. |
| chosen_image (runtime field) | chosen_image | protected / never overwritten by CSV | existing live type | preserve current values | Importer allowlist must exclude from CSV updates. |

---

## 6) Special handling decisions

1. **`db_itemId` decision**
   - Keep current live field (`item.db_itemId`) unless a deliberate compatibility rollout is approved.
   - Do **not** blindly add `db_item_id` as a second linkage column.

2. **`model_id` decision**
   - Add or verify presence of `model_id` for runtime linkage/fingerprint usage.
   - `UNIQUE(model_id)` must be deferred until the duplicate `nike_female_leggings` pair is resolved.

3. **`CropAllowed` / `crop_allowed` decision**
   - Verify live schema first.
   - Canonicalize through mapping/compatibility plan; do **not** create duplicate columns by default.

4. **`itemName` / `item_name_fully_derived` relationship**
   - `itemName`: runtime display/product label used across catalogue and cards.
   - `item_name_fully_derived`: derived-system canonical text snapshot for reproducibility/audit.
   - Keep relationship explicit in importer mapping so display naming does not accidentally erase derivation provenance.

5. **Media field distinction (`images`, `thumbnails_json`, `videos`, `images2`)**
   - `images`, `thumbnails_json`, `videos` are runtime-consumed media fields and remain in `item`.
   - `images2` is helper/import-only and should stay in staging/import pipeline unless a later runtime requirement is approved.

---

## 7) Protected-field preservation
Hard preservation rules for future CSV import/update execution:
- `item.hero_image` must **not** be overwritten by CSV import.
- `item.chosen_image` must **not** be overwritten by CSV import.
- `hero_override.chosen_image` must **not** be overwritten by CSV import.
- Future importer behavior should be **allowlist-based** (explicit writable fields), not broad overwrite/set-from-CSV logic.

---

## 8) Compatibility impact (likely PHP/tooling touch points)
Field mapping and/or compatibility layer updates are likely in:
- `inc/catalog-query.php`
- `inc/filters/color-facets.php`
- `inc/cards/product-grid.php`
- `inc/cards/utils.php`
- `admin/hero-edit.php`
- `admin/hero-rationale-report.php`
- `admin/image-integrity.php`
- `inc/hero/candidates.php`
- `inc/hero/authority.php`
- `tools/reports/image-sync-reconciliation.php`
- `tools/reports/generate_db_itemid_modelid_readiness_audit.php`
- `tools/importers/import_productdb_to_db.php`
- `tools/importers/regenerate_derived_system_fields.php`

Focus areas:
- alias mapping for camelCase/snake_case drift,
- keeping hero/image protections intact,
- preserving audit/report semantics for `db_itemId` and `model_id`.

---

## 9) Migration sequence recommendation (staged, reviewed, no execution in this document)
1. Backup/export live DB.
2. Verify live schema (actual production shape, not only repository snapshot DDL).
3. Create CSV staging table.
4. Import CSV into staging.
5. Validate staging row counts and required fields.
6. Resolve duplicate `model_id` (`nike_female_leggings` x2).
7. Add/adjust runtime columns according to approved mapping plan.
8. Dry-run update for 54 linked rows by `db_itemId`.
9. Dry-run insert for 66 likely new rows.
10. Execute only after formal review and approval.

---

## 10) Draft SQL policy
- Do **not** create executable SQL migration files yet in this phase.
- If SQL-like snippets are later added to planning docs, they must be clearly labeled **illustrative only / not for execution**.
- Execution-grade SQL must be separated into a future, explicitly approved migration phase with rollback planning.
