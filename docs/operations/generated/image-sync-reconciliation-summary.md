# Image Sync Reconciliation Summary

Generated: 2026-05-23 (UTC)

## Scope
This report documents **CSV-vs-local-database image-sync reconciliation status** after the taxonomy rename from `parentCategory` to `subCategoryParent` (PR #134 context) and after re-importing local `product_import_staging` from `docs/data/SportWarehouse_ProductDB.csv`.

## Current CSV Header Model
Detected headers include current taxonomy fields:
- `categoryName`
- `subCategory`
- `subCategoryParent` ✅ current header

Legacy note:
- `parentCategory` is a legacy name and is **not** a current CSV header.

## Current Row Counts
- Total CSV rows: **120**
- Total active MySQL items considered (prior local import baseline): **54**
- `csv_future_or_staging`: **66**
- `csv_only_candidate`: **8**
- `mysql_stale_relative_to_csv`: **46**
- `mysql_only_legacy`: **16**

Staging completeness checks:
- Missing `db_itemId`: **66**
- Missing images: **66**
- Missing `categoryName`: **0**
- Missing `subCategory`: **0**
- Blank `subCategoryParent` known Set component exceptions: **3**
- Blank `subCategoryParent` unexplained: **0**

## Classification Update (Important)
The 66 rows with blank `db_itemId` and blank images are now explicitly classified as:

- **`new_products_pending_database_reconciliation`**

These are **not unexplained import errors**. They are newer catalog rows introduced after the prior local website database snapshot.

### Pending Distribution
- **Ryderwear:** 62 rows pending database/image reconciliation
- **Adidas:** 4 rows pending database/image reconciliation

## Workflow Interpretation (Separated Concerns)
To avoid conflating readiness states, keep these tracks separate:

1. **Taxonomy remediation**
   - Ensure taxonomy columns are populated and aligned to current header names (`subCategoryParent`, not `parentCategory`).
2. **Image/path reconciliation**
   - Validate image/path fields only for rows already represented in database-backed workflows.
3. **Database insertion/reconciliation**
   - Controlled insertion/mapping is required for `new_products_pending_database_reconciliation` rows before assigning `db_itemId` and image linkage.
4. **Frontend publication readiness**
   - Rows should be treated as database-backed frontend products only after successful insertion/reconciliation and downstream validation.

## Next-Step Control Gate
The 66 pending rows require a later, controlled **database insertion/reconciliation workflow** before they should be interpreted as normal missing-data defects or published as frontend-ready database-backed products.
