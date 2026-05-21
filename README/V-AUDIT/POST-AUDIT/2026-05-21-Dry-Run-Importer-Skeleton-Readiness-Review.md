# 2026-05-21 Dry-Run Importer Skeleton Readiness Review

## 1. Purpose

This document reviews project readiness for a future skeleton-only pull request for a CSV-to-MySQL dry-run importer placeholder.

This review is documentation-only. It does not create the skeleton file, does not implement importer logic, does not generate reports, and does not approve database writes or import execution.

## 2. Source documents reviewed

### File location and interface design
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-File-Location-and-Command-Design.md`

### Dry-run planning documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Planning-Consistency-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Implementation-Checklist.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Pseudocode-Spec.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`

### Governance blocker documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`

### Migration design/reference documents
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

### Source CSV reference
- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Known migration facts

- 120 CSV rows.
- 54 existing rows linked by `db_itemId`.
- 66 likely new insert candidates with blank `db_itemId`.
- `db_itemId` is the current live linkage field.
- `model_id` exists in CSV but has one documented duplicate.
- `UNIQUE(model_id)` remains deferred until duplicate resolution is applied.
- `CropAllowed` and `crop_allowed` are deferred from first-pass import scope.
- `item.hero_image`, `item.chosen_image`, and `hero_override.chosen_image` are protected runtime/editor fields.
- No executable importer or report generation has yet been approved.

## 4. Readiness question

Is the project ready for a future skeleton-only PR that creates a non-executing placeholder file at `tools/migration/csv_mysql_dry_run_importer.php`?

This is not a question about implementing importer logic.

## 5. Skeleton-only scope definition

If later approved, a future skeleton-only PR may include only the following:

- Create `tools/migration/` if it does not already exist.
- Create `tools/migration/csv_mysql_dry_run_importer.php`.
- Include a top-level documentation comment describing purpose and non-execution constraints.
- Include a hard fail/exit message stating the tool is not implemented yet.
- Include no database connection.
- Include no CSV reading.
- Include no report writing.
- Include no SQL.
- Include no mutation logic.
- Include no runtime/admin/public integration.

## 6. Skeleton-only prohibited scope

A future skeleton-only PR must not include:

- No DB connection.
- No SQL queries.
- No CSV parsing.
- No allowlist processing.
- No row classification.
- No field comparison.
- No report generation.
- No file writes.
- No generated reports.
- No import execution.
- No admin UI link.
- No public route.
- No Composer/package changes unless separately justified.
- No schema changes.
- No `db_itemId` backfill.
- No `model_id` uniqueness enforcement.

## 7. File location readiness check

Planned location reviewed: `tools/migration/csv_mysql_dry_run_importer.php`.

| Readiness item | Status | Notes |
| --- | --- | --- |
| Location is outside public routes | ready | Planned under internal `tools/` path, not route-facing. |
| Location is outside admin UI | ready | Not an admin controller/view location. |
| Location is project-internal | ready | `tools/migration/` is internal tooling namespace. |
| Name clearly indicates dry-run | ready | `dry_run_importer` is explicit. |
| Name clearly indicates CSV-to-MySQL scope | ready | `csv_mysql` prefix makes source/target intent explicit. |
| File path matches prior command/interface design | ready | Matches approved planning command shape. |

## 8. Command/interface readiness check

Planned command reviewed: `php tools/migration/csv_mysql_dry_run_importer.php --dry-run`.

| Interface item | Status | Notes |
| --- | --- | --- |
| Command is CLI-oriented | ready | Starts with `php` and targets script path directly. |
| `--dry-run` remains the default conceptual mode | ready | Planning context remains no-execution and non-mutating. |
| No write flags approved | ready | No write mode approved in planning set. |
| No report-generation flag approved yet | ready | Reporting remains deferred for later approval. |
| Future `--write-reports` remains deferred | needs review | Explicit later governance approval required before addition. |
| Future `--execute`/`--apply`/`--write-db` remain disallowed | ready | Write-like execution flags are outside approved scope. |

## 9. Safety readiness check

| Safety constraint | Status | Notes |
| --- | --- | --- |
| No database writes | ready | Skeleton-only step can fully avoid DB I/O. |
| No CSV writes | ready | Skeleton-only file can avoid any file mutation behavior. |
| No generated report writes | ready | No reporting functions needed for placeholder. |
| No SQL | ready | No SQL generation or execution needed. |
| No protected field handling beyond documentation comments | ready | Placeholder can document constraints without touching fields. |
| No deferred governance handling beyond documentation comments | ready | Deferred items remain documented-only. |
| No importer logic | ready | Hard-stop placeholder prevents accidental logic execution. |
| Clear hard-stop message | ready | Required as explicit guardrail. |
| No callable web route | ready | CLI file path is non-route and non-public by design. |

## 10. Dependency readiness check

The skeleton-only PR should require no new dependencies.

| Dependency area | Needed for skeleton-only PR? | Notes |
| --- | --- | --- |
| Database connector | no | No DB connection allowed in placeholder step. |
| CSV parser | no | No CSV read/parse allowed in placeholder step. |
| Report writer | no | No report generation allowed in placeholder step. |
| Environment loader | no | Not needed for a hard-stop script. |
| Composer packages | no | No package additions should be required. |
| PHPUnit or test harness | no | Validation can be done with lint and safe CLI check. |
| Admin UI assets | no | No UI integration allowed. |

## 11. Testing readiness check

Appropriate checks for a future skeleton-only PR:

- `php -l tools/migration/csv_mysql_dry_run_importer.php`
- `git diff --check`
- Manual CLI run should print a not-implemented/readiness message and exit safely.
- Confirm no generated reports are created.
- Confirm no DB files/config are touched.
- Confirm `git status` shows only intended skeleton file and possibly directory creation.

These are future checks only. This review does not run or create the tool.

## 12. Documentation comment requirements for future skeleton

The future skeleton file top comment should state:

- Documentation-only planning sequence exists.
- Tool is not implemented.
- Tool is read-only by design when later implemented.
- No DB writes are approved.
- No report generation is approved in the skeleton-only step.
- Protected fields must never be written.
- Deferred governance fields remain excluded.
- Use is CLI-only.
- Public/admin invocation is not supported.

## 13. Exit behavior requirements for future skeleton

Future skeleton behavior should:

- Print a clear message that importer skeleton exists but implementation is not approved.
- Exit with a non-zero status, or a clearly documented safe status, depending on project convention.
- Not parse CSV.
- Not connect to DB.
- Not write files.
- Not generate reports.
- Not accept write-like flags.

Recommendation: non-zero exit is preferable for an unimplemented placeholder because it reduces accidental assumptions of successful import execution.

## 14. Risk assessment

| Risk | Likelihood | Impact | Mitigation |
| --- | --- | --- | --- |
| Skeleton mistaken for working importer | medium | medium | Include prominent top comment and hard-stop runtime message. |
| Accidental future DB connection added too early | medium | high | Keep explicit no-DB rule in skeleton comment and PR constraints. |
| Web/admin route accidentally linked | low | high | Keep file in `tools/migration/` and prohibit route/UI changes. |
| Report generation added before approval | medium | medium | Prohibit report flags and writer code in skeleton-only scope. |
| Write flags introduced prematurely | medium | high | Disallow `--execute`, `--apply`, `--write-db`, and similar flags. |
| Governance blockers bypassed | low | high | Require governance references in comment and future PR prompt constraints. |

## 15. Readiness decision

Ready for future skeleton-only PR.

Allowed future scope is narrow: create only a non-executing placeholder file at `tools/migration/csv_mysql_dry_run_importer.php` (and directory creation only if required), with documentation comment plus hard-stop message, and no importer logic or side effects.

## 16. Required constraints for the future skeleton-only PR

The future Codex prompt should include all constraints below:

- Create only `tools/migration/csv_mysql_dry_run_importer.php` and directory if needed.
- No DB connection.
- No CSV reading.
- No SQL.
- No report generation.
- No generated report changes.
- No PHP runtime changes.
- No public route changes.
- No admin UI changes.
- No image changes.
- No Hero Manager / Hero Editor changes.
- `php -l` must pass.
- Manual CLI run must be safe.
- `git diff --check` must pass.

## 17. Non-goals

- No CSV edits.
- No DB writes.
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
- No creation of the skeleton file in this task.

## 18. Recommended next step

Proceed with a future skeleton-only PR that creates a non-executing placeholder at:

- `tools/migration/csv_mysql_dry_run_importer.php`

Do not include implementation logic yet.
