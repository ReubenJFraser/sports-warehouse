# 2026-05-21 CSV Required-Field Policy and Remediation Plan

## 1. Purpose

This document defines a required-field policy and remediation category framework for CSV-to-MySQL migration planning in Sports Warehouse.

This is a documentation-only planning artifact. It does not edit the CSV, change the database, implement importer logic, generate reports, or modify admin UI behavior.

## 2. Source references

This policy is informed by the following planning and governance documents:

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
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`

Source CSV reference:

- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Current diagnostic context

Current `--check-required-fields` output is useful for diagnosis, but too blunt as a strict pass/fail gate.

Current observations:

- The checker is useful diagnostically.
- A single required-field list is too blunt for pass/fail.
- `subCategoryParent` is blank across all rows.
- Likely-new rows (blank `db_itemId`) have many missing values and are not insert-ready.
- Linked rows (non-blank `db_itemId`) are generally more complete but still have selected gaps.
- Further checker refinement should wait for a policy foundation.

## 4. Known row groups

For policy and future diagnostics, use these CSV-only row groups:

- All rows.
- Linked rows with non-blank `db_itemId`.
- Likely-new rows with blank `db_itemId`.

These are diagnostic groupings from CSV state only. They do not prove live MySQL row matching or runtime equivalence.

## 5. Required-field policy principles

- Not every blank field is equally serious.
- A field can be required for one purpose but optional for another.
- Update readiness and insert readiness are different checks.
- Frontend display readiness differs from migration readiness.
- Accessibility and content-quality fields may be required for publication quality even if not strictly required for insertion.
- Governance-deferred fields must not be forced into strict required checks until policy is approved.
- Diagnostics should lead toward remediation pathways, not only report failure.

## 6. Field category definitions

- **Globally required identity fields**: Core identifiers expected across all rows for consistent catalog identity.
- **Linked-row update readiness fields**: Fields expected when planning safe updates to known linked records.
- **Likely-new insert readiness fields**: Fields expected before considering insertion of likely-new records.
- **Frontend display readiness fields**: Fields needed for acceptable storefront rendering and navigation.
- **Accessibility/content-quality fields**: Fields supporting accessibility quality and content completeness.
- **Optional/enrichment fields**: Fields that improve merchandising but are not hard blockers by default.
- **Staging/helper fields**: Technical fields used for staging, transforms, assignment tracing, or helper logic.
- **Deferred governance fields**: Fields with unresolved governance that should not be strict blockers yet.
- **Protected/runtime/editor fields**: Fields controlled by runtime/editor behavior rather than CSV as a strict source.
- **Fields needing policy clarification**: Fields with unresolved semantics before they can be strict required checks.

## 7. Field classification table

| CSV field | Proposed category | Required for all rows? | Required for linked-row update readiness? | Required for likely-new insert readiness? | Required for frontend display readiness? | Required for accessibility/content quality? | Remediation owner | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| db_itemId | Staging/helper fields | No | Contextual | No | No | No | Migration tooling/governance | Grouping key only; blank implies likely-new grouping. |
| brand | Globally required identity fields | Yes | Yes | Yes | Yes | No | CSV source owner | Core catalog identity. |
| gender | Globally required identity fields | Yes | Yes | Yes | Contextual | No | CSV source owner | Identity/facet field. |
| itemName | Globally required identity fields | Yes | Yes | Yes | Yes | No | CSV source owner | Core display identity. |
| categoryName | Frontend display readiness fields | No | Contextual | Yes | Yes | No | CSV source owner | Important for taxonomy/navigation. |
| subCategoryParent | Fields needing policy clarification | No | Deferred | Deferred | Contextual | No | Governance + CSV source owner | Blank in all rows; policy unresolved. |
| subCategory | Globally required identity fields | Yes | Yes | Yes | Yes | No | CSV source owner | Required for initial policy identity and navigation. |
| price | Frontend display readiness fields | No | Contextual | Yes | Yes | No | CSV source owner (or pricing governance) | Insert/display critical for sellable item UX. |
| salePrice | Optional/enrichment fields | No | No | No | Contextual | No | Pricing governance | Optional promotional enrichment. |
| description | Frontend display readiness fields | No | Contextual | Yes | Yes | Contextual | CSV source owner or admin (future) | Important to content quality and conversion. |
| featured | Optional/enrichment fields | No | No | No | No | No | Merchandising owner | Merchandising flag, not migration blocker. |
| images | Frontend display readiness fields | No | Contextual | Yes | Yes | Contextual | CSV source owner or admin (future) | Primary visual readiness signal. |
| thumbnails_json | Staging/helper fields | No | No | No | No | No | Tooling/runtime owner | Derived/helper candidate. |
| altText | Accessibility/content-quality fields | No | Contextual | Yes | Contextual | Yes | CSV source owner or admin (future) | Accessibility expectation for image content. |
| ariaText | Accessibility/content-quality fields | No | Contextual | Yes | Contextual | Yes | CSV source owner or admin (future) | Accessibility support text. |
| videoAltText | Accessibility/content-quality fields | No | Contextual | Conditional | No | Conditional | CSV source owner or admin (future) | Required when `videos` present; optional otherwise. |
| videos | Optional/enrichment fields | No | No | No | Contextual | No | Content/merchandising owner | Optional media enrichment. |
| external_item_id | Likely-new insert readiness fields | No | Contextual | Yes | No | No | CSV source owner | External mapping/traceability for inserts. |
| model_id | Globally required identity fields | Yes | Yes | Yes | Contextual | No | CSV source owner + governance | Identity and uniqueness governance context. |
| CropAllowed | Deferred governance fields | No | Deferred | Deferred | No | No | Governance owner | Deferred until crop policy finalization. |
| crop_allowed | Deferred governance fields | No | Deferred | Deferred | No | No | Governance owner | Duplicate governance pair with `CropAllowed`. |
| ageGroup | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | CamelCase/snake_case pair governance pending. |
| age_group | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Duplicate governance pair pending decision. |
| sizeType | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Pair with `size_type`; defer strictness. |
| size_type | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Duplicate governance pair pending decision. |
| fitStyle | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Pair with `fit_style`; defer strictness. |
| fit_style | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Duplicate governance pair pending decision. |
| activityTags | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Pair with `activity_tags`; defer strictness. |
| activity_tags | Deferred governance fields | No | Deferred | Deferred | Contextual | No | Governance owner | Duplicate governance pair pending decision. |
| images2 | Optional/enrichment fields | No | No | No | Contextual | No | CSV source owner or admin (future) | Secondary media enrichment. |
| assignment_source | Staging/helper fields | No | No | No | No | No | Migration tooling owner | Diagnostic lineage/helper metadata. |
| _images_helper_normalize | Staging/helper fields | No | No | No | No | No | Migration tooling owner | Helper normalization output field. |

## 8. Initial recommended policy

This is an initial conservative recommendation, not an executable rule set yet.

### 8.1 Globally required identity fields

- `brand`
- `gender`
- `itemName`
- `subCategory`
- `model_id`

### 8.2 Likely-new insert readiness fields (subject to later confirmation)

- `brand`
- `gender`
- `itemName`
- `categoryName`
- `subCategory`
- `price`
- `description`
- `images`
- `altText`
- `ariaText`
- `external_item_id`
- `model_id`

### 8.3 Linked-row update readiness fields

- Evaluate field-by-field against allowlist intent.
- A blank may be diagnostic and non-blocking when runtime already has value coverage.
- No CSV-to-live-MySQL value comparison is implemented yet.

### 8.4 Frontend display readiness fields

- `itemName`
- `brand`
- `price`
- `images`
- `description`
- Category/subcategory fields needed for navigation.

### 8.5 Accessibility/content-quality fields

- `altText`
- `ariaText`
- `videoAltText` when `videos` exists

### 8.6 Optional/enrichment fields

- `salePrice`
- `videos`
- `videoAltText` when `videos` is blank
- `featured`

### 8.7 Deferred governance fields

- `CropAllowed`
- `crop_allowed`
- Duplicate camelCase/snake_case governance pairs until approved.

## 9. subCategoryParent policy question

`subCategoryParent` is blank across all rows and needs explicit policy treatment.

Possible interpretations:

- It may be derivable from `categoryName` and `subCategory`.
- It may not be needed by current runtime behavior.
- It may represent a future taxonomy layer.
- It may require Excel/CSV source remediation.
- It may need removal from strict required checks until governance decides its role.

Recommendation: Treat `subCategoryParent` as **needs policy clarification** and not as a hard failure at this stage.

## 10. Remediation pathways

- **Fix in Excel/CSV source of truth**: Use when authoritative catalog data is intended to originate in CSV/Excel and should remain canonical.
- **Fix later in admin UI**: Use only if governance explicitly allows admin-side edits for specific fields.
- **Generate future remediation guidance/export**: Use when diagnostics should produce actionable fix queues without changing data directly.
- **Defer pending governance decision**: Use for unresolved fields, duplicate columns, and policy-dependent requirements.
- **Leave optional**: Use where business value exists but blocking migration/readiness is not justified.

## 11. Excel/CSV remediation guidance

Because CSV/Excel is currently treated as source of truth, many missing catalog values should likely be corrected there first.

Likely candidates for CSV/Excel correction (especially for likely-new rows):

- `categoryName`
- `price`
- `images`
- `external_item_id`
- `altText` and `ariaText` when source-managed
- `description` when source-managed

## 12. Future admin remediation guidance

If governance permits, some fields may later be suitable for admin UI remediation, such as:

- `altText`
- `ariaText`
- `description`
- `images`
- Category/taxonomy fields
- `price` and `salePrice` only if pricing governance permits admin editing

Admin remediation must avoid source-of-truth drift unless there is a defined sync/export-back policy.

## 13. Future checker refinement recommendations

After this policy is accepted, evolve `--check-required-fields` into policy-based diagnostics with multiple categories:

- Separate all-row identity checks.
- Separate linked-row update readiness checks.
- Separate likely-new insert readiness checks.
- Separate frontend display readiness checks.
- Separate accessibility/content-quality checks.
- Include remediation-owner labels.
- Include severity levels: blocking, warning, advisory, deferred.
- Keep `subCategoryParent` non-blocking until policy clarification is completed.

## 14. Suggested severity model

- **Blocking**: Must be fixed before a defined migration action can proceed (for example, likely-new insert readiness core fields).
- **Warning**: Important gap likely requiring near-term remediation, but not an immediate hard block for all workflows.
- **Advisory**: Improvement opportunity with lower immediate risk.
- **Deferred**: Governed field not yet approved for strict enforcement.
- **Protected/no-action**: Field intentionally runtime-controlled or helper-managed; diagnostics should not ask operators to overwrite blindly.

## 15. Suggested future output model

Future checker/report-style output categories should include:

- Field
- Row group
- Blank count
- Severity
- Remediation owner
- Suggested remediation pathway
- Sample row numbers
- Governance note

This section defines output shape guidance only and does not generate any report artifact.

## 16. Non-goals

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
- No Hero Manager/Hero Editor changes.
- No schema cleanup.
- No duplicate-column canonicalization.
- No `db_itemId` backfill execution.
- No `model_id` uniqueness enforcement.
- No changes to `tools/migration/csv_mysql_dry_run_importer.php` in this task.

## 17. Recommended next step

After review/approval of this document, the next task should refine `--check-required-fields` to report policy-based categories and severity labels instead of a single blunt pass/fail required-field list.

Do not edit CSV yet.
Do not start admin UI remediation yet.
Do not perform database writes or importer implementation yet.
