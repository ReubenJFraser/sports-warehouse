# 2026-05-21 No-Execution Dry-Run Importer Pseudocode Spec

## 1. Purpose

This document provides implementation-shaped pseudo-code and specification guidance for a future CSV-to-MySQL dry-run importer.

This document is documentation-only. It is not executable code, and it must not be treated as approval to run an import.

## 2. Source references

This pseudo-code/specification references:

- `docs/data/SportWarehouse_ProductDB.csv`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

## 3. Known migration facts

Record these known migration facts for dry-run planning:

- 120 CSV rows.
- 54 existing rows linked by `db_itemId`.
- 66 likely new insert candidates with blank `db_itemId`.
- `db_itemId` is the current live linkage field.
- `model_id` exists in CSV but has one documented duplicate.
- `UNIQUE(model_id)` remains deferred until duplicate resolution is applied.
- `CropAllowed` and `crop_allowed` are deferred from the first-pass import allowlist.
- `item.hero_image`, `item.chosen_image`, and `hero_override.chosen_image` are protected runtime/editor fields.

## 4. Pseudo-code principles

Apply these principles to all future dry-run implementation work:

- read-only only
- no `INSERT`, `UPDATE`, `DELETE`, `ALTER`, or repair SQL
- allowlist-based comparisons only
- protected fields must be excluded before comparison/write-plan construction
- deferred governance fields must be reported separately
- raw CSV and raw runtime values must be preserved in report data
- normalization may be used for comparison notes only, not silent conversion
- report output is for review, not execution

## 5. High-level dry-run flow

Descriptive pseudo-code flow (non-executable):

1. Load configuration model.
2. Load allowlist definitions and deferred/staging/protected field sets.
3. Load CSV rows from source path.
4. Load live `item` rows with read-only query behavior.
5. Optionally load `hero_override` metadata for protected-field awareness only.
6. Validate CSV headers against expected and controlled field sets.
7. Classify each row into row classes.
8. Classify each field into field classes.
9. Compare linked rows (`db_itemId` present and matched).
10. Preview likely insert candidates (`db_itemId` blank).
11. Collect manual review items from row-level and field-level checks.
12. Verify protected field exclusion from all proposed write structures.
13. Compute summary metrics.
14. Prepare in-memory report data structures.
15. Stop without writing files unless a future report-generation task is explicitly approved.

## 6. Configuration model

Design-only pseudo-configuration model (not a real config file):

```text
config_model:
  csv_source_path: "docs/data/SportWarehouse_ProductDB.csv"
  live_table_name: "item"
  linkage_field: "db_itemId"

  update_allowlist:
    - <field_name_1>
    - <field_name_2>

  insert_allowlist:
    - <field_name_1>
    - <field_name_2>

  deferred_governance_fields:
    - CropAllowed
    - crop_allowed
    - model_id_uniqueness_enforcement
    - db_itemId_backfill
    - ageGroup
    - age_group
    - sizeType
    - size_type
    - fitStyle
    - fit_style
    - activityTags
    - activity_tags

  staging_only_fields:
    - <field_name_a>

  protected_never_overwrite_fields:
    - item.hero_image
    - item.chosen_image
    - hero_override.chosen_image

  required_insert_fields:
    - <required_field_1>
    - <required_field_2>

  report_output_paths:
    summary_report: <future_path>
    field_diff_rows: <future_path>
    manual_review_rows: <future_path>
    insert_preview_rows: <future_path>
```

## 7. Header validation pseudo-code

Descriptive pseudo-code:

1. Build `expected_header_fields` from:
   - update allowlist
   - insert allowlist
   - deferred governance fields
   - staging-only fields
   - linkage field and known identity fields
2. Read CSV header row as `csv_header_fields`.
3. Compute `unknown_fields = csv_header_fields - expected_header_fields`.
4. Compute `missing_expected_fields = expected_header_fields - csv_header_fields`.
5. If any protected field name appears as an import candidate, mark protected conflict.
6. Classify outcomes:
   - blocking failure when protected field is attempted in import candidate scope
   - warning/manual review for unknown fields
   - warning/manual review for missing expected fields (or blocking if required for row classification)
7. Persist header validation outcomes into manual review and summary structures.

## 8. Live data loading pseudo-code

Descriptive pseudo-code for read-only live loading:

1. Read live `item` rows keyed by `db_itemId`.
2. Load only item fields required for comparison and row classification.
3. Load live `model_id` values for duplicate-awareness checks.
4. Load protected item fields (`item.hero_image`, `item.chosen_image`) only for exclusion confirmation.
5. Optionally load `hero_override.chosen_image` only for protected-field awareness.
6. Do not write, mutate, or repair any live data.

## 9. Row classification pseudo-code

Classify each CSV row with rules and status:

- `linked_update_candidate`
  - Rule: `db_itemId` is non-blank and exists in live item map.
  - Status: non-blocking unless field-level/manual-review blockers arise.

- `new_insert_candidate`
  - Rule: `db_itemId` is blank and minimum insert identity assumptions pass.
  - Status: non-blocking unless required insert fields fail.

- `missing_db_itemId_manual_review`
  - Rule: `db_itemId` blank and row fails insert readiness assumptions.
  - Status: blocking for that row.

- `db_itemId_not_found_manual_review`
  - Rule: `db_itemId` non-blank but not found in live item map.
  - Status: blocking for that row.

- `duplicate_model_id_manual_review`
  - Rule: row `model_id` appears in duplicate set.
  - Status: manual-review blocker for uniqueness enforcement scope.

- `protected_field_conflict_manual_review`
  - Rule: protected field appears as attempted write candidate or proposed change target.
  - Status: blocking safety failure.

- `deferred_governance_field_present`
  - Rule: row contains deferred-governance field value.
  - Status: non-blocking by itself, but excluded from proposed write output.

## 10. Field classification pseudo-code

For each CSV field name, assign one class:

1. If field in update allowlist: `update_allowed`.
2. Else if field in insert allowlist: `insert_allowed`.
3. Else if field in deferred governance list: `deferred_governance`.
4. Else if field in staging-only list: `staging_only`.
5. Else if field in protected never-overwrite list: `protected_never_overwrite`.
6. Else: `unmapped_or_unknown`.

Rule: all `protected_never_overwrite` fields must be removed before any proposed change structure is assembled.

## 11. Linked-row comparison pseudo-code

Pseudo-code for the 54 linked rows:

1. For each `linked_update_candidate`, map CSV row to live row by exact `db_itemId`.
2. Iterate only through `update_allowed` fields.
3. For each field:
   - capture `raw_csv_value`
   - capture `raw_runtime_value`
   - optionally compute normalized values for comparison-note visibility only
4. Assign field state:
   - `changed`
   - `unchanged`
   - `blank_source`
   - `blank_runtime`
   - `manual_review`
5. Preserve `raw_csv_value` and `raw_runtime_value` in diff structures.
6. Exclude deferred governance fields from proposed changes.
7. Exclude protected fields from proposed changes.
8. Accumulate row-level review flags from field-level outcomes.

## 12. Insert-preview pseudo-code

Pseudo-code for the 66 likely new rows:

1. Identify rows where `db_itemId` is blank.
2. Validate required insert fields.
3. Build insert preview from `insert_allowed` fields only.
4. Exclude `db_itemId` from preassignment.
5. Log `db_itemId` backfill as deferred governance action.
6. Report deferred governance fields separately.
7. Report staging-only fields separately.
8. Flag row if required values are missing.

## 13. Duplicate model_id handling pseudo-code

Pseudo-code behavior:

1. Build frequency map of CSV `model_id` values.
2. Detect duplicates where count greater than 1.
3. Confirm known duplicate set includes `nike_female_leggings` x 2.
4. Mark duplicate rows as `duplicate_model_id_manual_review`.
5. Do not enforce `UNIQUE(model_id)` in dry-run.
6. Do not propose `model_id` write/enforcement when duplicate remains unresolved, unless uniqueness enforcement is explicitly deferred in scope notes.

## 14. Protected field exclusion pseudo-code

Protected fields that must be excluded from any proposed write structure:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

Safety assertion pseudo-code:

```text
for each proposed_write_record in proposed_update_output + proposed_insert_output:
  if proposed_write_record.field_name in protected_never_overwrite_fields:
    fail_safety_gate("protected field present in proposed write output")
```

This remains pseudo-code only.

## 15. Deferred governance pseudo-code

Separate inspection/report-only handling for:

- `CropAllowed`
- `crop_allowed`
- `model_id` uniqueness/enforcement
- `db_itemId` backfill for new rows
- `ageGroup` and `age_group`
- `sizeType` and `size_type`
- `fitStyle` and `fit_style`
- `activityTags` and `activity_tags`

Pseudo-code rule: these may be inspected and reported but must not be silently transformed or written.

## 16. Report data structure specification

Define in-memory future report structures (do not generate files now):

- `summary_report`
  - keys: `run_date`, `csv_path`, `total_rows`, `row_class_counts`, `field_state_counts`, `safety_gate_status`, `manual_review_count`

- `field_diff_rows`
  - keys: `csv_row_number`, `db_itemId`, `row_class`, `field_name`, `field_class`, `raw_csv_value`, `raw_runtime_value`, `normalized_csv_value_optional`, `normalized_runtime_value_optional`, `diff_state`, `manual_review_reason_optional`

- `manual_review_rows`
  - keys: `csv_row_number`, `db_itemId`, `model_id`, `row_class`, `issue_type`, `blocking_flag`, `reason`, `governance_reference`

- `insert_preview_rows`
  - keys: `csv_row_number`, `db_itemId_expected_blank`, `required_fields_present`, `required_fields_missing`, `insert_allowed_payload_preview`, `deferred_fields_present`, `staging_only_fields_present`, `insert_readiness_status`

- `protected_exclusion_log`
  - keys: `field_name`, `scope`, `detected_in_source`, `excluded_from_proposed_output`, `assertion_status`

- `deferred_governance_log`
  - keys: `field_name`, `row_reference`, `raw_value`, `deferred_reason`, `write_excluded_flag`, `manual_review_flag`

## 17. Summary metrics pseudo-code

Compute and report at minimum:

- total CSV rows
- linked update candidates
- likely insert candidates
- rows matched by `db_itemId`
- `db_itemId` values not found in live item table
- duplicate `model_id` rows
- changed field count
- unchanged field count
- blank source value count
- blank runtime value count
- deferred field count
- protected field exclusion confirmation
- manual review row count

Pseudo-code note: counts are derived from final classified row and field collections.

## 18. Safety gate pseudo-code

Pseudo-code checks before any future execution scope is considered:

1. Expected row counts match known assumptions or differences are explained.
2. No protected fields appear in proposed write output.
3. Deferred fields are excluded from proposed write output.
4. `model_id` duplicate is resolved or uniqueness enforcement remains deferred.
5. Insert candidates satisfy required field presence.
6. `db_itemId` backfill policy remains deferred unless explicitly approved.
7. Manual-review rows are listed and visible.
8. Dry-run remains read-only.

If any blocking check fails, mark dry-run safety gate as failed.

## 19. Failure and warning behavior

Define behavior categories:

- Blocking failures
  - protected field appears in proposed write output
  - read-only boundary violated
  - required insert field missing for a row marked insert-ready

- Non-blocking warnings
  - unknown CSV field detected
  - missing non-critical expected field
  - row-count drift that is explainable but not yet approved

- Manual-review flags
  - duplicate `model_id`
  - `db_itemId` not found in live table
  - unresolved deferred-governance or mapping ambiguity

Expected summary messaging:

- report blocking failures first with explicit blocker reasons
- report warnings separately from blockers
- report manual-review queue with row references and reason codes

## 20. Non-goals

This document explicitly excludes:

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
- no `db_itemId` backfill execution
- no `model_id` uniqueness enforcement

## 21. Recommended next step

After this pseudo-code/specification, the next task should be one of:

1. Review this pseudo-code/specification against the allowlist and governance documents.
2. Create a documentation-only implementation checklist for the eventual dry-run importer.

Do not implement or run the importer yet.
