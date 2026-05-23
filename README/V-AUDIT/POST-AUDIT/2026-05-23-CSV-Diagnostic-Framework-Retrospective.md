# CSV Diagnostic Framework Retrospective (2026-05-23)

## 1. Purpose

This retrospective evaluates the CSV diagnostic and reporting framework that was developed around the original practical goal of copying and importing Sports Warehouse Excel and CSV product data into MySQL through DBeaver.

Its purpose is to decide what was worthwhile, what was excessive, and how to avoid endless diagnostic expansion that delays actual import execution.

## 2. Original operational goal

Current phase: local DBeaver/Laragon MySQL staging import into `localhost:3306` / `sportswh`.

Future phase: possible online/cloud-hosted deployment or migration after local staging/import is verified. Cloudways is currently discontinued due to cost and is not part of this immediate workflow.

The original operational goal was simple: copy and import the newly structured Excel and CSV product database into the local DBeaver/Laragon MySQL database (`localhost:3306`, schema `sportswh`) through DBeaver.

In a humble workflow, this might have been completed in a day through manual paste or a straightforward CSV import.

## 3. What actually happened

Instead of taking only the shortest import path, the project developed a broader framework including:

- CSV structural checks
- db_itemId integrity checks
- model_id duplicate checks
- frontend-readiness diagnostics
- required-field policy diagnostics
- Excel and source remediation summaries
- admin remediation queues
- governance-deferred summaries
- generated report and export artifacts
- documentation and runbooks

## 4. Honest assessment: was this overbuilt?

Yes, this was overbuilt relative to the narrow immediate task of copying data into MySQL.

However, overbuilt does not automatically mean wasted.

The tradeoff was clear:

- more time spent before import
- slower immediate progress
- more complexity
- but stronger evidence, operational safety, and future maintainability

## 5. Why it was not a waste of time

The framework produced concrete value:

- row-count certainty (120 total rows)
- linked versus likely-new row distinction (54 linked with non-blank db_itemId, 66 likely-new with blank db_itemId)
- clear understanding of frontend readiness
- clear separation between admin-visible import and public frontend publication
- protection against overwriting runtime and editor-managed fields
- source-of-truth remediation visibility
- governance-deferred issue tracking
- generated audit evidence
- idempotent generated artifacts
- safer future DBeaver import path

## 6. What would have been risky about the simple path

Simply copying, pasting, or importing the Excel data into DBeaver without this framework would have carried several risks:

- unclear row grouping
- accidental frontend exposure risk
- unknown missing data for images, categoryName, and price
- hidden model_id duplicate problem
- confusion over blank db_itemId values
- source-of-truth drift risk
- no clear remediation queue
- no audit trail for decisions

## 7. Comparison with a professional or enterprise-style workflow

The approach resembles, in spirit, what larger organizations do, even if the tools differ.

A multinational company would likely use formal ETL pipelines, staging environments, CI checks, migrations, data-quality dashboards, approval workflows, and deployment gates.

Sports Warehouse is much smaller, but the principles are aligned:

- do not blindly import data
- validate structure
- separate staging from production
- distinguish admin visibility from public publication
- preserve audit evidence
- document governance decisions
- make remediation actionable

## 8. Why this approach may be appropriate even for a smaller project

Because AI and Codex can scaffold disciplined workflows faster than manual development alone, it can be rational to apply enterprise-style data discipline in a student or portfolio project.

This strengthens the portfolio by demonstrating:

- systems thinking
- data governance awareness
- operational safety
- documentation discipline
- frontend and backend separation
- admin workflow thinking
- practical AI-assisted development

## 9. The danger of continuing forever

The same process becomes harmful if it keeps expanding after it has served its purpose.

Diagnostics and reports must not become a substitute for execution.

Principle: more diagnostics are justified only when they remove a real blocker or reduce a real operational risk.

## 10. What is now enough

The project now has enough diagnostic evidence to move toward DBeaver staging and import planning because:

- CSV is structurally readable
- row counts are known
- linked and new split is known
- frontend readiness is known
- remediation targets are known
- governance-deferred issues are documented
- generated artifacts exist
- admin-visible import can be separated from frontend publication

## 11. What still must not be rushed

Before live update or insert, these items still require careful handling:

- exact local schema confirmation (`localhost:3306` / `sportswh`)
- staging table versus direct target table decision
- backup and rollback plan
- column mapping validation
- protected field exclusion
- db_itemId backfill policy
- model_id duplicate decision
- frontend publication gating

## 12. Strategic conclusion

This framework was too much if judged only against the narrow task of copying data into MySQL.

It was worthwhile if judged as building a safer, more professional, portfolio-quality data workflow.

The correct next move is not to keep expanding diagnostics. The next move is to use the framework to support the actual DBeaver import and copy runbook and staging process.

## 13. Practical rule going forward

Do not add another diagnostic, report, or generated artifact unless it directly supports one of the following:

- staging import and copy
- live schema mapping
- rollback safety
- frontend publication gating
- Excel and source remediation
- admin remediation
- governance decisions required before import

## 14. Recommended next operational direction

Next priority should be:

- finish the DBeaver CSV-to-MySQL import and copy runbook
- create a minimal staging-table mapping checklist if needed
- move toward actual staging import and copy execution
- verify row counts after import
- only then continue remediation and governance work as needed

## 15. Non-goals

This retrospective task explicitly excludes:

- no CSV edits
- no database writes
- no DBeaver execution
- no ALTER TABLE execution
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
- no db_itemId backfill execution
- no model_id uniqueness enforcement
- no changes to tools/migration/csv_mysql_dry_run_importer.php in this task

## Appendix: Current framework facts acknowledged in this retrospective

- Total CSV rows: 120
- Linked rows with non-blank db_itemId: 54
- Likely-new rows with blank db_itemId: 66
- Linked rows are frontend-ready under current CSV-only policy
- Likely-new rows are not frontend-ready under current CSV-only policy
- Frontend-readiness blocking fields include categoryName, images, and price
- Known model_id duplicate group: nike_female_leggings x 2
- subCategoryParent is governance-deferred
- CropAllowed and crop_allowed plus camelCase and snake_case duplicate fields are governance-deferred
- Readiness-blocking does not mean blocking admin-visible copy or import
- Imperfect rows can be admin-visible for diagnosis and remediation while public frontend display remains gated
