# 2026-05-21 CSV Remediation Workflow and Frontend Readiness Gating Plan

## 1. Purpose

This document defines how CSV diagnostic findings should be used operationally for remediation planning, admin visibility, future import and update readiness, and frontend readiness gating.

This document is planning-only and governance-only. It does not implement import logic, generate reports, edit the CSV source, change the database, or change frontend or admin behavior.

## 2. Source references

Primary planning and governance references:

- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-Required-Field-Policy-and-Remediation-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Skeleton-Readiness-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-File-Location-and-Command-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Planning-Consistency-Review.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Dry-Run-Importer-Implementation-Checklist.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Pseudocode-Spec.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-No-Execution-Dry-Run-Importer-Report-Design.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-First-Pass-Import-Allowlist-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`

Source CSV:

- `docs/data/SportWarehouse_ProductDB.csv`

Dry-run importer skeleton location:

- `tools/migration/csv_mysql_dry_run_importer.php`

## 3. Current verified diagnostic state

Current verified CLI diagnostic behavior is summarized as follows:

- CSV header check passes.
- CSV row-count check passes with 120 total rows, 54 linked rows, and 66 likely-new rows.
- `model_id` duplicate check identifies expected `nike_female_leggings` x 2.
- `db_itemId` integrity check passes with valid, unique, non-blank `db_itemId` values.
- CSV baseline check passes.
- Required-field and remediation guidance identifies readiness and remediation issues.
- Remediation guidance exits successfully when diagnostics complete and no fatal structural failure exists.
- No DB connection, SQL execution, report generation, or file writes are implemented.

## 4. Severity and readiness category definitions

The categories below are workflow-specific readiness signals. A blocking category does not automatically mean whole-database import must be blocked.

### fatal

- Meaning: Structural failure that prevents diagnostic workflow trust or completion.
- Examples: Missing required header set, unreadable CSV file, malformed structure preventing parser progress.
- Blocks admin-visible copy or import: Yes, because baseline workflow cannot safely proceed.
- Blocks automated import or update readiness: Yes.
- Blocks frontend publication readiness: Yes.
- Likely remediation owner: Engineering and data operations.

### import-readiness-blocking

- Meaning: Row-level or field-level issue that blocks safe automated insert or update decisions.
- Examples: Missing required source identity or linkage field for automated mapping, unresolved key field needed for deterministic updates.
- Blocks admin-visible copy or import: Not necessarily; controlled admin diagnostic import can still be allowed.
- Blocks automated import or update readiness: Yes, for affected rows or workflows.
- Blocks frontend publication readiness: Sometimes indirect; not automatic unless frontend-specific requirements also fail.
- Likely remediation owner: Data operations, source data owners, and engineering for rule enforcement.

### frontend-readiness-blocking

- Meaning: Issue that makes public product display incomplete, misleading, or policy-noncompliant.
- Examples: Missing required price, missing required image asset, missing required category navigation field without approved fallback.
- Blocks admin-visible copy or import: No, if handled in controlled admin diagnostics.
- Blocks automated import or update readiness: Not always; depends on import policy for incomplete but admin-visible rows.
- Blocks frontend publication readiness: Yes, for affected products.
- Likely remediation owner: Merchandising, content operations, accessibility reviewers, and product governance.

### admin-remediation

- Meaning: Data quality or content quality issue that should be corrected but may not block baseline storage or diagnostics.
- Examples: Weak description, missing alt text, missing aria text, metadata cleanup tasks.
- Blocks admin-visible copy or import: No.
- Blocks automated import or update readiness: Usually no, unless combined with stronger requirements.
- Blocks frontend publication readiness: Usually no by itself, but may do so if policy elevates requirement levels.
- Likely remediation owner: Admin users, content team, merchandising, accessibility support.

### advisory

- Meaning: Non-blocking informational finding for quality improvement or future hardening.
- Examples: Optional field sparsity, suggested normalization opportunity, non-critical consistency warning.
- Blocks admin-visible copy or import: No.
- Blocks automated import or update readiness: No.
- Blocks frontend publication readiness: No.
- Likely remediation owner: Engineering and operations backlog owners.

### deferred-governance

- Meaning: Field or rule cannot be enforced consistently until governance decisions are finalized.
- Examples: `parentCategory` enforcement ambiguity, duplicated policy fields needing canonical decision.
- Blocks admin-visible copy or import: No by default.
- Blocks automated import or update readiness: Potentially, where enforcement is undecided.
- Blocks frontend publication readiness: Potentially, if field affects navigation or policy compliance.
- Likely remediation owner: Product governance, taxonomy owners, and engineering leads.

## 5. Operational policy summary

| Category | Admin-visible database copy or import allowed? | Automated import or update readiness allowed? | Frontend publication allowed? | Admin remediation required? | Governance required? | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| fatal | No | No | No | Yes, before any workflow proceeds | Sometimes | Blocks tool or workflow execution until fixed. |
| import-readiness-blocking | Yes, in controlled diagnostics | No for affected automation scope | Conditional | Yes | Sometimes | Blocks specific automation readiness, not necessarily admin-visible diagnostic import. |
| frontend-readiness-blocking | Yes | Conditional | No for affected products | Yes | Sometimes | Blocks public display until remediation or approved fallback. |
| admin-remediation | Yes | Usually yes | Usually yes, policy-dependent | Yes | No | Requires correction workflow but not hard stop by default. |
| advisory | Yes | Yes | Yes | Optional | No | Informational unless later promoted by policy. |
| deferred-governance | Yes | Conditional | Conditional | Sometimes | Yes | Decision required before strict enforcement. |

## 6. Admin-visible import and copy principle

The project may need to copy or import imperfect product rows into an admin-visible database so diagnostic tools and administrators can investigate and remediate issues.

Admin visibility is a controlled backend state and is different from public frontend visibility.

When safe and governed, imperfect items should be allowed into admin diagnostics and remediation workflows, even when those items are not yet approved for frontend publication.

## 7. Frontend readiness gating principle

Frontend publication and display should be gated separately from admin visibility.

Frontend readiness means the product has, at minimum:

- Usable display identity.
- Price when required by policy.
- Image or display assets when required.
- Required category or navigation fields, or an approved fallback behavior.
- Sufficient description or content where policy requires it.
- No dependency on unresolved governance fields that block safe display.

Items with `frontend-readiness-blocking` findings should be hidden from public display, or marked not frontend-ready, until remediation is completed or fallback policy is explicitly approved.

## 8. Automated import and update readiness principle

Automated import or update readiness is a separate gate from admin visibility and frontend readiness.

A row may be admin-visible but still not ready for automated insert or update if required source fields, identity fields, or linkage fields are incomplete.

Future importer work should support row-level readiness decisions and scoped gating, instead of treating the entire CSV as all-or-nothing.

## 9. Excel and CSV source-of-truth remediation pathway

Fixes should occur in Excel or CSV when those files remain the authoritative product source.

Examples include:

- `categoryName` for likely-new rows.
- `price` for likely-new rows.
- `images` and source asset mapping for likely-new rows.
- `external_item_id` for likely-new rows when source linkage is required.
- `model_id` duplicate resolution.
- Product identity fields.
- Taxonomy fields when source-managed.

When CSV remains source-of-truth, source-file remediation is preferred over downstream patch-only corrections.

## 10. Future admin UI remediation pathway

Future admin UI workflows may support selected remediation actions where governance permits.

Candidate examples:

- `altText`.
- `ariaText`.
- `description`.
- Image selection or image metadata corrections.
- Category or taxonomy corrections, if governance allows admin-authoritative edits.
- Product publication status or frontend-ready flag management.

Admin UI remediation must avoid source-of-truth drift unless there is:

- A defined sync or export policy back to Excel or CSV, or
- An explicit decision that database or admin UI is authoritative for selected fields.

## 11. Future guidance and export pathway

Future tooling may provide guidance and export artifacts, but this plan does not generate any reports or files.

Possible future outputs include:

- Console summary.
- CSV remediation checklist.
- Markdown remediation report.
- Admin remediation queue.
- Frontend readiness summary.
- Excel-fix worksheet or export.

Generated report files remain out of scope until a later explicit implementation task approves them.

## 12. Field-to-remediation policy table

| Field | Typical finding | Category | Affected row group | Preferred remediation pathway | Owner | Frontend gate? | Import or update gate? | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| brand | Missing or inconsistent brand naming | admin-remediation | mixed | Excel or CSV source fix, optional admin cleanup | merchandising and data ops | Conditional | Conditional | May affect filters or display quality. |
| gender | Missing or inconsistent taxonomy value | admin-remediation | mixed | Excel or CSV taxonomy correction | merchandising and taxonomy owner | Conditional | Conditional | Policy may elevate to frontend gate for navigation. |
| itemName | Missing or weak product name | frontend-readiness-blocking | affected rows | Excel or CSV identity correction | merchandising and content | Yes | Conditional | Core display identity requirement. |
| subCategory | Missing navigation field | frontend-readiness-blocking | affected rows | Excel or CSV taxonomy correction | taxonomy owner | Yes | Conditional | Approved fallback can reduce severity. |
| model_id | Duplicate or missing model identifier | import-readiness-blocking | affected rows | Excel or CSV identifier remediation | data ops and engineering | Conditional | Yes | Known duplicate `nike_female_leggings` x 2. |
| categoryName | Missing for likely-new rows | import-readiness-blocking | likely-new rows | Excel or CSV source remediation | source data owner | Conditional | Yes | Current known priority. |
| price | Missing where required | frontend-readiness-blocking | likely-new and affected rows | Excel or CSV source remediation | merchandising and pricing owner | Yes | Conditional | Also business-critical for commerce display. |
| images | Missing display assets or unresolved mapping | frontend-readiness-blocking | likely-new and affected rows | Excel or CSV asset mapping, later admin asset tools | content ops | Yes | Conditional | Current known priority for likely-new rows. |
| description | Missing or insufficient content | admin-remediation | affected rows | Admin content remediation, optionally source update | content team | Conditional | No | Can become frontend gate if policy requires. |
| altText | Missing accessibility text | admin-remediation | affected rows | Admin accessibility remediation, optionally source update | content and accessibility owner | Conditional | No | Quality and compliance concern. |
| ariaText | Missing accessibility metadata | admin-remediation | affected rows | Admin accessibility remediation, optionally source update | content and accessibility owner | Conditional | No | Quality and accessibility concern. |
| external_item_id | Missing linkage for likely-new rows when required | import-readiness-blocking | likely-new rows | Excel or CSV source linkage remediation | source data owner and integration owner | No | Yes | Required only where linkage policy applies. |
| parentCategory | Ambiguous enforcement rule | deferred-governance | mixed | Governance decision before strict enforcement | taxonomy governance | Conditional | Conditional | Decision needed on derive, optional, required, or remove. |
| CropAllowed | Duplicated policy field ambiguity | deferred-governance | mixed | Governance and schema policy decision | governance and engineering | No | Conditional | Coordinate with `crop_allowed` canonical policy. |
| crop_allowed | Duplicated policy field ambiguity | deferred-governance | mixed | Governance and schema policy decision | governance and engineering | No | Conditional | Coordinate with `CropAllowed` policy. |
| salePrice | Missing or inconsistent optional sale value | advisory | mixed | Source cleanup as needed | merchandising | No | No | Non-blocking unless sale workflow demands strictness. |
| videos | Missing optional media | advisory | mixed | Optional source or admin media enrichment | content team | No | No | Non-blocking by default. |
| videoAltText | Missing accessibility text for optional videos | advisory | mixed | Optional accessibility enrichment | content and accessibility owner | Conditional | No | Elevate if videos become required. |
| thumbnails_json | Missing or malformed optional thumbnails metadata | advisory | mixed | Source normalization or tooling cleanup | engineering and content ops | No | No | Keep non-blocking unless rendering requires strict parseability. |

## 13. Current known remediation priorities

Based on verified diagnostics, current remediation priorities should be treated as a planning queue, not an immediate execution directive:

- Likely-new rows need `categoryName` remediation.
- Likely-new rows need `price` remediation.
- Likely-new rows need `images` and source asset remediation.
- Likely-new rows need `external_item_id` remediation where source linkage is required.
- `description` needs admin or content remediation for affected rows.
- `altText` and `ariaText` need accessibility and content-quality remediation.
- `parentCategory` requires governance and taxonomy decision before enforcement.
- `model_id` duplicate `nike_female_leggings` x 2 remains a known governance and data issue.

## 14. Product publication and readiness states

Future workflows may use conceptual product states such as:

- `admin_visible`
- `source_needs_remediation`
- `import_ready`
- `frontend_ready`
- `frontend_hidden`
- `governance_deferred`
- `archived_or_hold`

These states are conceptual planning labels only and are not implemented by this task.

## 15. Future admin and backend design implications

Implications for future admin tooling include:

- Diagnostic findings should be visible to administrators.
- Administrators need row-level and item-level remediation detail.
- Admin tools may need filters such as not frontend-ready, needs source fix, needs governance, accessibility missing, image missing, and price missing.
- Admin tools may later provide direct fix forms or export-style guidance outputs.
- Frontend should not expose incomplete products unless explicit fallback policy allows it.

## 16. Future CLI and tooling implications

Future CLI modes may include:

- `--show-frontend-readiness-summary`
- `--show-excel-remediation-summary`
- `--show-admin-remediation-queue`
- `--show-governance-deferred-summary`

These should remain console-only modes until report generation is explicitly approved in a later task.

## 17. Decision points before implementation

Unresolved decisions to settle before implementation:

- Should database or admin UI become authoritative for any fields?
- Should Excel or CSV remain authoritative for all product fields?
- Should frontend-ready status be persisted as a database field?
- Should admin-visible import allow incomplete rows by default?
- Should report or export generation be approved?
- How should source-of-truth drift be reconciled?
- Should `parentCategory` be derived, optional, remediated, or removed from readiness checks?
- Which fields can be remediated directly in admin UI?

## 18. Recommended next step

After this plan is reviewed, the next task should be one of:

- Add a safe `--show-frontend-readiness-summary` CLI mode.
- Add a safe `--show-excel-remediation-summary` CLI mode.
- Create a documentation-only admin remediation queue design.
- Create a documentation-only report or export design.

Recommended first step: add `--show-frontend-readiness-summary`.

Reason: frontend gating is the clearest operational distinction after adopting the corrected severity model.

This recommendation does not include DB writes, report generation, admin UI changes, or importer implementation.

## 19. Non-goals

Explicit non-goals for this task:

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
- No changes to `tools/migration/csv_mysql_dry_run_importer.php` in this task.
