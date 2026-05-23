# 2026-05-21 Dry-Run Importer Planning Consistency Review

## 1. Purpose
This document is a planning-consistency review only.

It checks internal alignment across the current CSV-to-MySQL dry-run importer planning and governance artifacts before any future implementation task is considered.

This review does not:
- implement an importer,
- generate reports,
- approve execution,
- change the CSV,
- write to the database,
- modify PHP,
- modify generated report files,
- modify images,
- modify Hero Manager or Hero Editor files.

## 2. Source documents reviewed

### Core dry-run planning documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Pseudocode-Spec.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Implementation-Checklist.md`

### Governance blocker documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`

### Migration design/reference documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

### Source CSV reference
- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Known migration facts consistency check

| fact | consistent? | documents where it appears | notes |
|---|---|---|---|
| 120 CSV rows | yes | allowlist plan, report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan, model_id duplicate plan | No count conflict identified. |
| 54 existing rows linked by db_itemId | yes | allowlist plan, report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan | Treated as linked update scope baseline. |
| 66 likely new insert candidates with blank db_itemId | yes | allowlist plan, report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan | Treated as insert-preview scope baseline. |
| db_itemId is the current live linkage field | yes | report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan | Consistently used for linked-row matching. |
| model_id exists in CSV and has one documented duplicate | yes | report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan, model_id duplicate plan | Duplicate condition explicitly documented. |
| UNIQUE(model_id) remains deferred until duplicate resolution is applied | yes | report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan, model_id duplicate plan | Consistently treated as blocked/deferred. |
| CropAllowed/crop_allowed remain deferred from first-pass import scope | yes | allowlist plan, report design, pseudocode spec, implementation checklist, crop governance plan, schema migration design, illustrative SQL plan | No approval found for first-pass inclusion. |
| item.hero_image, item.chosen_image, and hero_override.chosen_image are protected runtime/editor fields | yes | allowlist plan, report design, pseudocode spec, implementation checklist, schema migration design, illustrative SQL plan | Always excluded from CSV write scope. |
| no CSV edits, DB writes, executable SQL, importer execution, PHP changes, image changes, or generated report changes are approved | yes | report design, pseudocode spec, implementation checklist, governance/planning docs in sequence | Read-only planning boundary preserved. |

## 4. Allowlist consistency check

| field or field group | allowlist status | consistent? | notes |
|---|---|---|---|
| brand | update yes, insert yes | yes | Core included field across planning set. |
| gender | update yes, insert yes | yes | Core included field across planning set. |
| itemName | update yes, insert yes | yes | Core included field across planning set. |
| categoryName | update yes, insert yes | yes | Core included field across planning set. |
| subCategoryParent | update yes, insert yes | yes | Core included field across planning set. |
| subCategory -> subcategory | update yes, insert yes | yes | Explicit alias mapping consistently noted. |
| price | update yes, insert yes | yes | Numeric validation expectation consistently noted. |
| salePrice | update yes, insert yes | yes | Numeric validation expectation consistently noted. |
| description | update yes, insert yes | yes | Included as content field. |
| featured | update yes, insert yes | yes | Included as flag field. |
| images | update yes, insert yes | yes | Included with parsing/normalization expectations. |
| thumbnails_json | update yes, insert yes | yes | Included with JSON-validity expectation. |
| altText | update yes, insert yes | yes | Included accessibility field. |
| ariaText | update yes, insert yes | yes | Included accessibility field. |
| videoAltText | update yes, insert yes | yes | Included accessibility field. |
| videos | update yes, insert yes | yes | Included media field. |
| external_item_id | update yes, insert yes | yes | Included stable linkage helper field. |
| model_id | deferred/conditional | yes | Consistently blocked by duplicate governance condition. |
| db_itemId | update linkage only; insert deferred backfill | yes | Used as linkage key, not preassigned for inserts. |

## 5. Deferred governance consistency check

| field | status in planning sequence | consistent? | execution implication | notes |
|---|---|---|---|---|
| CropAllowed | deferred | yes | Must stay out of proposed write output in first pass | Governance decision plan keeps it excluded initially. |
| crop_allowed | deferred | yes | Must stay out of proposed write output in first pass | Paired with CropAllowed deferral to avoid semantic drift. |
| model_id uniqueness/enforcement | conditional/deferred | yes | No UNIQUE enforcement unless duplicate is resolved or waiver approved | Duplicate gate required before executable enforcement. |
| db_itemId backfill for new rows | deferred | yes | No automatic preassignment; post-insert policy required first | Explicitly treated as unresolved policy item. |
| ageGroup/age_group | conditional/deferred compatibility | yes | Excluded from first-pass write allowlist | Canonicalization decision intentionally postponed. |
| sizeType/size_type | conditional/deferred compatibility | yes | Excluded from first-pass write allowlist | Canonicalization decision intentionally postponed. |
| fitStyle/fit_style | conditional/deferred compatibility | yes | Excluded from first-pass write allowlist | Canonicalization decision intentionally postponed. |
| activityTags/activity_tags | conditional/deferred compatibility | yes | Excluded from first-pass write allowlist | Canonicalization decision intentionally postponed. |

## 6. Protected field consistency check

| protected field | excluded from update? | excluded from insert? | excluded from proposed write structures? | reported only for awareness? | consistent? |
|---|---|---|---|---|---|
| item.hero_image | yes | yes | yes | yes | yes |
| item.chosen_image | yes | yes | yes | yes | yes |
| hero_override.chosen_image | yes | yes | yes | yes | yes |

Any future plan that allows these fields into CSV-driven write output would conflict with current governance and planning policy.

## 7. Row classification consistency check

| row class | consistent? | blocking behavior | notes |
|---|---|---|---|
| linked_update_candidate | yes | non-blocking unless downstream anomaly/manual-review flags appear | Definition is stable: db_itemId present and matched. |
| new_insert_candidate | yes | non-blocking unless required fields fail | Definition is stable: db_itemId blank and insert assumptions pass. |
| missing_db_itemId_manual_review | yes | blocking until resolved/accepted | Used when blank db_itemId row fails insert readiness. |
| db_itemId_not_found_manual_review | yes | blocking until resolved/accepted | Used when db_itemId is present but unmatched in live data. |
| duplicate_model_id_manual_review | yes | blocking for uniqueness enforcement; conditional execution blocker | Explicitly connected to duplicate governance gate. |
| protected_field_conflict_manual_review | yes | blocking if protected write proposal appears | Ensures policy breach detection. |
| deferred_governance_field_present | yes | informational by itself, but overall execution still blocked by unresolved governance | Used for tracking and auditability, not direct write scope. |

## 8. Field classification consistency check

| field class | consistent? | may appear in future SET clause? | requires manual review? | notes |
|---|---|---|---|---|
| update_allowed | yes | yes, if future execution approved | conditional | Only for linked updates after safety gates pass. |
| insert_allowed | yes | yes, as insert payload fields if approved | conditional | Only for insert previews/execution after safety gates pass. |
| deferred_governance | yes | no | yes | Must remain excluded until explicit approval. |
| staging_only | yes | no | usually no | Reporting/context only. |
| protected_never_overwrite | yes | no | yes if conflict signal appears | Explicit never-overwrite class. |
| unmapped_or_unknown | yes | no | yes | Requires mapping or governance decision. |

## 9. Report output consistency check

| future report artifact | purpose | referenced consistently? | notes |
|---|---|---|---|
| csv-mysql-dry-run-summary.md | high-level run summary, counts, gate outcomes | yes | Repeated in report design, pseudocode outputs, and checklist expectations. |
| csv-mysql-dry-run-field-diff.csv | row-field diff and field-state detail for linked rows | yes | Repeated with aligned intent and key columns. |
| csv-mysql-dry-run-manual-review.csv | consolidated manual-review queue | yes | Repeated with aligned intent and issue tracking. |
| csv-mysql-dry-run-insert-preview.csv | insert-candidate preview and readiness status | yes | Repeated with aligned intent and deferred-field visibility. |

## 10. Safety gate consistency check

| safety gate | present in planning sequence? | consistent? | notes |
|---|---|---|---|
| expected row counts match or differences are explained | yes | yes | Count reconciliation is repeatedly required. |
| no protected fields appear in proposed write output | yes | yes | Explicit gate across report design/spec/checklist. |
| deferred fields are excluded from proposed write output | yes | yes | Explicit gate tied to governance deferrals. |
| model_id duplicate is resolved or uniqueness enforcement remains deferred | yes | yes | Consistent conditional gate. |
| insert candidates have required fields | yes | yes | Required-field readiness appears in all dry-run planning artifacts. |
| db_itemId backfill policy remains deferred unless approved | yes | yes | Repeated unresolved dependency. |
| manual-review rows are listed | yes | yes | Manual-review artifact is consistently planned. |
| dry-run remains read-only | yes | yes | Core non-execution boundary is stable. |
| database backup/export required before any future write task | yes | yes | Explicitly present in migration design/reference and report design gates. |

## 11. Non-goals consistency check

| non-goal | consistently stated? | notes |
|---|---|---|
| no CSV edits | yes | Repeated in planning/governance artifacts. |
| no DB writes | yes | Repeated in planning/governance artifacts. |
| no ALTER TABLE execution | yes | SQL documents are planning-only and non-executable. |
| no executable SQL | yes | Explicit non-execution posture repeated. |
| no importer implementation | yes | Dry-run planning artifacts remain design/spec/checklist only. |
| no importer execution | yes | Execution approval is not granted. |
| no report generation | yes | Report outputs are future artifacts only. |
| no generated report changes | yes | Generated files remain out of scope. |
| no PHP changes | yes | Not approved in this planning phase. |
| no image edits | yes | Not approved in this planning phase. |
| no Hero Manager / Hero Editor changes | yes | Not approved in this planning phase. |
| no schema cleanup | yes | Deferred beyond current planning scope. |
| no duplicate-column canonicalization | yes | Deferred governance item. |
| no db_itemId backfill execution | yes | Policy is deferred and not executable in current phase. |
| no model_id uniqueness enforcement | yes | Deferred until duplicate resolution is complete/approved. |

## 12. Internal inconsistencies or wording drift
No blocking inconsistencies were identified.

Minor wording alignment recommendations:
- Issue: Some documents use "likely new insert candidates" while others imply insert-ready status.
  - Affected documents: allowlist plan, report design, pseudocode spec, checklist.
  - Why it matters: "likely" vs "insert-ready" can be read as different confidence levels.
  - Recommended correction: standardize on "likely insert candidates (insert-ready only after required-field checks)".
  - Blocking before implementation? no.
- Issue: Some documents reference required insert fields as placeholders, while others describe them as defined checks.
  - Affected documents: pseudocode spec, checklist, report design.
  - Why it matters: may cause ambiguity in future implementation task framing.
  - Recommended correction: add a single canonical required-insert-field list in a future doc-only interface/design artifact.
  - Blocking before implementation? no.

## 13. Consolidated current position
Current agreed planning position:
- first executable work is still not approved,
- dry-run implementation is not yet approved,
- current docs support only planning, review, and future read-only dry-run design,
- protected fields remain excluded,
- deferred governance fields remain excluded,
- model_id duplicate remains unresolved unless a later CSV edit or decision record resolves it,
- db_itemId backfill remains deferred,
- generated report files remain out of scope unless a later task explicitly approves report generation.

## 14. Recommended next step
No blocking inconsistencies were found.

Recommended next step is a documentation-only task that defines:
- future dry-run importer file location,
- naming convention,
- command/interface design,
without writing implementation code.

If future review finds a blocking inconsistency, do a targeted documentation correction PR before any implementation planning continues.

Do not implement or run the importer yet.
