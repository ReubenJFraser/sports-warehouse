# 2026-05-23 DBeaver Staging Table Mapping Checklist

## 1. Purpose

This checklist supports the immediate next operational step: importing/copying the Sports Warehouse CSV into a staging table through DBeaver.

This is not another diagnostic expansion. It is a practical mapping aid for staging import preparation.

## 2. Scope

Current phase: local DBeaver/Laragon MySQL staging import into `localhost:3306` / `sportswh`.

Future phase: possible online/cloud-hosted deployment or migration after local staging/import is verified.

This document is documentation-only and defines planning and mapping guidance only.

- documentation-only
- no database execution
- no SQL execution
- no CSV edits
- no code changes
- no generated report changes
- no admin/frontend behavior changes

## 3. Recommended staging target

Recommended staging table name:

- `product_import_staging`

Confirm the exact staging table name before running the DBeaver import.

For the first staging pass, mirror CSV fields as raw source values as closely as possible.

## 4. Staging-first rule

Staging import is safer than direct live-table import because:

- imperfect rows can be inspected
- frontend publication is not automatically affected
- row counts can be verified
- linked and likely-new rows can be separated later
- live item/product rows are not overwritten
- protected runtime/editor fields are not touched

## 5. Source CSV

Source file:

- `docs/data/SportWarehouse_ProductDB.csv`

Import the CSV as source data into staging only. Do not treat CSV presence as permission to update live runtime fields.

## 6. Minimal DBeaver import checklist

- [ ] open DBeaver
- [ ] connect to local MySQL database (`localhost:3306`, schema `sportswh`)
- [ ] confirm database/schema
- [ ] confirm backup/restore point
- [ ] select CSV import/data transfer workflow
- [ ] select `docs/data/SportWarehouse_ProductDB.csv`
- [ ] confirm header row is used
- [ ] confirm UTF-8/BOM handling
- [ ] choose/create staging table
- [ ] map columns
- [ ] import into staging only
- [ ] verify row counts
- [ ] do not update live frontend tables in this step

## 7. Proposed column mapping table

Use the same name for the staging column as the CSV field unless a concrete, approved reason requires a different name.

| CSV field | staging column | import into staging? | role | notes |
|---|---|---|---|---|
| db_itemId | db_itemId | yes | linkage field | Preserve exact value, including blank vs non-blank state. |
| brand | brand | yes | identity field | Raw source brand value. |
| gender | gender | yes | identity field | Raw source gender value. |
| itemName | itemName | yes | display field | Source display/title field. |
| categoryName | categoryName | yes | taxonomy/category field | Frontend readiness may depend on this, but still import raw value. |
| parentCategory | parentCategory | yes | governance-deferred field | Governance-deferred; preserve as-is in staging. |
| subCategory | subCategory | yes | taxonomy/category field | Preserve source classification value. |
| price | price | yes | pricing field | Preserve source numeric/text representation. |
| salePrice | salePrice | yes | pricing field | Preserve source sale value as imported. |
| description | description | yes | display field | Preserve source descriptive content. |
| featured | featured | yes | optional/enrichment field | Preserve source featured flag/value. |
| images | images | yes | image/source asset field | Import raw image source value only; no runtime overwrite authorization. |
| thumbnails_json | thumbnails_json | yes | image/source asset field | Preserve raw structured asset metadata string/value. |
| altText | altText | yes | accessibility/content field | Preserve source accessibility text. |
| ariaText | ariaText | yes | accessibility/content field | Preserve source accessibility text. |
| videoAltText | videoAltText | yes | accessibility/content field | Preserve source accessibility text. |
| videos | videos | yes | image/source asset field | Preserve source media value. |
| external_item_id | external_item_id | yes | identity field | Preserve external linkage/identity value. |
| model_id | model_id | yes | identity field | Known duplicate group exists; preserve value as-is. |
| CropAllowed | CropAllowed | yes | governance-deferred field | Governance-deferred duplicate-pattern field; preserve raw value. |
| crop_allowed | crop_allowed | yes | governance-deferred field | Governance-deferred duplicate-pattern field; preserve raw value. |
| ageGroup | ageGroup | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| age_group | age_group | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| sizeType | sizeType | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| size_type | size_type | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| fitStyle | fitStyle | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| fit_style | fit_style | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| activityTags | activityTags | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| activity_tags | activity_tags | yes | governance-deferred field | Governance-deferred camelCase/snake_case pattern; preserve raw value. |
| images2 | images2 | yes | image/source asset field | Preserve additional source image value. |
| assignment_source | assignment_source | yes | staging/helper field | Preserve import assignment/source marker value. |
| _images_helper_normalize | _images_helper_normalize | yes | staging/helper field | Preserve helper value for staging review traceability. |

## 8. Field roles

- linkage field: value used to connect a CSV row to an existing database row.
- identity field: value used to describe product identity or external key context.
- display field: value used for item naming or descriptive display content.
- taxonomy/category field: value used for category structure/classification.
- pricing field: value related to price and sale price.
- image/source asset field: value containing image/video or asset source references.
- accessibility/content field: value related to accessibility-oriented text.
- optional/enrichment field: value that enriches content but is not core linkage identity.
- governance-deferred field: known field with policy/normalization decisions intentionally deferred.
- staging/helper field: helper/traceability value useful during staging and review.

## 9. Protected live fields not mapped for overwrite

The staging import must not authorize overwrite of these live runtime/editor fields:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

CSV image-related fields can be imported into staging as raw source values, but they must not overwrite runtime/editor-selected hero fields.

## 10. Linked row handling in staging

- Rows with non-blank `db_itemId` are linked rows.
- Expected linked-row count: 54.
- Staging import should preserve `db_itemId` exactly.
- Staging import does not itself authorize live updates.
- Live update mapping must be approved in a later step.

## 11. Likely-new row handling in staging

- Rows with blank `db_itemId` are likely-new rows.
- Expected likely-new row count: 66.
- Staging import should preserve blank `db_itemId` values.
- Blank `db_itemId` must not be automatically backfilled in this step.
- Likely-new rows are not frontend-ready under current policy.
- Staging import remains useful for admin/source review.

## 12. Post-import row-count verification

After DBeaver staging import, run/verify these checks in DBeaver (no executable SQL provided in this document):

- staging table row count = 120
- non-blank `db_itemId` count = 54
- blank `db_itemId` count = 66
- `model_id` duplicate `nike_female_leggings` x 2 remains understood
- no live/frontend table changed
- no protected runtime/editor fields overwritten

## 13. Frontend publication warning

Staging import must not expose likely-new rows to the public frontend.

Frontend publication requires a separate future gating/publish decision.

## 14. What is enough to proceed with staging import

Proceed to DBeaver staging import once all of the following are true:

- staging table name is confirmed
- local database/schema (`sportswh`) is confirmed
- backup/rollback exists
- CSV header mapping is reviewed
- import target is staging only
- protected fields are not overwritten
- frontend publication is not affected

## 15. What remains after staging import

- verify imported row counts
- compare staging schema/columns
- decide update allowlist
- decide insert allowlist
- decide `db_itemId` backfill policy
- resolve or govern `model_id` duplicate
- decide frontend publication gating
- plan live update/insert separately

## 16. Non-goals

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
- no Hero Manager / Hero Editor changes
- no schema cleanup
- no duplicate-column canonicalization
- no `db_itemId` backfill execution
- no `model_id` uniqueness enforcement
- no changes to `tools/migration/csv_mysql_dry_run_importer.php` in this task

## 17. Immediate next step

After this checklist is reviewed, the immediate next step is a human-guided DBeaver staging import preparation step:

- confirm local DB/schema (`localhost:3306` / `sportswh`)
- confirm staging table name
- decide whether DBeaver will create the staging table from CSV or import into a pre-created staging table
- then perform the staging import carefully

Do not start another diagnostic/reporting task unless a concrete import blocker is found.
