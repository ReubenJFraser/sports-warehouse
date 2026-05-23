# CSV Governance-Deferred Summary

- Generated artifact path: docs/operations/generated/csv-governance-deferred-summary.md
- Source CSV path: docs/data/SportWarehouse_ProductDB.csv
- Console/report safety note: Generated from CSV-only diagnostics with no database connection, no SQL execution, and no importer execution.
- Governance-deferred terminology note: Governance-deferred findings are policy-decision items and must not be treated as automatic CSV edits, importer actions, frontend blockers, or database changes until explicitly approved.

## Current Row-Count Context

- Total CSV rows: 120
- Linked rows (non-blank db_itemId): 54
- Likely-new rows (blank db_itemId): 66

## Governance-Deferred Findings Summary

- subCategoryParent blank findings detected: 120 row(s).
- CropAllowed/crop_allowed pair detected in header and remains governance-deferred.
- camelCase/snake_case governance pairs detected and require canonical schema policy.
- model_id duplicate under governance review: nike_female_leggings x 2.
- db_itemId backfill policy remains deferred for likely-new rows.

## subCategoryParent Policy Question

- Finding summary: subCategoryParent has blank values and currently remains governance-deferred.
- Affected scope: taxonomy readiness and downstream mapping rules for CSV/admin/runtime usage.
- Why deferred: subCategoryParent may be derivable, optional, future taxonomy metadata, or a source field requiring remediation only after policy decision.
- Possible decisions: treat as optional; derive from categoryName/subCategory; make required in source workflow; define as future taxonomy-only field.
- Recommended next decision: approve canonical subCategoryParent policy and whether blanks are acceptable, derivable, or source-remediated.

## CropAllowed / crop_allowed Governance Question

- Finding summary: CropAllowed and crop_allowed are present as deferred governance fields.
- Why duplication matters: camelCase/snake_case duplicates can represent the same semantic field and should not be forced as separate required runtime fields.
- Possible decisions: select one canonical field name; keep both with explicit mapping; deprecate one field with migration policy.
- Recommended next decision: approve canonical naming and runtime/source mapping before required-field enforcement or remediation.

## camelCase / snake_case Duplicate-Field Governance Question

- Pairs under governance review: ageGroup/age_group, sizeType/size_type, fitStyle/fit_style, activityTags/activity_tags, CropAllowed/crop_allowed.
- Duplicate naming pairs should not be blindly treated as separate required runtime fields.
- Canonical naming requires policy or schema decision.
- No automatic remediation should occur until canonical source/runtime mapping is approved.

## model_id Duplicate Governance Question

- Finding summary: known duplicate model_id is nike_female_leggings x 2.
- Why it matters: duplicate model_id policy affects uniqueness enforcement, linking behavior, and downstream remediation ownership.
- Possible decisions: fix duplicate in Excel/CSV; formally allow duplicate for now; defer UNIQUE(model_id); assign a new model_id to one row.
- Recommended next decision: approve interim duplicate-handling policy and explicit path to canonical uniqueness.

## db_itemId Backfill Policy Question

- Current known facts: 54 rows have non-blank db_itemId; 66 rows have blank db_itemId; blank db_itemId rows are likely-new rows; db_itemId backfill remains policy-deferred.
- Why blank db_itemId should not be automatically filled: automatic backfill would imply importer/database actions that are not approved in this governance stage.
- Why backfill requires explicit approval: id ownership, sequencing, and source-of-truth responsibilities must be explicitly decided.
- Possible decisions: leave blank until insert; backfill after successful DB insert; map later through generated report; make db_itemId non-source-managed.
- Recommended next decision: approve db_itemId source-of-truth ownership and exact backfill timing policy.

## Source-of-Truth Decision Points

- Decide canonical source/runtime naming for camelCase/snake_case duplicates.
- Decide whether subCategoryParent is optional, derivable, required, or taxonomy-only.
- Decide interim and target policy for model_id uniqueness.
- Decide whether db_itemId remains source-managed or becomes system-managed post-insert.

## Recommended Next Governance Decisions

1. Approve canonical naming and mapping policy for duplicate field pairs before required-field enforcement.
2. Approve subCategoryParent policy and remediation ownership path.
3. Approve model_id duplicate policy for nike_female_leggings and downstream uniqueness strategy.
4. Approve db_itemId ownership/backfill policy and sequencing guardrails.

## No-Side-Effect Statement

- no source CSV was edited
- no database connection was opened
- no SQL was executed
- no importer execution occurred
- no admin/frontend behavior was changed
