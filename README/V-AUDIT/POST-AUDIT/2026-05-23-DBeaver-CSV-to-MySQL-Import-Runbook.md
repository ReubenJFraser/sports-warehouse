# 2026-05-23 DBeaver CSV-to-MySQL Import Runbook

## 1. Purpose

This runbook defines a practical DBeaver-based operational path for copying/importing Sports Warehouse CSV product data into the local DBeaver/Laragon MySQL database (`localhost:3306`, schema `sportswh`).

The immediate goal is no longer to default to additional diagnostics and reporting. The goal is to move toward controlled import/copy execution with explicit safety checks.

This document is documentation-only guidance. It does not perform any import, copy, update, insert, or publish action.

## 2. Current verified state

Current phase: local DBeaver/Laragon MySQL staging import into `localhost:3306` / `sportswh`.

Future phase: possible online/cloud-hosted deployment or migration after local staging/import is verified.

The current verified CSV and workflow state is:

- 120 total CSV product rows.
- 54 linked rows with non-blank `db_itemId` values.
- 66 likely-new rows with blank `db_itemId` values.
- Linked rows are frontend-ready under the current CSV-only policy.
- Likely-new rows are not frontend-ready under the current CSV-only policy.
- Generated remediation/readiness artifacts exist and are available.
- Known governance issues remain documented in existing planning/governance documents.

## 3. Operational principle

Core policy:

Imperfect product data should not automatically block admin-visible database import/copy.

Instead:

- Fatal structural problems block the operation.
- Import-readiness issues affect specific automated insert/update workflows.
- Frontend-readiness issues block public display for affected products.
- Admin-remediation issues should remain visible to administrators.
- Governance-deferred issues require policy decisions before enforcement.

## 4. Recommended import strategy

Recommend a staging-first strategy unless live schema safety and rollback certainty make direct import clearly safe.

### A. Staging table import path

- Import CSV rows into a staging/import table.
- Verify row counts and key split metrics.
- Review linked vs likely-new rows in database context.
- Apply controlled update/insert logic later, after approvals.

### B. Direct target table import path

- Use only if target schema, column mapping, backups, and rollback are fully confirmed.
- This path has higher operational risk.
- This path is not recommended as the first operational move.

Recommendation:

Use a staging-first DBeaver import/copy path.

## 5. Why staging-first is recommended

Staging protects live frontend/admin runtime behavior from imperfect CSV data while still allowing data copy into MySQL for inspection and controlled transformation.

Benefits:

- Safer rollback.
- Easier verification.
- Easier row-count comparison.
- Avoids frontend publication side effects.
- Allows admin/backend diagnostics and remediation after copy.
- Avoids crude all-or-nothing import logic.

## 6. Pre-import checklist

Before any DBeaver import/copy operation:

- Confirm local DBeaver connection works (`localhost:3306`, schema `sportswh`).
- Confirm target database/schema name.
- Confirm backup or restore point exists.
- Confirm current live item/product table schema.
- Confirm whether a staging table already exists.
- Confirm CSV source path and file encoding.
- Confirm generated remediation artifacts are current for this source CSV.
- Confirm unrelated generated files are not touched.
- Confirm this step will not change frontend publication behavior.

## 7. Source file and generated evidence

Primary source and supporting generated evidence:

- Source CSV: `docs/data/SportWarehouse_ProductDB.csv`
  - Purpose: authoritative source rows for DBeaver CSV import/copy.
- Excel remediation checklist: `docs/operations/generated/csv-excel-remediation-checklist.csv`
  - Purpose: remediation task support for spreadsheet/admin workflows.
- Frontend readiness summary: `docs/operations/generated/csv-frontend-readiness-summary.md`
  - Purpose: explains frontend-ready vs not-ready split and blockers.
- Admin remediation queue: `docs/operations/generated/csv-admin-remediation-queue.csv`
  - Purpose: admin-visible backlog for fixing incomplete or policy-blocked rows.
- Governance-deferred summary: `docs/operations/generated/csv-governance-deferred-summary.md`
  - Purpose: captures deferred policy issues that should not block staging copy by default.

These files support import/copy decision-making without requiring immediate live frontend publication.

## 8. Proposed staging table concept

Future staging table concept (no SQL execution in this document):

- Candidate name: `csv_product_import_staging`
- Alternate candidate name: `product_import_staging`

The exact staging table name must be confirmed before implementation.

The staging table should preserve raw CSV values and include metadata needed for later review, mapping, and controlled promotion to live tables.

## 9. Staging table column approach

Possible approaches:

- Approach A: mirror CSV headers as staging columns to preserve source values closely.
- Approach B: use a normalized staging schema that stores original values plus import metadata.

Recommended first pass:

Mirror CSV headers as staging columns, then add optional import metadata fields later if needed.

No executable SQL is provided in this task.

## 10. Column inclusion policy

Fields that should be copied into staging include:

- Identity/source fields.
- Product display fields.
- Category/taxonomy fields.
- Image/content fields.
- Accessibility fields.
- `db_itemId` linkage field.
- `model_id` field.
- Governance/deferred fields as raw source values.

Policy note:

Protected runtime/editor fields must not be overwritten in live tables by CSV-driven workflows.

## 11. Protected fields and non-overwrite policy

These fields must not be overwritten by CSV-driven import/update:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

A staging import may store raw CSV image/source fields for analysis. That does not authorize overwriting runtime/editor-selected hero fields.

## 12. Linked-row handling

Handling for the 54 linked rows:

- These rows have non-blank `db_itemId` values.
- Treat them as update candidates only after live DB match confirmation.
- They are frontend-ready under current CSV-only policy.
- Staging import is safe and recommended for review.
- Any direct live update should wait for explicit mapping/update approval.

## 13. Likely-new row handling

Handling for the 66 likely-new rows:

- These rows have blank `db_itemId` values.
- They are not frontend-ready under current CSV-only policy.
- They may still be imported into staging/admin-visible review.
- They should not be treated as public frontend-ready.
- Any insert into live item/product tables requires controlled insert policy, `db_itemId` backfill decision, and frontend publication gating.

## 14. Frontend publication gating

Importing/copying rows into staging or admin-visible tables must not automatically expose incomplete likely-new rows to the public frontend.

Possible future gating approaches:

- `frontend_ready` flag.
- `publication_status` field.
- `admin_visible` only state.
- Hidden until reviewed state.
- Category/image/price required before publication.

No frontend gating implementation is performed in this task.

## 15. DBeaver import/copy outline

Practical DBeaver runbook outline (no executed operation here):

1. Open DBeaver.
2. Connect to the local MySQL database (`localhost:3306`, schema `sportswh`).
3. Select the target database/schema.
4. Start Data Import / Import Data from CSV.
5. Choose source CSV file: `docs/data/SportWarehouse_ProductDB.csv`.
6. Choose staging table target, or run create-staging-table workflow in DBeaver.
7. Review delimiter, header usage, and encoding settings.
8. Map columns carefully and verify field alignment.
9. Run import into staging.
10. Verify imported row count and key split counts.
11. Do not auto-publish or auto-update frontend-facing live tables.

This is an operational outline only, not an executed operation.

## 16. Verification after import/copy

Post-import verification targets:

- Staging row count equals 120.
- Non-blank `db_itemId` count equals 54.
- Blank `db_itemId` count equals 66.
- `model_id` duplicate context remains understood (expected `nike_female_leggings` x 2).
- No protected fields are overwritten.
- Frontend live output remains unchanged.
- Likely-new rows remain non-public unless explicitly approved.
- Generated reports either still match source state or are intentionally regenerated only after authorized source changes.

## 17. Rollback and safety plan

Rollback and safety expectations:

- Backup/restore point exists before import.
- Staging table can be dropped and recreated if needed.
- Direct live table changes require stronger rollback controls.
- Do not proceed to live update/insert without verification completion.

## 18. What is now enough to proceed

More diagnostics are not required before staging import/copy if all are true:

- CSV file is structurally readable.
- Row counts match expected values.
- Destination/staging table plan is confirmed.
- Backup/rollback capability exists.
- Import path does not auto-publish to frontend.
- Protected fields are not overwritten.

## 19. What still remains before live update/insert

Before applying staged rows to live item/product tables, unresolved items include:

- Exact live schema verification.
- Final staging table name and schema.
- Column mapping approval.
- Update allowlist definition.
- Insert allowlist definition.
- `db_itemId` backfill policy.
- `model_id` duplicate resolution policy.
- Frontend publication gating implementation approach.
- Source-of-truth policy for admin edits after import.

## 20. Immediate next operational step

Recommended next task:

Create a minimal DBeaver staging import checklist or staging-table mapping sheet for execution readiness.

The next step should not return to general diagnostics/report mode unless a concrete import blocker is found.

## 21. Non-goals

This runbook task explicitly does not include:

- No CSV edits.
- No database writes.
- No DBeaver execution.
- No `ALTER TABLE` execution.
- No executable SQL.
- No importer implementation.
- No report generation.
- No generated report changes.
- No PHP runtime changes.
- No public route changes.
- No admin UI changes.
- No image edits.
- No Hero Manager / Hero Editor changes.
- No schema cleanup.
- No duplicate-column canonicalization.
- No `db_itemId` backfill execution.
- No `model_id` uniqueness enforcement.
- No changes to `tools/migration/csv_mysql_dry_run_importer.php` in this task.
