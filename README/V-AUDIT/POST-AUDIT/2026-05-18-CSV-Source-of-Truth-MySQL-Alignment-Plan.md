# CSV-as-Source-of-Truth MySQL Alignment Plan (No-Write)

## Scope and constraints
- **Objective:** plan structural and data alignment so MySQL `item` aligns to the Excel-derived CSV as the source of truth.
- **No-write guardrails honored:** no DB writes, no migrations executed, no repair SQL run, no hero/image behavior changes.
- **Identity direction:** `db_itemId` is a newly created/intended database linkage field for mapping CSV rows to existing MySQL `item` records; `model_id` is a new formula-derived catalogue fingerprint used for identity validation and stability governance.

## Inputs reviewed
### Repository snapshot analysis input
- CSV source: `docs/data/SportWarehouse_ProductDB.csv`
- MySQL snapshot schema/data: `db/sportswh_dump_sanitized.sql` (`item` DDL + INSERT snapshot)

### Live local reconciliation input (DBeaver/MySQL CLI report)
- Reconciliation source: local DBeaver/MySQL report output (`tools/reports/image-sync-reconciliation.php` logic + local database state).
- Reported buckets in the live run: matched rows **46**, `csv_future_or_staging` **66**, `csv_only_candidate` **8**, `mysql_only_legacy` **16`.
- Live MySQL includes legacy `db_itemId` usage in reconciliation context.

### Scope note on source freshness
- The sanitized dump is a repository snapshot for planning and may be older than, or otherwise different from, the current live local DBeaver/MySQL schema and row state.
- Therefore, snapshot-derived counts and live reconciliation counts are intentionally tracked separately in this document.

- Identity contract context: `README/II-CONTRACTS/19-Model_ID_Generation_&_Identity_Governance_Contract.md`

## 1) CSV columns vs MySQL `item` columns (snapshot-based)

### CSV columns (45)
`brand, gender, itemName, itemName_fully_derived, model_id, product_domain, collection, model_family, subCategory, fabric, construction, seamless, scrunchFlag, invisibleFlag, neckline, strap_configuration, support_level, rise, length, variant, usage_category, usage_subtype, categoryName, parentCategory, ageGroup, sizeType, fitStyle, activityTags, price, salePrice, description, featured, images, thumbnails_json, external_item_id, campaign_or_series, altText, ariaText, videoAltText, videos, images2, CropAllowed, db_itemId, assignment_source, _images_helper_normalize`

### MySQL `item` columns in snapshot (24)
`itemId, itemName, brand, gender, subcategory, price, salePrice, description, featured, categoryId, categoryName, parentCategory, activity_tags, age_group, size_type, fit_style, images, orientation, thumbnails_json, altText, ariaText, videoAltText, videos`

> Note: `subCategory` vs `subcategory`, `activityTags` vs `activity_tags`, `ageGroup` vs `age_group`, `sizeType` vs `size_type`, `fitStyle` vs `fit_style` are semantic matches but naming-drifted.

## 2) CSV columns missing from MySQL `item` (schema alignment candidates)
30 CSV columns are absent in current `item` schema:

- `itemName_fully_derived`
- `model_id`
- `product_domain`
- `collection`
- `model_family`
- `subCategory` (case/shape mismatch vs `subcategory`)
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
- `ageGroup` (shape mismatch vs `age_group`)
- `sizeType` (shape mismatch vs `size_type`)
- `fitStyle` (shape mismatch vs `fit_style`)
- `activityTags` (shape mismatch vs `activity_tags`)
- `external_item_id`
- `campaign_or_series`
- `images2`
- `CropAllowed`
- `db_itemId`
- `assignment_source`
- `_images_helper_normalize`

## 3) `model_id` as new identity (with validation)
- `model_id` is present on every CSV row and should be promoted to canonical identity candidate.
- Before adoption, enforce validation gates:
  1. nonblank required,
  2. uniqueness required,
  3. normalization consistency (trim/lower/slug policy),
  4. immutability governance post-publication.

## 4) `model_id` audit
- Total CSV rows: **120**
- Nonblank `model_id`: **120**
- Blank `model_id`: **0**
- Duplicate `model_id` values: **1 value duplicated**
  - `nike_female_leggings` appears **2** times

## 5) `db_itemId` audit (new linkage field)
- CSV rows with populated `db_itemId`: **54 distinct IDs referenced**.
- `db_itemId` is intended to link CSV rows to existing MySQL `itemId`/`db_itemId` records when such records already exist.
- Blank `db_itemId` is expected for new CSV rows not yet imported into MySQL.

## 6) Row classification (planning-grade, snapshot-based)
Using CSV + MySQL snapshot (`db/sportswh_dump_sanitized.sql`) with `db_itemId` linkage checks and name-delta diagnostics:

- **Existing MySQL products mapped confidently:** **15**
  - (`db_itemId` present in snapshot and `itemName` unchanged)
- **Renamed products requiring manual mapping review:** **33**
  - (`db_itemId` present in snapshot but `itemName` changed)
- **New CSV products not yet in MySQL snapshot:** **72**
  - (`db_itemId` blank or not found in snapshot)
- **Old MySQL products no longer represented in CSV:** **0** in this snapshot comparison

Interpretation (snapshot-based):
- High rename count confirms why normalized `brand + itemName` is unsafe as primary identity.
- `itemName` drift during restructure is expected; it should be treated as a diagnostic signal, not identity authority.
- Review queue should prioritize verified `db_itemId` linkage, then validate with `model_id`, then route ambiguities to human review.

## 7) Live local reconciliation buckets (DBeaver/MySQL report-based)
From the live local reconciliation report (separate from repository snapshot analysis):

- **Matched rows:** **46**
- **CSV future or staging:** **66** (`csv_future_or_staging`)
- **CSV only candidate:** **8** (`csv_only_candidate`)
- **MySQL only legacy:** **16** (`mysql_only_legacy`)

Interpretation (live report-based):
- These counts reflect current local MySQL runtime state as seen by the reconciliation run, not the sanitized SQL dump snapshot.
- `db_itemId` remains a primary linkage signal for existing rows when populated and verified against MySQL identifiers.

## 8) Recommended alignment approach
Yes—proceed with a staged, reviewable workflow:

1. **Schema alignment prep (design only now):**
   - Add missing authoritative CSV columns to MySQL (or a parallel catalog table) so CSV semantics can land losslessly.
   - Add `model_id` column with planned unique index after duplicate cleanup.
2. **Create CSV staging table:**
   - `item_staging_csv` mirroring CSV headers (including helper/trace columns).
   - Load raw CSV into staging unchanged.
3. **Reviewed import/update pipeline:**
   - Validation phase (required/enum/type/nullability + duplicate `model_id` stop condition).
   - Match phase in priority order: verified `db_itemId` -> `model_id` validation -> `brand + itemName` fallback diagnostics -> manual queue.
   - Classification rule: rows with blank `db_itemId` and valid unique `model_id` are likely new insert candidates.
   - Action phase (future): INSERT new, UPDATE matched, flag unresolved for review.
   - Produce dry-run diff artifacts before any write execution.
4. **Cutover rule:**
   - Treat staging+review outputs as gate; only then schedule controlled SQL migration/import.

## 9) Runtime/editor-managed fields to preserve
During future alignment execution, explicitly preserve operational image overrides and runtime-managed selections:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

Practical rule for future implementation:
- Do not overwrite these fields from CSV import paths unless a dedicated override workflow explicitly authorizes it.

## Proposed next steps (still plan-only)
1. Draft target DDL (not executed) for missing columns and index strategy (`model_id` unique after duplicate resolution).
2. Draft staging table DDL and CSV load spec (no run).
3. Draft dry-run reconciliation SQL/report queries using verified `db_itemId` linkage, `model_id` validation, and `brand + itemName` fallback diagnostics.
4. Resolve duplicate `model_id` (`nike_female_leggings`) in source CSV governance before any unique constraint rollout.
