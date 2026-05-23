# 2026-05-21 First-Pass Import Allowlist Plan

## 1. Purpose

This document defines a documentation-only first-pass import boundary for CSV-to-MySQL migration planning. It is intended to set the allowed and disallowed field scope before any executable importer implementation or migration SQL is authored.

## 2. Source references

This plan is based on the following governance and planning references:

- `README/V-AUDIT/POST-AUDIT/2026-05-21-Model-ID-Duplicate-Resolution-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CropAllowed-Governance-Decision-Plan.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`
- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Known migration facts

- CSV row count in scope: 120 rows.
- Existing runtime rows linked by `db_itemId`: 54 rows.
- Likely new insert candidates with blank `db_itemId`: 66 rows.
- `model_id` duplicate condition is documented, but the source CSV has not been edited yet.
- `CropAllowed` and `crop_allowed` are deferred from the first import allowlist pending governance completion.
- Protected Hero Manager and editor image fields are excluded from first-pass import scope.

## 4. Allowlist principles

- The first importer must be allowlist-based and must not use broad overwrite behavior.
- Source CSV field names should be preserved in staging for traceability.
- Runtime item updates must be explicit and field-by-field.
- Protected fields are never imported from CSV in update or insert write paths.
- Unresolved governance fields remain deferred until an explicit decision record permits inclusion.

## 5. First-pass update allowlist for 54 linked rows

| CSV field | Runtime item field | Update allowed? | Reason | Notes |
| --- | --- | --- | --- | --- |
| brand | brand | yes | Core catalog attribute | Standard text overwrite candidate. |
| gender | gender | yes | Core catalog attribute | Requires normal validation only. |
| itemName | itemName | yes | Primary product display name | Keep runtime naming conventions unchanged. |
| categoryName | categoryName | yes | Catalog taxonomy attribute | Subject to existing taxonomy checks. |
| subCategoryParent | subCategoryParent | yes | Catalog taxonomy attribute | Subject to existing taxonomy checks. |
| subCategory | subcategory | yes | CSV-to-runtime mapped taxonomy field | Explicit mapping required (`subCategory` -> `subcategory`). |
| price | price | yes | Core sell price data | Numeric validation required. |
| salePrice | salePrice | yes | Promotional price data | Numeric validation and bounds checks required. |
| description | description | yes | Product content field | Plain content update only. |
| featured | featured | yes | Merchandising flag | Normalize to runtime boolean format. |
| images | images | yes | Product image list payload | Parsing and normalization required. |
| thumbnails_json | thumbnails_json | yes | Thumbnail metadata payload | Must remain JSON-valid after normalization. |
| altText | altText | yes | Accessibility text | Content quality review may be needed. |
| ariaText | ariaText | yes | Accessibility text | Content quality review may be needed. |
| videoAltText | videoAltText | yes | Video accessibility text | Content quality review may be needed. |
| videos | videos | yes | Video metadata field | Validate structure before update. |
| external_item_id | external_item_id | yes | External system linkage key | Keep stable when already populated unless governance says otherwise. |
| model_id | model_id | deferred | Duplicate governance blocker not fully resolved | Allow only after duplicate resolution is applied, or keep uniqueness enforcement deferred in first executable pass. |

## 6. First-pass insert allowlist for 66 new rows

| CSV field | Runtime item field | Insert allowed? | Reason | Notes |
| --- | --- | --- | --- | --- |
| brand | brand | yes | Required core catalog field | Include in insert payload. |
| gender | gender | yes | Required core catalog field | Include in insert payload. |
| itemName | itemName | yes | Required product identity field | Include in insert payload. |
| categoryName | categoryName | yes | Required catalog taxonomy field | Include in insert payload. |
| subCategoryParent | subCategoryParent | yes | Required catalog taxonomy field | Include in insert payload. |
| subCategory | subcategory | yes | Required mapped taxonomy field | Use explicit CSV-to-runtime mapping. |
| price | price | yes | Required commerce field | Numeric validation required. |
| salePrice | salePrice | yes | Optional commerce field used by runtime | Include when present and valid. |
| description | description | yes | Product content field | Include when present. |
| featured | featured | yes | Merchandising flag | Normalize to runtime boolean format. |
| images | images | yes | Product image list payload | Parsing and normalization required. |
| thumbnails_json | thumbnails_json | yes | Thumbnail metadata payload | Must remain JSON-valid. |
| altText | altText | yes | Accessibility field | Include when present. |
| ariaText | ariaText | yes | Accessibility field | Include when present. |
| videoAltText | videoAltText | yes | Accessibility field | Include when present. |
| videos | videos | yes | Video metadata field | Validate structure before insert. |
| external_item_id | external_item_id | yes | External system linkage key | Include if present and non-conflicting. |
| model_id | model_id | deferred | Conditional on duplicate governance outcome | Insert only after model duplicate handling and uniqueness posture are approved. |
| db_itemId | itemId reference link | deferred | Requires controlled identity backfill policy | Do not blindly pre-assign from CSV before insert; use a post-insert backfill policy. |

## 7. Deferred governance fields

| Field | Status | Why deferred or conditional |
| --- | --- | --- |
| CropAllowed | deferred | Governance decision is pending and excluded from first-pass import boundary. |
| crop_allowed | deferred | Governance decision is pending and excluded from first-pass import boundary. |
| model_id uniqueness and enforcement | conditional | Keep deferred until duplicate resolution is approved and enforcement strategy is confirmed. |
| db_itemId backfill for new rows | deferred | Needs a documented post-insert linkage policy to avoid identity mismatch risk. |
| ageGroup / age_group | conditional | Include only if compatibility rollout is approved in governance and schema handling. |
| sizeType / size_type | conditional | Include only if compatibility rollout is approved in governance and schema handling. |
| fitStyle / fit_style | conditional | Include only if compatibility rollout is approved in governance and schema handling. |
| activityTags / activity_tags | conditional | Include only if compatibility rollout is approved in governance and schema handling. |

## 8. Staging-only / import-helper fields

The following fields are staging and import-helper scope only for first pass:

- `images2`
- `assignment_source`
- `_images_helper_normalize`

These fields should remain in staging and import audit context unless a later governance decision explicitly re-scopes them into runtime writes.

## 9. Protected fields - never overwrite

The following fields are protected and must never be overwritten from CSV:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

These protected fields must not appear in any CSV-driven update `SET` clause.

## 10. Dry-run report requirements

Before any execution-capable implementation is approved, a dry-run report must show:

- Rows matched by `db_itemId` = 54.
- Insert candidates = 66.
- Exact fields that would change for update candidates.
- Confirmation that protected fields are excluded.
- Confirmation that deferred fields are excluded.
- Rows requiring manual review with reason tags.

## 11. Non-goals

This plan explicitly does not do any of the following:

- No CSV edits.
- No DB writes.
- No migrations.
- No `ALTER TABLE` execution.
- No importer execution.
- No repair SQL.
- No generated report changes.
- No PHP changes.
- No image edits.
- No Hero Manager or Hero Editor changes.

## 12. Recommended next step

After this allowlist plan is accepted, the next task should draft a no-execution dry-run importer and report design artifact that validates update and insert candidates without executing import writes.
