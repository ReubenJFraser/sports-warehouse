# 2026-05-21 Dry-Run Importer File Location and Command Design

## 1. Purpose

This document defines the future dry-run importer file location, naming convention, and command and interface design only.

This is a documentation-only design record. It does not implement an importer, does not authorize importer execution, and does not approve database writes.

## 2. Source references

This design is based on the following completed planning and governance documents:

- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Planning-Consistency-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Pseudocode-Spec.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Implementation-Checklist.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

Source CSV reference:

- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Known migration facts

The following migration facts are treated as current planning baseline facts:

- 120 CSV rows.
- 54 existing rows linked by `db_itemId`.
- 66 likely new insert candidates with blank `db_itemId`.
- `db_itemId` is the current live linkage field.
- `model_id` exists in CSV but has one documented duplicate.
- `UNIQUE(model_id)` remains deferred until duplicate resolution is applied.
- `CropAllowed` and `crop_allowed` are deferred from first-pass import scope.
- `item.hero_image`, `item.chosen_image`, and `hero_override.chosen_image` are protected runtime and editor fields.
- No executable importer or report generation has yet been approved.

## 4. Placement principles

The future dry-run importer should follow these placement principles:

- It should not live in public web runtime paths.
- It should not be callable from public routes.
- It should be clearly separated from admin UI files.
- It should be placed under a project operations or tools area.
- It should have an explicitly dry-run oriented name.
- It should make write behavior impossible unless a future, separately approved task changes scope.
- It should output only review artifacts when report generation is later approved.

## 5. Recommended future file location

Recommended future location:

- `tools/migration/csv_mysql_dry_run_importer.php`

Why this is preferable:

- It keeps migration operations code out of web runtime paths.
- It cleanly separates operational tooling from admin UI and public route concerns.
- It aligns file intent with name intent by embedding `dry_run_importer` in the script name.
- It supports a least-privilege and fail-closed execution model for future command-line use.

This document does not create the file.

## 6. Alternative locations considered

| Candidate location | Recommendation | Reason |
| --- | --- | --- |
| `tools/migration/csv_mysql_dry_run_importer.php` | Recommended | Best separation from runtime UI, clear operations scope, explicit dry-run intent. |
| `scripts/csv_mysql_dry_run_importer.php` | Acceptable fallback | Still internal and non-public, but less specific than a dedicated migration tools path. |
| `admin/csv_mysql_dry_run_importer.php` | Not recommended | Admin path coupling risks accidental UI or route exposure and blurs operations versus UI boundaries. |
| Public web root location | Disallowed | Publicly reachable runtime paths are inappropriate for migration review tooling and increase risk surface. |
| `README/V-AUDIT` or `docs` location | Documentation only | Suitable for design records but not for executable tooling placement. |

Admin and public locations are not preferred because a read-only operations tool should remain isolated from routable runtime surfaces and UI code paths.

## 7. Naming convention

Future migration and dry-run tooling naming convention should include:

- Prefix and scope naming: use a migration scope path such as `tools/migration/`.
- Source target naming: include source and target systems in filename, for example `csv_mysql`.
- Dry-run marker: include explicit `dry_run` token.
- Importer and report distinction: scripts should use `_importer`, generated outputs should use report-oriented suffixes.
- Date-stamped naming: use date-stamps for generated reports only when needed by workflow; do not date-stamp the script filename by default.

Recommended future script name:

- `csv_mysql_dry_run_importer.php`

Recommended future report names:

- `csv-mysql-dry-run-summary.md`
- `csv-mysql-dry-run-field-diff.csv`
- `csv-mysql-dry-run-manual-review.csv`
- `csv-mysql-dry-run-insert-preview.csv`

## 8. Command and interface design principles

Future command and interface design should enforce:

- Default mode must be read-only dry-run.
- No write mode should exist in first implementation.
- No destructive flags should exist.
- Command should fail closed if required inputs are missing.
- Protected fields must be excluded before any proposed output is assembled.
- Generated report writing should require an explicit future-approved flag or mode.
- Command output should be clear enough to support review before any implementation escalation.

## 9. Proposed future command shape

The following examples are illustrative design examples only. They are not currently executable in this task.

- `php tools/migration/csv_mysql_dry_run_importer.php --dry-run`
- `php tools/migration/csv_mysql_dry_run_importer.php --dry-run --summary-only`
- `php tools/migration/csv_mysql_dry_run_importer.php --dry-run --write-reports`

`--write-reports` must remain unavailable until a later report-generation task explicitly approves it.

No command that writes to the database is in scope.

## 10. Proposed future options

Any option implying database writes is out of scope.

| Option | Purpose | Required? | Default | Safety notes |
| --- | --- | --- | --- | --- |
| `--dry-run` | Explicitly enable dry-run review mode. | Yes | On | Command should fail if absent unless safe default enforces dry-run anyway. |
| `--csv` | Override CSV source path for review input. | No | `docs/data/SportWarehouse_ProductDB.csv` | Must validate file existence and readability before processing. |
| `--env` | Select non-write execution environment profile. | No | `local` | Must never grant write capability in first implementation. |
| `--summary-only` | Print summary counts without report files. | No | Off | Must still run safety gates and protected field exclusions. |
| `--fail-on-warning` | Elevate warnings to non-zero exit behavior. | No | Off | Useful for CI style validation of governance conditions. |
| `--write-reports` | Future gated mode for writing review artifacts only. | No | Off | Must be disabled until separately approved report-generation task. |
| `--output-dir` | Future override for report artifact destination. | No | `docs/operations/generated` | Must reject public or unsafe destinations. |
| `--limit` | Limit processed rows for diagnostics. | No | None | Must not alter safety gates or hide blocking failures. |
| `--row` | Inspect a specific row for manual review. | No | None | Must remain read-only and safety-gated. |
| `--help` | Show usage and safety policy summary. | No | Off | Must include explicit no-write guarantees and disallowed flags. |

## 11. Disallowed options

The following options must not exist in the first implementation:

| Option | Why disallowed |
| --- | --- |
| `--execute` | Implies execution of changes and violates documentation-first dry-run scope. |
| `--apply` | Suggests applying writes to live data, which is out of scope. |
| `--write-db` | Explicit database write behavior is prohibited. |
| `--update` | Update execution semantics are not approved for first implementation. |
| `--insert` | Insert execution semantics are not approved for first implementation. |
| `--alter` | Schema mutation is out of scope and conflicts with no-execution constraints. |
| `--repair` | Data repair implies write behavior and unreviewed transformation. |
| `--backfill-db-item-id` | `db_itemId` backfill is explicitly deferred and must remain manual and reviewed. |
| `--enforce-model-id-unique` | Uniqueness enforcement is deferred pending duplicate resolution plan completion. |

## 12. Configuration and interface boundary

Future implementation may use one of the following interface patterns:

- Command-line options.
- A design-only configuration array or model.
- A separate configuration file only if later approved.

Configuration inputs must include:

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

This task does not create configuration code or files.

## 13. Output behavior design

Future console output should print at minimum:

- Source CSV path.
- Live table target.
- Row count summary.
- Linked update candidate count.
- Likely insert candidate count.
- Manual review count.
- Protected field exclusion status.
- Deferred governance status.
- Safety gate result.

No generated files should be written unless a later task explicitly approves report generation.

## 14. Report output path design

Preserved future report output path targets:

- `docs/operations/generated/csv-mysql-dry-run-summary.md`
- `docs/operations/generated/csv-mysql-dry-run-field-diff.csv`
- `docs/operations/generated/csv-mysql-dry-run-manual-review.csv`
- `docs/operations/generated/csv-mysql-dry-run-insert-preview.csv`

These paths are future design targets only. This task does not create or modify generated reports.

## 15. Safety defaults

Required safety defaults for future implementation:

- Read-only database access where possible.
- No database writes.
- No CSV writes.
- No generated report writes by default.
- No protected fields in proposed output.
- Deferred governance fields excluded from proposed output.
- Duplicate `model_id` remains manual review.
- `db_itemId` backfill remains deferred.
- Command fails if protected write output is detected.
- Command fails if execution or write flags are attempted.

## 16. Manual review and failure behavior

Future command and interface behavior should represent:

- Blocking failures: clear non-zero status with cause and row references where applicable.
- Warnings: visible warning block and warning count summary.
- Manual-review rows: explicit count and row identifiers for follow-up.
- Safety-gate failure: immediate failure status with gate name and reason.
- Safety-gate pass-with-warnings: success state with warnings and manual review notes.
- Dry-run summary success: explicit completion message for read-only review stage.

A dry-run pass does not approve or authorize an actual import.

## 17. Relationship to future implementation

This document is a file location and command interface design only.

Before implementation, a future PR should still:

- Create the dry-run tool skeleton in read-only mode.
- Avoid report generation unless separately approved.
- Include clear tests and checks.
- Preserve all safety gates.
- Not implement database writes.

## 18. Non-goals

This task explicitly excludes:

- No CSV edits.
- No database writes.
- No `ALTER TABLE` execution.
- No executable SQL.
- No importer implementation.
- No report generation.
- No generated report changes.
- No PHP runtime changes.
- No public route changes.
- No admin UI changes.
- No image edits.
- No Hero Manager or Hero Editor changes.
- No schema cleanup.
- No duplicate-column canonicalization.
- No `db_itemId` backfill execution.
- No `model_id` uniqueness enforcement.

## 19. Recommended next step

After this file location and command interface design, the recommended next task is either:

- A documentation-only implementation-readiness review for the future tool skeleton, or
- A narrowly scoped skeleton-only PR that creates a non-executing placeholder only if explicitly approved.

Full importer logic implementation is not recommended at this stage.
