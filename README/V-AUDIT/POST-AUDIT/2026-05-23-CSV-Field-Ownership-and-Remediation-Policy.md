# CSV Field Ownership and Remediation Policy (2026-05-23)

## 1. Purpose

This policy defines field ownership for the Sports Warehouse CSV-to-local-DBeaver staging workflow.

The purpose is to avoid treating every blank or incomplete field the same way.

This document is documentation-only. It does not import data, edit CSV files, change the database, generate reports, or implement admin UI changes.

## 2. Current workflow phase

Current phase:
- Local DBeaver/Laragon MySQL staging import.
- Connection target: `localhost:3306`.
- Database/schema target: `sportswh`.
- Intended staging table: `product_import_staging`.

Future phase:
- Online or cloud-hosted deployment may be considered after local staging/import is verified.
- Cloudways is discontinued for now and is not part of the current workflow.

Current goal:
- Prepare a clean and reviewable staging/import pathway without pretending every field must be complete in Excel first.

## 3. Core policy distinction

This policy uses four field ownership categories:
- Excel/CSV source-of-truth fields.
- Admin-backend remediation fields.
- Governance-deferred fields.
- Runtime/editor-protected fields.

A field's ownership determines where it should be fixed, not merely whether it is blank.

## 4. Excel/CSV source-of-truth fields

Structured data should usually be maintained in Excel/CSV because it is easier to validate in bulk and directly affects mapping, import behavior, taxonomy, pricing, and source linkage.

Examples include:
- `brand`
- `gender`
- `itemName`
- `model_id`
- `external_item_id`
- `categoryName`
- `subCategory`
- `parentCategory` (if later governed as source-managed)
- `price`
- `salePrice`
- `images` and source asset mapping
- `product_domain`
- `collection`
- `model_family`
- `ageGroup` / `age_group` (if governed)
- `sizeType` / `size_type` (if governed)
- `fitStyle` / `fit_style` (if governed)
- `activityTags` / `activity_tags` (if governed)

These fields should usually be fixed before live insert/update or frontend publication.

## 5. Admin-backend remediation fields

Long-form content and accessibility text may be better completed in the admin backend because admin UI surfaces can provide textareas, image preview, product preview, validation messaging, and review context.

Examples include:
- `description`
- `altText`
- `ariaText`
- `videoAltText`
- Image captions or content notes, if added later

These fields may be imported into staging as blanks or partial values, then completed in admin if a future admin remediation workflow is approved.

## 6. Governance-deferred fields

Some fields require policy or schema decisions before they can be treated as required or automatically remediated.

Examples include:
- `parentCategory`
- `CropAllowed` / `crop_allowed`
- `ageGroup` / `age_group`
- `sizeType` / `size_type`
- `fitStyle` / `fit_style`
- `activityTags` / `activity_tags`
- `model_id` duplicate group `nike_female_leggings x 2`
- `db_itemId` backfill policy

Governance-deferred fields should not block admin-visible staging import by themselves.

## 7. Runtime/editor-protected fields

The following fields must not be overwritten by CSV-driven import:
- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

CSV image/source fields may be imported into staging as raw values, but those values do not authorize overwriting runtime/editor-selected images.

## 8. Field ownership table

| field | ownership category | preferred remediation location | required before staging import? | required before live insert/update? | required before frontend publication? | notes |
|---|---|---|---|---|---|---|
| db_itemId | Governance-deferred | Governance policy plus controlled data operation | No | Yes (for update path policy) | Yes (for row identity confidence) | Distinguish linked vs likely-new rows; do not auto-backfill in staging-only phase. |
| brand | Excel/CSV source-of-truth | Excel/CSV | No | Yes | Yes | Structured product attribute for merchandising and filters. |
| gender | Excel/CSV source-of-truth | Excel/CSV | No | Usually yes | Usually yes | Treated as structured taxonomy/segmentation field. |
| itemName | Excel/CSV source-of-truth | Excel/CSV | No | Yes | Yes | Core product identity field. |
| itemName_fully_derived | Governance-deferred | Governance policy first, then source/admin by decision | No | Policy-dependent | Policy-dependent | Clarify derivation rule before making required. |
| model_id | Excel/CSV source-of-truth plus governance check | Excel/CSV with governance review on duplicates | No | Yes | Yes | Duplicate policy needed for `nike_female_leggings x 2`. |
| product_domain | Excel/CSV source-of-truth | Excel/CSV | No | Usually yes | Usually yes | Useful for structured grouping. |
| collection | Excel/CSV source-of-truth | Excel/CSV | No | Usually yes | Usually yes | Supports merchandising and internal grouping. |
| model_family | Excel/CSV source-of-truth | Excel/CSV | No | Usually yes | Usually yes | Structured source-managed family signal. |
| categoryName | Excel/CSV source-of-truth | Excel/CSV | No | Yes (insert-ready rows) | Yes (or approved fallback) | Primary taxonomy routing input. |
| parentCategory | Governance-deferred | Governance first, then Excel/CSV if source-managed | No | Policy-dependent | Policy-dependent | Not auto-required until governance decision. |
| subCategory | Excel/CSV source-of-truth | Excel/CSV | No | Usually yes | Usually yes | Taxonomy granularity field. |
| price | Excel/CSV source-of-truth | Excel/CSV | No | Yes (insert-ready rows) | Yes | Commercial requirement for live display/sale. |
| salePrice | Excel/CSV source-of-truth | Excel/CSV | No | Policy-dependent | Policy-dependent | Required only when sale logic applies. |
| description | Admin-backend remediation | Admin backend (or source if future policy says so) | No | Policy-dependent | Usually yes if copy is required | Long-form content; may remain blank in staging. |
| featured | Governance-deferred | Governance policy and admin control | No | Policy-dependent | Policy-dependent | Feature flag behavior should be explicitly governed. |
| images | Excel/CSV source-of-truth | Excel/CSV | No | Yes (insert-ready rows) | Yes | Source asset mapping field; does not override protected runtime fields. |
| thumbnails_json | Governance-deferred | Governance plus technical mapping decision | No | Policy-dependent | Policy-dependent | Decide whether generated, imported, or ignored. |
| altText | Admin-backend remediation | Admin backend | No | Policy-dependent | Yes if accessibility policy requires | Better authored with media context. |
| ariaText | Admin-backend remediation | Admin backend | No | Policy-dependent | Yes if accessibility policy requires | Accessibility copy often needs UI context. |
| videoAltText | Admin-backend remediation | Admin backend | No | Policy-dependent | Policy-dependent | Applies when video assets exist. |
| videos | Governance-deferred | Governance plus admin/source workflow decision | No | Policy-dependent | Policy-dependent | Determine media governance before strict requirement. |
| images2 | Governance-deferred | Governance decision, then source/admin as chosen | No | Policy-dependent | Policy-dependent | Clarify semantic role vs `images`. |
| external_item_id | Excel/CSV source-of-truth | Excel/CSV | No | Yes if linkage policy requires | Usually yes for traceability | Key external linkage signal. |
| campaign_or_series | Governance-deferred | Governance policy then source/admin decision | No | Policy-dependent | Policy-dependent | Marketing classification policy pending. |
| CropAllowed | Governance-deferred | Governance policy | No | Policy-dependent | Policy-dependent | Duplicate/variant governance with `crop_allowed`. |
| crop_allowed | Governance-deferred | Governance policy | No | Policy-dependent | Policy-dependent | Canonicalization decision required. |
| ageGroup | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| age_group | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| sizeType | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| size_type | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| fitStyle | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| fit_style | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| activityTags | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| activity_tags | Governance-deferred | Governance policy then likely Excel/CSV | No | Policy-dependent | Policy-dependent | Resolve dual-column governance. |
| assignment_source | Governance-deferred | Governance plus process ownership decision | No | Policy-dependent | Policy-dependent | Define provenance semantics before requiring. |
| _images_helper_normalize | Runtime/editor-protected support field | Import tooling/internal mapping only | No | No | No | Helper/internal field; must not be used to overwrite editor-chosen images. |

## 9. Staging import policy

Staging import may proceed even if admin-remediation fields are incomplete, provided structural and source conditions are safe.

Staging import should preserve:
- Raw CSV values.
- Blanks where meaningful.
- `db_itemId` distinction between linked and likely-new rows.
- Source fields needed for later review.

Staging import should not:
- Publish products.
- Update live `item` or `product` rows.
- Overwrite protected runtime/editor fields.
- Backfill `db_itemId`.
- Resolve governance-deferred fields automatically.

## 10. What should be fixed before staging import

Minimal fixes needed before staging import:
- CSV is structurally readable.
- Header mapping is correct.
- Staging table exists or is created correctly.
- Row count expectation is understood.
- Source file is the intended CSV (`docs/data/SportWarehouse_ProductDB.csv`).
- Staging import will not touch live frontend/public tables.

Long-form content completion is not required before staging import.

## 11. What should be fixed before live insert/update

Live insert/update requires stricter readiness controls.

Likely required before live insert/update:
- `model_id` policy.
- `external_item_id` and source linkage, if required.
- `categoryName` for insert-ready rows.
- `price` for insert-ready rows.
- `images` and source asset mapping for insert-ready rows.
- `db_itemId` backfill/update policy.
- Explicit update/insert allowlist.
- Protected-field non-overwrite rules.

## 12. What should be fixed before frontend publication

Frontend publication requires products to be safe and complete for users.

Likely required before frontend publication:
- `categoryName` or approved fallback.
- `price`.
- `images` or equivalent display asset.
- Sufficient product identity.
- `description`, if policy requires user-facing copy.
- `altText`/`ariaText`, if accessibility policy requires them before publication.
- No unresolved governance issue that affects display or routing.

## 13. Admin remediation implications

Admin backend remediation may be valuable because it can provide:
- Product image preview.
- Frontend-like preview.
- Larger text editing fields.
- Validation messages.
- Review queues.
- Publication status signals.
- Accessibility checks.

However, admin remediation requires source-of-truth rules to avoid drift.

## 14. Source-of-truth drift policy

Risk:
If Excel remains source-of-truth while admin edits `description`, `altText`, or `ariaText`, database content and Excel content can diverge.

Possible future solutions:
- Export approved admin edits back to Excel/CSV.
- Make database/admin authoritative for selected content fields.
- Maintain a sync/reconciliation report.
- Decide authority field-by-field.

This document does not implement a sync policy; it identifies the need for one.

## 15. Practical recommendation

Recommended direction:
- Keep structured/source fields in Excel/CSV.
- Allow staging import with incomplete content fields.
- Use admin backend later for long-form content remediation, if approved.
- Do not block staging import merely because `description`/`altText`/`ariaText` are incomplete.
- Do not publish incomplete products to frontend until readiness criteria are met.

## 16. Relationship to generated artifacts

Current artifacts supporting this policy include:
- `csv-excel-remediation-checklist.csv` identifies source fixes.
- `csv-admin-remediation-queue.csv` identifies admin-review candidates.
- `csv-frontend-readiness-summary.md` identifies publication readiness.
- `csv-governance-deferred-summary.md` identifies policy decisions.

## 17. Immediate next operational direction

Immediate practical next step:
- Continue preparing local `product_import_staging` import.
- Do not require all long-form content to be completed in Excel before staging import.
- Use staging table to hold source data.
- Use generated artifacts to decide what must be fixed in Excel before live insert/update and what can be handled later in admin.

## 18. Non-goals

This task does not perform any of the following:
- No CSV edits.
- No database writes.
- No DBeaver execution.
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
