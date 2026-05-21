# 2026-05-21 No-Execution Dry-Run Importer/Report Design

## 1. Purpose

This document defines a documentation-only design for a future CSV-to-MySQL dry-run importer/report workflow.

It is intentionally non-executable and planning-focused. It does not implement or run an importer, does not alter any database data or schema, and does not produce executable SQL.

## 2. Source references

The dry-run importer/report design is based on the following source materials:

- `docs/data/SportWarehouse_ProductDB.csv`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

## 3. Known migration facts

The design assumes and records these known facts from prior planning:

- Total CSV rows: 120.
- Existing rows linked by `db_itemId`: 54.
- Likely new insert candidates with blank `db_itemId`: 66.
- `db_itemId` is the current live linkage field.
- `model_id` exists in CSV but has one documented duplicate.
- `UNIQUE(model_id)` remains deferred until duplicate resolution is applied.
- `CropAllowed` and `crop_allowed` are deferred from the first-pass import allowlist.
- `item.hero_image`, `item.chosen_image`, and `hero_override.chosen_image` are protected runtime/editor fields.

## 4. Dry-run design principles

The future dry-run importer/report must follow these principles:

- The dry-run must be read-only.
- The dry-run must be allowlist-based.
- The dry-run must compare CSV source values against live runtime values.
- The dry-run must classify rows before classifying field changes.
- The dry-run must never include protected fields in proposed write output.
- The dry-run must separate update candidates, insert candidates, deferred fields, staging-only fields, and manual-review rows.
- The dry-run output must be reviewable before any executable migration/import work.

## 5. Input assumptions

| Input | Role in dry-run design |
| --- | --- |
| CSV source file (`docs/data/SportWarehouse_ProductDB.csv`) | Primary source dataset for row discovery, field discovery, update comparison candidates, and insert preview candidates. |
| Live `item` table | Runtime comparison target for linked rows keyed by `db_itemId`, and source of current live values for diff reporting. |
| `hero_override` table (read-only) | Protected-field awareness source for `hero_override.chosen_image`; used only to ensure exclusion from write proposals. |
| First-pass allowlist plan | Defines update-allowed fields, insert-allowed fields, deferred governance fields, and staging-only fields for classification. |
| Governance decision documents | Define policy constraints, deferred decisions, and preconditions that govern row handling, field handling, and execution gates. |

## 6. Row classification design

| Row class | Detection rule | Expected count (if known) | Dry-run action | Blocks execution |
| --- | --- | --- | --- | --- |
| `linked_update_candidate` | CSV row has non-blank `db_itemId` and matching live `item` row exists. | 54 | Compare only update-allowed fields, then record field-level outcomes. | No, unless downstream manual-review flags appear. |
| `new_insert_candidate` | CSV row has blank `db_itemId` and minimum insert identity fields are present. | 66 likely | Prepare insert preview using insert-allowed fields only; do not assign `db_itemId`. | No, unless required-field/manual-review conditions fail. |
| `missing_db_itemId_manual_review` | `db_itemId` is blank but row does not satisfy insert readiness assumptions (for example missing required insert fields). | Unknown subset of 66 | Route to manual review with missing data reasons. | Yes, until resolved or explicitly accepted. |
| `db_itemId_not_found_manual_review` | CSV row has non-blank `db_itemId` but no live `item` row matches. | Unknown | Route to manual review; do not auto-convert to insert. | Yes, until resolved or explicitly accepted. |
| `duplicate_model_id_manual_review` | Row has `model_id` that participates in known duplicate set. | 1 duplicate condition documented | Route to manual review and duplicate-resolution workflow. | Yes for uniqueness enforcement; conditional block for execution scope. |
| `protected_field_conflict_manual_review` | Row contains values in protected fields that differ from live protected values or appear as attempted write candidates. | Unknown | Exclude protected fields from write proposals and add explicit conflict/review note. | Yes if any protected write proposal appears. |
| `deferred_governance_field_present` | Row contains values for governance-deferred fields (for example `CropAllowed` or `crop_allowed`). | Unknown | Track in deferred section; do not include in write proposal. | Not by itself, but execution remains blocked until governance conditions are met. |

## 7. Field classification design

| Field class | Source | Report behavior | May appear in future `SET` clause | Requires manual review |
| --- | --- | --- | --- | --- |
| `update_allowed` | Allowlist plan | Include in linked-row diff as changed/unchanged/blank-state outcomes. | Yes, for linked updates after gates pass. | Only when anomalies are detected. |
| `insert_allowed` | Allowlist plan | Include in insert preview with required/optional presence checks. | Yes, for inserts after gates pass. | Yes when required fields are missing or invalid. |
| `deferred_governance` | Governance decisions plus allowlist exclusions | Report separately in deferred section; no coercion and no write proposal. | No, until explicit governance approval. | Yes when needed for launch scope decisions. |
| `staging_only` | Allowlist/governance notes | Report separately as staging context only. | No, not for first-pass runtime writes. | Usually no, unless mapping ambiguity exists. |
| `protected_never_overwrite` | Runtime/editor protection policy | Exclude from diff/write proposal and confirm exclusion in summary. | No, never in this migration path. | Yes if conflict signals policy breach risk. |
| `unmapped_or_unknown` | CSV/schema comparison | Report as unknown/unmapped with row references and counts. | No, until mapping is approved. | Yes, mapping decision required. |

## 8. Comparison rules for linked rows

For the 54 linked rows, the future dry-run should follow this sequence:

1. Match each CSV row to a live `item` row by exact `db_itemId`.
2. Compare only fields classified as `update_allowed`.
3. Apply normalization only where safe for reporting clarity (for example trimming surrounding whitespace for a normalized-comparison view).
4. Preserve and emit raw CSV values and raw runtime values in report output alongside any normalized comparison flag.
5. Do not silently coerce deferred governance fields.
6. Do not compare or propose changes for protected fields.
7. Assign field-level states at minimum: `changed`, `unchanged`, `blank_source`, `blank_runtime`, and `manual_review`.
8. Roll field-level outcomes up to row-level review status so reviewers can approve or block future execution.

## 9. Insert preview rules for new rows

For the 66 likely insert candidates (blank `db_itemId`), the future dry-run should:

1. Identify blank `db_itemId` rows as likely inserts.
2. Never preassign or infer `db_itemId`.
3. Build insert previews from `insert_allowed` fields only.
4. Report required fields as present/missing per row.
5. Report optional fields as present/missing per row.
6. Report deferred fields in a dedicated deferred section per row.
7. Report staging-only fields in a dedicated staging section per row.
8. Flag any row that requires manual review before it can enter executable insert scope.

## 10. Protected field exclusion rules

The following protected fields must never appear in proposed update or insert write plans:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

The dry-run report must explicitly confirm that protected fields were excluded from proposed write output.

## 11. Deferred governance handling

| Deferred or conditional area | Why deferred or conditionally allowed | Dry-run reporting behavior | Decision needed before execution |
| --- | --- | --- | --- |
| `CropAllowed` | Governance decision deferred for first-pass importer scope. | List as deferred field presence by row; do not include in write proposals. | Approve inclusion policy or keep excluded for execution phase. |
| `crop_allowed` | Same governance deferral as `CropAllowed`, including naming overlap risk. | Track presence and potential conflicts with `CropAllowed`; no write proposal. | Canonical handling decision and inclusion approval required. |
| `model_id` uniqueness/enforcement | One documented duplicate exists; uniqueness enforcement not safe yet. | Flag duplicate-participating rows and summarize duplicate set. | Resolve duplicate or explicitly defer `UNIQUE(model_id)` enforcement. |
| `db_itemId` backfill for new rows | Blank on likely insert candidates; assignment policy unresolved. | Report blank linkage and candidate rows without assigning values. | Approve authoritative backfill/assignment workflow. |
| `ageGroup`/`age_group` | Potential duplicate-column/canonicalization governance issue. | Report both as conditional/deferred mapping until canonical target is approved. | Choose canonical field mapping and execution behavior. |
| `sizeType`/`size_type` | Potential duplicate-column/canonicalization governance issue. | Report both as conditional/deferred mapping until canonical target is approved. | Choose canonical field mapping and execution behavior. |
| `fitStyle`/`fit_style` | Potential duplicate-column/canonicalization governance issue. | Report both as conditional/deferred mapping until canonical target is approved. | Choose canonical field mapping and execution behavior. |
| `activityTags`/`activity_tags` | Potential duplicate-column/canonicalization governance issue. | Report both as conditional/deferred mapping until canonical target is approved. | Choose canonical field mapping and execution behavior. |

## 12. Proposed dry-run report outputs

This section defines future report artifacts only. It does not generate files.

### A. `docs/operations/generated/csv-mysql-dry-run-summary.md`

Suggested sections:

- Run metadata (timestamp, CSV path, live environment label, document version reference).
- High-level row classification counts.
- High-level field-state counts.
- Deferred-field overview.
- Protected-field exclusion confirmation.
- Manual-review queue summary.
- Safety-gate checklist status (pass/fail/pending, documentation only).

### B. `docs/operations/generated/csv-mysql-dry-run-field-diff.csv`

Suggested columns:

- `csv_row_number`
- `db_itemId`
- `row_class`
- `field_name`
- `field_class`
- `source_raw_value`
- `runtime_raw_value`
- `source_normalized_value` (if computed)
- `runtime_normalized_value` (if computed)
- `diff_state` (`changed`, `unchanged`, `blank_source`, `blank_runtime`, `manual_review`)
- `deferred_flag`
- `protected_flag`
- `manual_review_reason`

### C. `docs/operations/generated/csv-mysql-dry-run-manual-review.csv`

Suggested columns:

- `csv_row_number`
- `db_itemId`
- `model_id`
- `row_class`
- `manual_review_reason`
- `blocking_flag`
- `related_field_names`
- `governance_reference`
- `reviewer_decision` (placeholder)
- `review_notes` (placeholder)

### D. `docs/operations/generated/csv-mysql-dry-run-insert-preview.csv`

Suggested columns:

- `csv_row_number`
- `db_itemId` (expected blank)
- `row_class`
- `required_fields_present`
- `required_fields_missing`
- `optional_fields_present_count`
- `optional_fields_missing_count`
- `deferred_fields_present`
- `staging_only_fields_present`
- `insert_readiness_status`
- `manual_review_reason`

## 13. Required summary metrics

The dry-run summary should include at minimum:

- total CSV rows
- linked update candidates
- likely insert candidates
- rows matched by `db_itemId`
- `db_itemId` values not found in live `item` table
- duplicate `model_id` rows
- changed field count
- unchanged field count
- blank source value count
- blank runtime value count
- deferred field count
- protected field exclusion confirmation
- manual review row count

## 14. Safety gates before execution

Before any future executable importer task or SQL migration task is considered, all gates below must pass or be formally accepted with documented rationale:

1. Dry-run row counts match expectations, or differences are explained and approved.
2. No protected fields appear in proposed write output.
3. Deferred governance fields are excluded from executable write scope.
4. The documented `model_id` duplicate is resolved, or uniqueness enforcement remains explicitly deferred.
5. Insert-candidate rows satisfy required-field completeness rules.
6. `db_itemId` backfill policy for new rows is approved.
7. Manual-review rows are resolved, or explicit risk acceptance is documented.
8. A database backup/export is completed before any future write task.

## 15. Non-goals

This document intentionally does not perform any of the following:

- no CSV edits
- no DB writes
- no `ALTER TABLE` execution
- no executable SQL
- no importer implementation
- no report generation
- no generated report changes
- no PHP changes
- no image edits
- no Hero Manager / Hero Editor changes
- no schema cleanup
- no duplicate-column canonicalization

## 16. Recommended next step

After this design, the recommended next task is either:

- draft a no-execution pseudo-code/specification for the future dry-run importer/report, or
- conduct a focused review of the allowlist and governance documents to finalize deferred and conditional decisions.

Do not proceed to execute an import yet.
