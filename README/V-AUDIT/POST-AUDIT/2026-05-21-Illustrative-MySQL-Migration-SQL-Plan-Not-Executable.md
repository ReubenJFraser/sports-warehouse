# 2026-05-21 Illustrative MySQL Migration SQL Plan (Not Executable)

## 1) Purpose and hard non-execution warning
This document is a planning bridge between the approved no-execution migration design and any future real migration implementation work.

Hard warning: this document is not an executable migration. It must not be run against MySQL. All SQL-shaped content below is draft planning material only.

## 2) Source inputs
Primary planning inputs:
- `docs/data/SportWarehouse_ProductDB.csv`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`
- `docs/operations/generated/2026-05-20-live-schema-verification-report.md`

Known facts preserved from the approved design and verification context:
- CSV row count: 120.
- Existing products linked by `db_itemId`: 54.
- Likely new insert candidates with blank `db_itemId`: 66.
- `model_id` exists in CSV for all rows, but duplicate exists: `nike_female_leggings` appears 2 times.
- Live `item.db_itemId` exists and is the current linkage field.
- Live `item.model_id` does not yet exist.
- Protected fields that must not be overwritten:
  - `item.hero_image`
  - `item.chosen_image`
  - `hero_override.chosen_image`
- CSV/source camelCase fields currently preferred for value source:
  - `ageGroup`
  - `sizeType`
  - `fitStyle`
  - `activityTags`
- `CropAllowed` / `crop_allowed` remains unresolved because live values differ.
- Staging/import-only fields must not automatically become runtime `item` columns:
  - `images2`
  - `assignment_source`
  - `_images_helper_normalize`

## 3) Backup / export requirement
Any future executable migration must require a full logical backup first, and restore steps must be validated before execution.

```bash
# DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
mysqldump --single-transaction --routines --triggers --databases sports_warehouse > backup_pre_migration.sql
```

No migration may proceed without verified backup completion and restore confidence in a testable environment.

## 4) Staging table design
Staging is intended to preserve source CSV data as-is before runtime mapping decisions.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
CREATE TABLE item_staging (
  brand VARCHAR(100) NULL,
  gender VARCHAR(50) NULL,
  itemName TEXT NULL,
  itemName_fully_derived VARCHAR(255) NULL,
  model_id VARCHAR(255) NULL,
  product_domain VARCHAR(100) NULL,
  collection VARCHAR(255) NULL,
  model_family VARCHAR(255) NULL,
  subCategory VARCHAR(255) NULL,
  fabric VARCHAR(255) NULL,
  construction VARCHAR(255) NULL,
  seamless VARCHAR(50) NULL,
  scrunchFlag VARCHAR(50) NULL,
  invisibleFlag VARCHAR(50) NULL,
  neckline VARCHAR(255) NULL,
  strap_configuration VARCHAR(255) NULL,
  support_level VARCHAR(255) NULL,
  rise VARCHAR(255) NULL,
  length VARCHAR(255) NULL,
  variant VARCHAR(255) NULL,
  usage_category VARCHAR(255) NULL,
  usage_subtype VARCHAR(255) NULL,
  categoryName VARCHAR(255) NULL,
  parentCategory VARCHAR(255) NULL,
  ageGroup VARCHAR(100) NULL,
  sizeType VARCHAR(100) NULL,
  fitStyle VARCHAR(100) NULL,
  activityTags TEXT NULL,
  price DECIMAL(10,2) NULL,
  salePrice DECIMAL(10,2) NULL,
  description TEXT NULL,
  featured VARCHAR(20) NULL,
  images TEXT NULL,
  thumbnails_json TEXT NULL,
  external_item_id VARCHAR(255) NULL,
  campaign_or_series VARCHAR(255) NULL,
  altText TEXT NULL,
  ariaText TEXT NULL,
  videoAltText TEXT NULL,
  videos TEXT NULL,
  images2 TEXT NULL,
  CropAllowed VARCHAR(20) NULL,
  db_itemId VARCHAR(64) NULL,
  assignment_source VARCHAR(100) NULL,
  _images_helper_normalize TEXT NULL
);
```

Notes:
- This draft keeps all 45 CSV columns.
- CSV/source names are preserved, including camelCase fields.
- Data types are intentionally broad and nullable in this planning draft.

## 5) item table candidate column additions
Only runtime candidate catalogue fields are shown below.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
ALTER TABLE item
  ADD COLUMN model_id VARCHAR(255) NULL,
  ADD COLUMN itemName_fully_derived VARCHAR(255) NULL,
  ADD COLUMN product_domain VARCHAR(100) NULL,
  ADD COLUMN collection VARCHAR(255) NULL,
  ADD COLUMN model_family VARCHAR(255) NULL,
  ADD COLUMN fabric VARCHAR(255) NULL,
  ADD COLUMN construction VARCHAR(255) NULL,
  ADD COLUMN seamless VARCHAR(50) NULL,
  ADD COLUMN scrunchFlag VARCHAR(50) NULL,
  ADD COLUMN invisibleFlag VARCHAR(50) NULL,
  ADD COLUMN neckline VARCHAR(255) NULL,
  ADD COLUMN strap_configuration VARCHAR(255) NULL,
  ADD COLUMN support_level VARCHAR(255) NULL,
  ADD COLUMN rise VARCHAR(255) NULL,
  ADD COLUMN length VARCHAR(255) NULL,
  ADD COLUMN variant VARCHAR(255) NULL,
  ADD COLUMN usage_category VARCHAR(255) NULL,
  ADD COLUMN usage_subtype VARCHAR(255) NULL,
  ADD COLUMN campaign_or_series VARCHAR(255) NULL;
```

Do not treat staging/import-only fields (`images2`, `assignment_source`, `_images_helper_normalize`) as automatic runtime `item` additions.

Exact data types and constraints require final review before any execution-grade migration is authored.

## 6) Duplicate-column governance constraints
Future migration SQL must not drop, rename, overwrite, or auto-canonicalize duplicate camelCase/snake_case pairs without explicit approval.

Governed pairs:
- `ageGroup` / `age_group`
- `sizeType` / `size_type`
- `fitStyle` / `fit_style`
- `activityTags` / `activity_tags`
- `CropAllowed` / `crop_allowed`

Current governance position:
- `ageGroup`, `sizeType`, `fitStyle`, and `activityTags` favor CSV/camelCase source values.
- `CropAllowed` / `crop_allowed` remains unresolved due to differing live values.
- No `DROP COLUMN` or `RENAME COLUMN` is included in this illustrative plan.

## 7) model_id addition and uniqueness plan
`model_id` is a formula-derived catalogue fingerprint, not a numeric primary key.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
ALTER TABLE item
  ADD COLUMN model_id VARCHAR(255) NULL;
```

Uniqueness policy:
- `UNIQUE(model_id)` is deferred.
- Duplicate `nike_female_leggings` (2 rows) must be resolved or explicitly waived before uniqueness enforcement.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
SELECT model_id, COUNT(*) AS c
FROM item_staging
GROUP BY model_id
HAVING COUNT(*) > 1;

-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
SELECT model_id, COUNT(*) AS c
FROM item
GROUP BY model_id
HAVING COUNT(*) > 1;
```

## 8) db_itemId backfill requirement
Backfill planning requirements:
- 54 existing linked rows (`db_itemId` present) must not be blindly reassigned.
- 66 candidate insert rows currently have blank `db_itemId`.
- Future execution must decide and document how `db_itemId` is assigned/backfilled after insert.

Illustrative post-insert backfill logic (pseudocode, non-executable):

```text
DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
1) Insert only staging rows where db_itemId is blank.
2) Capture newly created item.itemId values and a stable join key (for example model_id + external_item_id).
3) Write back db_itemId mapping in a controlled step with audit logging.
4) Reconcile that each inserted row has exactly one db_itemId mapping.
5) Review itemId <-> db_itemId alignment before any downstream process.
```

## 9) Protected-field exclusion rules
Any future update allowlist must explicitly exclude protected fields:
- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

Illustrative allowlist concept (planning only):

```text
DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
Permitted catalogue update domains:
- Core descriptors (brand, gender, itemName, category metadata)
- Commercial metadata (price, salePrice)
- Taxonomy/runtime candidates approved for item table
- Model fingerprint fields (model_id, collection, family, usage fields)

Explicit exclusions:
- item.hero_image
- item.chosen_image
- hero_override.chosen_image
```

No executable update statement is included in this document.

## 10) Dry-run update strategy for 54 linked rows
Future dry-run update analysis should compare staging rows to live `item` rows using `db_itemId` linkage and produce preview-only output.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
SELECT
  s.db_itemId,
  i.itemId,
  i.itemName AS before_itemName,
  s.itemName AS after_itemName,
  i.price AS before_price,
  s.price AS after_price,
  i.salePrice AS before_salePrice,
  s.salePrice AS after_salePrice
FROM item_staging s
JOIN item i ON i.db_itemId = s.db_itemId
WHERE s.db_itemId IS NOT NULL
  AND s.db_itemId <> ''
  AND (
    COALESCE(i.itemName, '') <> COALESCE(s.itemName, '') OR
    COALESCE(i.price, -1) <> COALESCE(s.price, -1) OR
    COALESCE(i.salePrice, -1) <> COALESCE(s.salePrice, -1)
  );
```

Dry-run reporting should identify:
- Rows that would update.
- Fields that would change.
- Protected fields excluded.
- Duplicate naming governance fields requiring manual decision.

No executable `UPDATE` statements are included.

## 11) Dry-run insert strategy for 66 new rows
Future dry-run insert analysis should isolate CSV rows with blank `db_itemId` and preview the insert shape.

```sql
-- DRAFT / ILLUSTRATIVE ONLY / NOT FOR EXECUTION
INSERT INTO item (model_id, itemName, brand, gender, categoryName, parentCategory, price, salePrice)
SELECT s.model_id, s.itemName, s.brand, s.gender, s.categoryName, s.parentCategory, s.price, s.salePrice
FROM item_staging s
WHERE s.db_itemId IS NULL OR s.db_itemId = '';
```

Execution readiness criteria:
- Planned insert count must reconcile to 66 before execution is considered.
- `db_itemId` assignment/backfill design must be approved for post-insert linkage.

## 12) Rollback / restore expectations
Rollback expectations for future real migration work:
- Backup before migration.
- Separate schema phase and data phase.
- Verify state after schema phase.
- Verify state after staging import.
- Verify state after dry-run reports.
- Restore from backup if execution fails at any phase.

Rollback planning must exist and be approved before any executable migration artifact is created.

## 13) Execution gates before real migration
All gates below are required before future execution:
- Backup verified.
- `model_id` duplicate resolved or approved exception recorded.
- `CropAllowed` governance decision recorded.
- Staging table design reviewed.
- Runtime column additions reviewed.
- Update allowlist reviewed.
- Protected fields explicitly excluded.
- Dry-run update and insert reports reviewed.
- Rollback plan approved.

## 14) Non-goals
This PR and this document explicitly do not do the following:
- No database writes.
- No migrations.
- No `ALTER TABLE` execution.
- No importer execution.
- No repair SQL.
- No image edits.
- No Hero Manager or Hero Editor behavior changes.
- No schema changes in this PR.
- No generated report updates in this PR.
