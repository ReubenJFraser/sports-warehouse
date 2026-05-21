# 2026-05-21 Dry-Run Importer Implementation Checklist

## 1. Purpose

This document is an implementation checklist for a future CSV-to-MySQL dry-run importer only.

This document is documentation-only. It is not executable code, not a migration, not approval to write to the database, and not approval to run an import.

## 2. Source references

This checklist is based on:

- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Pseudocode-Spec.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`
- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Known migration facts

Record and preserve these known facts for future implementation planning:

- 120 CSV rows.
- 54 existing rows linked by `db_itemId`.
- 66 likely new insert candidates with blank `db_itemId`.
- `db_itemId` is the current live linkage field.
- `model_id` exists in CSV but has one documented duplicate.
- `UNIQUE(model_id)` remains deferred until duplicate resolution is applied.
- `CropAllowed` and `crop_allowed` are deferred from the first-pass import allowlist.
- `item.hero_image`, `item.chosen_image`, and `hero_override.chosen_image` are protected runtime/editor fields.

## 4. Implementation readiness checklist

| Area | Checklist item | Required before implementation? | Blocking if missing? | Notes |
| --- | --- | --- | --- | --- |
| source CSV availability | Confirm `docs/data/SportWarehouse_ProductDB.csv` exists and is readable. | Yes | Yes | Source file must be stable and review-approved. |
| live database read access | Confirm read-only access path for loading live `item` and optional `hero_override` data. | Yes | Yes | Read-only connection is required wherever possible. |
| item table schema awareness | Confirm field names and types needed for linkage, compare, and insert preview logic are documented. | Yes | Yes | Include `db_itemId`, `model_id`, allowlisted fields, and required insert fields. |
| hero_override awareness | Confirm `hero_override.chosen_image` is known as protected and not an import target. | Yes | Yes | Awareness required for safety checks even if optional to load. |
| allowlist definitions | Confirm update and insert allowlists are explicit and versioned in planning docs. | Yes | Yes | No implementation should infer allowlists from CSV headers alone. |
| deferred governance fields | Confirm deferred fields list is complete and synchronized with governance docs. | Yes | Yes | Deferred fields must be excluded from proposed writes. |
| protected field exclusion | Confirm protected fields are excluded before proposed change structures are built. | Yes | Yes | Missing this is a safety gate failure. |
| report output paths | Confirm intended output file paths are defined for future approved tasks only. | Yes | No | This checklist does not generate report files. |
| dry-run safety gates | Confirm all safety gates are defined and testable in dry-run mode. | Yes | Yes | Safety gates must fail closed on violations. |
| manual-review handling | Confirm manual-review classes and blocking rules are defined before implementation. | Yes | Yes | Must distinguish per-row blockers from global blockers. |

## 5. Read-only safety checklist

- [ ] Confirm no `INSERT` statements.
- [ ] Confirm no `UPDATE` statements.
- [ ] Confirm no `DELETE` statements.
- [ ] Confirm no `ALTER TABLE` statements.
- [ ] Confirm no repair SQL.
- [ ] Confirm no migration execution.
- [ ] Confirm no CSV writes.
- [ ] Confirm no generated reports unless explicitly approved in a later task.
- [ ] Confirm database connection is read-only where possible.
- [ ] Confirm the script design requires failure if any write operation is attempted.

## 6. Configuration checklist

Before implementation, define and validate these future configuration pieces:

- `csv_source_path`
- `live_table_name`
- `linkage_field`
- `update_allowlist`
- `insert_allowlist`
- `deferred_governance_fields`
- `staging_only_fields`
- `protected_never_overwrite_fields`
- `required_insert_fields`
- `report_output_paths`

This checklist does not create a configuration file.

## 7. CSV header validation checklist

- [ ] Confirm expected fields are present.
- [ ] Detect and classify unknown fields.
- [ ] Detect missing expected fields.
- [ ] Recognize and classify staging-only fields.
- [ ] Recognize and classify deferred governance fields.
- [ ] Reject protected field names from import candidates.
- [ ] Ensure header validation outcomes are included in the future dry-run summary output.

## 8. Live data loading checklist

- [ ] Confirm `item` rows are loaded with read-only behavior.
- [ ] Confirm `item` rows are keyed by `db_itemId`.
- [ ] Confirm comparison fields are limited to allowlisted fields.
- [ ] Confirm `model_id` values are loaded for duplicate awareness.
- [ ] Confirm protected `item` fields are loaded only for exclusion confirmation.
- [ ] Confirm `hero_override.chosen_image` is loaded only for protected-field awareness.
- [ ] Confirm runtime/editor fields are not treated as CSV import targets.

## 9. Row classification checklist

| Row class | Expected handling | Blocks later execution? |
| --- | --- | --- |
| `linked_update_candidate` | Compare by `db_itemId` using update-allowed fields only; include in diff preview. | No, unless later row-level blockers appear. |
| `new_insert_candidate` | Include in insert preview with required/optional field checks and no preassigned `db_itemId`. | No, unless required fields are missing. |
| `missing_db_itemId_manual_review` | Send to manual review when row cannot be safely classified as insert-ready. | Yes, for that row. |
| `db_itemId_not_found_manual_review` | Send to manual review when non-blank linkage key is absent in live data. | Yes, for that row. |
| `duplicate_model_id_manual_review` | Send duplicate rows to manual review and defer uniqueness enforcement actions. | Yes, if uniqueness enforcement is required for execution scope. |
| `protected_field_conflict_manual_review` | Trigger safety failure when protected field appears in proposed write scope. | Yes, global blocking safety failure. |
| `deferred_governance_field_present` | Record and report separately; exclude from proposed write structures. | No by itself, but execution remains deferred by governance policy. |

## 10. Field classification checklist

| Field class | Checklist intent |
| --- | --- |
| `update_allowed` | Eligible for linked-row comparison proposals only. |
| `insert_allowed` | Eligible for insert preview proposals only. |
| `deferred_governance` | Report separately and exclude from proposed write output. |
| `staging_only` | Report separately and exclude from live write proposals. |
| `protected_never_overwrite` | Reject from import targets; treat as safety gate-protected. |
| `unmapped_or_unknown` | Flag for warning or manual review; do not include in proposed writes. |

Protected fields in `protected_never_overwrite` must be removed before proposed change structures are assembled.

## 11. Linked-row comparison checklist

- [ ] Match rows by exact `db_itemId`.
- [ ] Compare only update-allowed fields.
- [ ] Preserve raw CSV value.
- [ ] Preserve raw runtime value.
- [ ] Record changed/unchanged status per compared field.
- [ ] Record blank-source state.
- [ ] Record blank-runtime state.
- [ ] Exclude deferred governance fields.
- [ ] Exclude protected fields.
- [ ] Flag manual-review conditions.

## 12. Insert-preview checklist

- [ ] Treat blank `db_itemId` rows as likely inserts.
- [ ] Confirm `db_itemId` is not preassigned.
- [ ] Check required insert fields.
- [ ] Report optional insert fields.
- [ ] Use insert-allowed fields only.
- [ ] Report deferred governance fields separately.
- [ ] Report staging-only fields separately.
- [ ] Flag missing required values.

## 13. Duplicate model_id checklist

- [ ] Detect duplicate `model_id` values across CSV rows.
- [ ] Report known duplicate `nike_female_leggings` x 2.
- [ ] Send duplicate rows to manual review.
- [ ] Confirm `UNIQUE(model_id)` is not enforced in dry-run.
- [ ] Confirm `model_id` write/enforcement remains deferred unless duplicate is resolved or enforcement remains explicitly deferred.

## 14. Protected field checklist

Protected fields:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

Future dry-run safety gates must fail if any protected field appears in proposed update or insert output.

## 15. Deferred governance checklist

| Deferred governance item | What must be checked before any future execution |
| --- | --- |
| `CropAllowed` | Verify governance approval status and confirm treatment is explicitly approved for execution scope. |
| `crop_allowed` | Verify governance approval status and confirm treatment is explicitly approved for execution scope. |
| `model_id` uniqueness/enforcement | Verify duplicate resolution is complete or uniqueness enforcement remains explicitly deferred. |
| `db_itemId` backfill for new rows | Verify backfill policy approval exists before any execution path that would assign new linkage IDs. |
| `ageGroup` / `age_group` | Verify naming/canonicalization policy is approved before execution scope includes these fields. |
| `sizeType` / `size_type` | Verify naming/canonicalization policy is approved before execution scope includes these fields. |
| `fitStyle` / `fit_style` | Verify naming/canonicalization policy is approved before execution scope includes these fields. |
| `activityTags` / `activity_tags` | Verify naming/canonicalization policy is approved before execution scope includes these fields. |

## 16. Report output checklist

Design checklist for future outputs only. Do not generate reports in this task.

- `csv-mysql-dry-run-summary.md`
  - [ ] Include scope statement, read-only confirmation, and safety gate results.
  - [ ] Include header validation outcomes and row/field classification totals.
  - [ ] Include manual-review summary and deferred governance summary.
- `csv-mysql-dry-run-field-diff.csv`
  - [ ] Include row identifiers (`db_itemId`, `model_id` where available), field name, raw CSV value, raw runtime value, and change state.
  - [ ] Include explicit exclusion confirmation for protected and deferred fields.
- `csv-mysql-dry-run-manual-review.csv`
  - [ ] Include row class, blocking status, reason code, and reviewer notes placeholder.
  - [ ] Include duplicate, missing linkage, unknown field, and protected-field conflict flags.
- `csv-mysql-dry-run-insert-preview.csv`
  - [ ] Include likely insert rows with required field readiness, optional fields, and missing-required indicators.
  - [ ] Exclude protected and deferred fields from proposed write columns.

## 17. Summary metrics checklist

- [ ] total CSV rows
- [ ] linked update candidates
- [ ] likely insert candidates
- [ ] rows matched by `db_itemId`
- [ ] `db_itemId` values not found in live `item` table
- [ ] duplicate `model_id` rows
- [ ] changed field count
- [ ] unchanged field count
- [ ] blank source value count
- [ ] blank runtime value count
- [ ] deferred field count
- [ ] protected field exclusion confirmation
- [ ] manual review row count

## 18. Safety gate checklist

- [ ] Expected row counts match known totals, or differences are explained.
- [ ] No protected fields appear in proposed write output.
- [ ] Deferred fields are excluded from proposed write output.
- [ ] `model_id` duplicate is resolved or enforcement remains deferred.
- [ ] Insert candidates have required fields.
- [ ] `db_itemId` backfill policy remains deferred unless explicitly approved.
- [ ] Manual-review rows are listed.
- [ ] Dry-run remains read-only.

## 19. Failure and warning checklist

Examples for future handling behavior:

- Blocking failures:
  - protected field in proposed write output = blocking failure.
  - attempted write operation in dry-run mode = blocking failure.
- Non-blocking warnings:
  - unknown CSV field = warning or manual review.
  - missing non-required expected field = warning pending review.
- Manual-review flags:
  - duplicate `model_id` = manual review and execution blocker if uniqueness enforcement is required.
  - `db_itemId` not found in live data = manual review blocker for that row.
- Summary messages:
  - include pass/fail state for safety gates.
  - include explicit count of blocked rows and warning rows.

## 20. Non-goals

This checklist does not perform any of the following:

- no CSV edits
- no DB writes
- no `ALTER TABLE` execution
- no executable SQL
- no importer implementation
- no report generation
- no generated report changes
- no PHP changes
- no image edits
- no Hero Manager or Hero Editor changes
- no schema cleanup
- no duplicate-column canonicalization
- no `db_itemId` backfill execution
- no `model_id` uniqueness enforcement

## 21. Recommended next step

After this checklist, the next task should be a review/consolidation pass that verifies the allowlist plan, report design, pseudo-code spec, and this implementation checklist are internally consistent before any implementation task is created.

Do not implement or run the importer yet.
