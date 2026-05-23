# 2026-05-21 CSV Remediation Report and Export Design (No Generation)

## 1. Purpose

This document defines a documentation-only design for future report and export artifacts that support CSV remediation, frontend readiness review, Excel/source remediation workflows, admin remediation queue planning, and governance-deferred findings review.

This task does not generate reports, write output files, edit CSV data, connect to a database, execute SQL, implement importer logic, or modify admin or frontend behavior.

## 2. Source references

Planning and governance references:

- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-Required-Field-Policy-and-Remediation-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-Remediation-Workflow-and-Frontend-Readiness-Gating-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Skeleton-Readiness-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-File-Location-and-Command-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Planning-Consistency-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`

Source CSV:

- `docs/data/SportWarehouse_ProductDB.csv`

Related importer skeleton path (for context only):

- `tools/migration/csv_mysql_dry_run_importer.php`

## 3. Current verified CLI state

The current dry-run CLI modes are safe, read-only, and console-only diagnostics. Their current purpose is summarized below.

- Baseline CSV checks:
  - `--check-csv-header`: validates expected header structure.
  - `--check-csv-row-count`: validates row-count expectations.
  - `--check-csv-baseline`: runs baseline structural checks.
- Required-field policy diagnostics:
  - `--check-required-fields`: evaluates required-field readiness policy.
- Duplicate/integrity diagnostics:
  - `--check-model-id-duplicates`: reports `model_id` duplicate conditions.
  - `--check-db-item-id-integrity`: reports `db_itemId` integrity findings.
- Remediation guidance:
  - `--show-remediation-guidance`: prints remediation-oriented guidance.
- Frontend readiness summary:
  - `--show-frontend-readiness-summary`: currently reports 54 frontend-ready linked rows and 66 not-frontend-ready likely-new rows.
- Excel/source remediation summary:
  - `--show-excel-remediation-summary`: runs successfully and exits 0.

All current mode outputs are console-only. No report files are generated. No DB writes occur.

## 4. Report/export design principles

Future report/export implementation should follow these principles:

1. Generated artifacts require explicit implementation approval before any write behavior is added.
2. Reports are review aids and remediation tools, not import execution approval.
3. Reports must clearly distinguish:
   - admin-visible copy/import viability,
   - automated import/update readiness, and
   - frontend publication readiness.
4. Report content must never imply DB writes or importer execution occurred.
5. Report language must preserve source-of-truth clarity between source spreadsheet/CSV and downstream systems.
6. Reports should be remediation-oriented and actionable, not failure-list-only dumps.
7. Findings must be separated by category and severity, including:
   - fatal,
   - import-readiness-blocking,
   - frontend-readiness-blocking,
   - admin-remediation,
   - advisory,
   - deferred-governance.

## 5. Proposed output directory

Recommended future generated report location:

- `docs/operations/generated/`

Why this location is appropriate:

- It centralizes operational artifacts in a dedicated location.
- It keeps generated outputs separate from policy, governance, and implementation code.
- It supports future git tracking policy decisions (tracked, ignored, or mixed).

This task must not create or modify any files in `docs/operations/generated/`.

## 6. Proposed future report/export artifacts

| Artifact path | Format | Purpose | Source CLI mode | Intended audience | Generation approved now? | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| `docs/operations/generated/csv-remediation-summary.md` | Markdown | High-level remediation status and priorities. | `--show-remediation-guidance` (or future dedicated write mode) | Data remediation leads, audit reviewers | no | Should summarize categories and actionable next steps. |
| `docs/operations/generated/csv-frontend-readiness-summary.md` | Markdown | Frontend publication readiness status with gating context. | `--show-frontend-readiness-summary` | Product, content, QA, frontend stakeholders | no | Must separate frontend readiness from admin-visible import/copy viability. |
| `docs/operations/generated/csv-excel-remediation-checklist.csv` | CSV | Row-level checklist for source Excel or CSV repair planning. | `--show-excel-remediation-summary` (or future dedicated write mode) | Spreadsheet remediation operators | no | Review-only output; not executable input. |
| `docs/operations/generated/csv-admin-remediation-queue.csv` | CSV | Queue of issues potentially fixable in admin UI after controlled import. | `--show-remediation-guidance` plus readiness diagnostics | Admin operations, content editors | no | Must include source-of-truth drift warnings. |
| `docs/operations/generated/csv-governance-deferred-summary.md` | Markdown | Summary of decisions intentionally deferred to governance. | Governance-related diagnostics and policy outputs | Governance owners, technical leads | no | Should enumerate unresolved decision points and policy dependencies. |
| `docs/operations/generated/csv-required-field-diagnostics.csv` | CSV | Structured required-field findings by row/field. | `--check-required-fields` | Data quality and remediation teams | no | Intended for triage and batching. |
| `docs/operations/generated/csv-model-id-duplicate-summary.csv` | CSV | Duplicate `model_id` findings and remediation notes. | `--check-model-id-duplicates` | Data remediation and governance teams | no | Must not imply uniqueness constraints were enforced automatically. |
| `docs/operations/generated/csv-db-item-id-integrity-summary.csv` | CSV | `db_itemId` integrity findings and handling paths. | `--check-db-item-id-integrity` | Import planning and governance teams | no | Must align with approved backfill policy only after governance sign-off. |

## 7. Markdown report design

Future Markdown reports should include:

- Executive summary.
- Data source path(s), including `docs/data/SportWarehouse_ProductDB.csv`.
- Timestamp and source context when write modes are later implemented.
- Row-count summary and grouping context.
- Severity and category summary.
- Frontend readiness summary.
- Admin-visible import/copy guidance.
- Remediation priorities and suggested sequencing.
- Governance decisions required.
- Non-goals and safety notes.

## 8. CSV export design

Future CSV exports should be structured for remediation workflows. Candidate columns:

- `row_number`
- `db_itemId`
- `model_id`
- `itemName`
- `row_group`
- `field`
- `finding_category`
- `readiness_category`
- `blank_or_issue_count`
- `remediation_owner`
- `remediation_pathway`
- `suggested_action`
- `governance_note`
- `frontend_ready`
- `import_ready`
- `admin_visible_ok`

All CSV exports are review and remediation aids only. They must not be treated as executable import files.

## 9. Frontend readiness report design

Future frontend-readiness outputs should include:

- Frontend-ready row counts.
- Not-frontend-ready row counts.
- Linked versus likely-new row split.
- Blocking frontend fields and category breakdown.
- Sample affected rows for triage context.
- Suggested frontend gating treatment and publication sequencing.
- A clear note that frontend readiness does not equal database import readiness.

## 10. Excel/source remediation checklist design

Future Excel remediation checklist output should include:

- Source field to fix.
- Affected row group.
- Affected row numbers.
- Suggested Excel or CSV action.
- Source-of-truth note.
- Governance note where relevant.

The checklist may guide manual worksheet updates, but it must never perform automatic spreadsheet or CSV edits.

## 11. Admin remediation queue design

Future admin remediation queue output should include:

- Fields potentially fixable later in admin UI.
- Accessibility and content quality issues.
- Missing descriptions and content completeness gaps.
- Image metadata issues.
- Items requiring review before frontend publication.
- Warning about source-of-truth drift unless sync/export policy is approved.

## 12. Governance-deferred summary design

Future governance-deferred output should track unresolved governance topics, including:

- `parentCategory` treatment.
- `CropAllowed` versus `crop_allowed` policy.
- camelCase versus snake_case duplicate-field policy.
- `model_id` duplicate governance.
- `db_itemId` backfill policy.
- Source-of-truth decision ownership and status.

## 13. Safety and approval gates

Before any report generation is implemented, the following gates should be approved:

1. Report paths approved.
2. Generated directory policy approved.
3. No DB writes.
4. No CSV edits.
5. No importer execution.
6. No report treated as executable input.
7. Privacy and source-data considerations reviewed.
8. Output naming convention approved.
9. Expected git tracking behavior approved.

## 14. Naming convention

Recommended naming convention for latest-state outputs uses stable filenames, for example:

- `csv-remediation-summary.md`
- `csv-frontend-readiness-summary.md`

Date-stamped filenames may be added later only if historical snapshots are explicitly required by workflow policy.

## 15. Git tracking policy

Generated report tracking policy should be explicitly selected before implementation. Potential policies:

- Commit generated reports only when explicitly useful as audit evidence.
- Ignore generated reports by default.
- Track small Markdown summaries while ignoring larger CSV exports.
- Preserve current known untracked generated files as-is in this task.

## 16. Relationship to current untracked generated files

Currently known untracked files:

- `docs/operations/generated/image-sync-reconciliation-report.csv`
- `docs/operations/generated/image-sync-reconciliation-summary.md`

This task does not modify, delete, classify, or add these files.

## 17. Future CLI implications

Potential future write flags (not implemented in this task):

- `--write-remediation-summary`
- `--write-frontend-readiness-summary`
- `--write-excel-remediation-checklist`
- `--write-admin-remediation-queue`
- `--write-governance-deferred-summary`

These write flags are not approved yet and should remain unavailable until a later explicit implementation task.

## 18. Recommended implementation sequence

Recommended cautious sequence:

1. Keep current console-only modes stable.
2. Add documentation-only approval for report paths and formats.
3. Add one report writer at a time, starting with a Markdown summary or Excel remediation checklist.
4. Keep write behavior behind explicit flags.
5. Confirm generated files in `git status` before committing.
6. Do not implement DB writes or importer logic as part of report generation.

Suggested first implementation later (not now):

- Markdown frontend-readiness summary if audit/readiness evidence is the immediate goal, or
- Excel remediation checklist CSV if practical source-data repair is the immediate goal.

## 19. Non-goals

This task explicitly excludes:

- no CSV edits
- no database writes
- no `ALTER TABLE` execution
- no executable SQL
- no importer implementation
- no report generation
- no generated report changes
- no PHP runtime changes
- no public route changes
- no admin UI changes
- no image edits
- no Hero Manager or Hero Editor changes
- no schema cleanup
- no duplicate-column canonicalization
- no `db_itemId` backfill execution
- no `model_id` uniqueness enforcement
- no changes to `tools/migration/csv_mysql_dry_run_importer.php` in this task

## 20. Recommended next step

After this design is reviewed, the next approved implementation task should be one of the following:

- implement a safe Markdown-only `--write-frontend-readiness-summary` mode, or
- implement a safe CSV-only `--write-excel-remediation-checklist` mode.

Recommended choice depends on immediate objective:

- choose Markdown frontend readiness summary for audit/readiness evidence,
- choose Excel remediation checklist for practical source-data repair.

Both options require explicit approval before implementation.
