# CSV Field Ownership and Remediation Policy (2026-05-23)

## 1. Purpose

This policy defines field ownership and stage-specific readiness for the Sports Warehouse CSV-to-local-DBeaver staging workflow.

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
- Excel/CSV source-owned fields.
- Admin-backend operational or remediation fields.
- Governance-deferred fields.
- Runtime/editor-protected fields.

A field's ownership determines where it should be fixed and when it is required.

## 4. Stage-specific readiness gates

The workflow uses stage-specific gates instead of a universal completeness rule.

1) Staging import readiness:
- Staging import is allowed when the CSV is structurally safe and mapping is correct.
- Staging import does not require every product-content or operational field to be complete.
- Meaningful blanks are preserved for later readiness decisions.

2) Live insert/update readiness:
- This gate is stricter than staging and is where source-owned critical structured fields are enforced.
- Live insert/update requires either an initial `price` value or an approved admin price-entry workflow.
- Rows with blank `db_itemId` can still stage, but are not linked/update-ready until controlled insert and identity policy are settled.

3) Frontend publication readiness:
- This is the strictest gate for user-visible completeness.
- Frontend publication requires product identity, display safety, accessibility policy compliance where applicable, and required commercial fields.
- Missing required display fields can block publication even when staging and live existence are allowed.

4) Optional/enrichment readiness:
- Enrichment fields can remain blank without blocking staging.
- They are completed only when relevant to product quality goals or campaign needs.

5) Feature/toggle-dependent readiness:
- Some fields become required only when a feature is enabled.
- Example: sale pricing, video, featured merchandising toggles.

6) Governance-deferred readiness:
- Fields with unresolved policy remain non-blocking for staging.
- They must not be auto-resolved by importer logic until governance decisions are made.

## 5. Staged evidence now available

Current local staging evidence:
- `product_import_staging` contains 120 rows.
- 54 rows are linked (non-blank `db_itemId`).
- 66 rows are likely-new (blank `db_itemId`).
- Those 66 likely-new rows currently lack `categoryName`, `price`, `images`, and `external_item_id`.

This identifies readiness work for later gates, not a staging-import failure.

## 6. Protected and governance baseline policies

The following baseline policies remain unchanged:
- Staging-first strategy remains correct.
- Current phase remains local DBeaver/Laragon MySQL on `localhost:3306`, schema `sportswh`.
- Future cloud or online deployment is later only.
- Governance-deferred fields must not be auto-resolved.
- Protected runtime/editor fields must not be overwritten.
- No frontend publication of incomplete products.

Runtime/editor-protected fields that must not be overwritten by CSV-driven flow:
- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

CSV image fields can be staged as source values but must never overwrite those protected hero image fields.

## 7. Field-specific policy clarifications

### 7.1 price and salePrice

- `price` is a hybrid source/operational field.
- `price` may need an initial value before frontend publication.
- `price` does not need to be completed before staging import.
- Ongoing `price` maintenance may belong in the admin backend because prices can change over time.
- Live insert/update requires either an initial `price` value or an approved admin price-entry workflow.
- `salePrice` is conditional and should only be required if the product is on sale or sale display is enabled.

### 7.2 featured

- `featured` is an admin/backend editorial or operational toggle.
- `featured` is not required for staging import.
- `featured` should not block live product existence.
- `featured` affects merchandising/editorial presentation only.
- `featured` should be managed in admin/backend rather than permanently fixed in Excel.

### 7.3 videos and videoAltText

- `videos` are optional/enrichment unless a video module or campaign feature is enabled.
- `videoAltText` is required only when a video exists and accessibility policy requires it.
- Missing video fields should not block staging import.
- Missing video fields should not block frontend publication unless the product is intended to display video.

### 7.4 description, altText, and ariaText

- `description`, `altText`, and `ariaText` do not block staging import.
- They may be better completed in admin backend UI workflows.
- They may be required before frontend publication depending on content and accessibility policy.
- They are not necessarily Excel/CSV source-of-truth fields.
- If Excel remains authoritative for any of them, admin/backend remediation must include source-of-truth drift safeguards.

### 7.5 images and hero controls

- Source image mapping in `images`/`images2` may remain Excel/CSV-owned.
- Display or hero-image choice remains protected and admin-managed.
- Missing `images` does not block staging import.
- Missing `images` blocks frontend publication unless an approved fallback policy exists.
- CSV image fields must not overwrite `item.hero_image`, `item.chosen_image`, or `hero_override.chosen_image`.

### 7.6 db_itemId

- Blank `db_itemId` for likely-new rows is expected and meaningful.
- `db_itemId` should not be manually completed in Excel before staging.
- `db_itemId` backfill is a future import/database policy issue after controlled insert.
- Blank `db_itemId` blocks linked/update-ready treatment, but not staging import.

## 8. Field ownership and gate table

| field | ownership category | required before staging import? | required before live insert/update? | required before frontend publication? | optional or toggle-dependent? | preferred remediation location | notes |
|---|---|---|---|---|---|---|---|
| db_itemId | Governance-deferred identity field | No | Yes for linked update path; No for initial staging of likely-new rows | Yes for confident linked updates; insert path policy applies | Governance-dependent | Governance policy plus controlled DB operation | Blank is meaningful for likely-new rows and should not be prefilled in Excel before staging. |
| brand | Excel/CSV source-owned | No | Yes (normally) | Yes (normally) | No | Excel/CSV | Structured merchandising/filter field. |
| gender | Excel/CSV source-owned | No | Usually yes | Usually yes | Sometimes taxonomy-dependent | Excel/CSV | Structured segmentation field. |
| itemName | Excel/CSV source-owned | No | Yes | Yes | No | Excel/CSV | Core product identity label. |
| model_id | Excel/CSV source-owned plus governance check | No | Yes with duplicate policy | Yes with duplicate policy | Governance-dependent for duplicates | Excel/CSV plus governance review | Duplicate governance remains required. |
| categoryName | Excel/CSV source-owned | No | Yes for insert-ready rows | Yes unless approved fallback | No | Excel/CSV | Missing in likely-new rows marks readiness work for later gates. |
| subCategory | Excel/CSV source-owned | No | Usually yes | Usually yes | Taxonomy-policy dependent | Excel/CSV | Taxonomy granularity field. |
| external_item_id | Excel/CSV source-owned | No | Yes where linkage policy requires | Usually yes for traceability | Policy-dependent | Excel/CSV | Missing in likely-new rows is a live readiness concern, not staging failure. |
| price | Hybrid source/operational | No | Yes, or approved admin price-entry workflow | Yes for normal purchasable display | Sometimes workflow-dependent | Initial source value in Excel/CSV or approved admin workflow | Ongoing maintenance may belong in admin backend. |
| salePrice | Hybrid conditional commercial field | No | Required only when on-sale logic is enabled | Required only when sale display is enabled | Yes | Admin/backend or Excel/CSV per sale workflow | Conditional field; do not enforce universally. |
| featured | Admin/backend editorial toggle | No | No | Only if merchandising policy requires explicit state | Yes | Admin/backend | Should not block live product existence. |
| description | Admin-backend remediation content field | No | Not universally required | Policy-dependent, often yes before publication | Yes | Admin/backend (or governed source workflow) | Long-form content often better maintained in admin UI. |
| altText | Admin-backend accessibility content field | No | Not universally required | Required when accessibility policy requires before publication | Yes | Admin/backend (with drift safeguards if source-authoritative) | Not a staging blocker. |
| ariaText | Admin-backend accessibility content field | No | Not universally required | Required when accessibility policy requires before publication | Yes | Admin/backend (with drift safeguards if source-authoritative) | Not a staging blocker. |
| videos | Optional enrichment or feature module field | No | No unless module-enabled workflow requires | No unless product is intended to display video | Yes | Admin/backend or governed source workflow | Optional unless video feature/campaign is enabled. |
| videoAltText | Conditional accessibility enrichment | No | No unless video workflow requires | Required only when video exists and policy requires | Yes | Admin/backend | Depends on actual video presence and policy. |
| images | Excel/CSV source-owned mapping field with protected runtime display controls | No | Yes for insert-ready media completeness | Yes unless approved fallback | Sometimes fallback-dependent | Excel/CSV for source mapping; admin/backend for hero/display choice | Must not overwrite `item.hero_image`, `item.chosen_image`, `hero_override.chosen_image`. |
| images2 | Governance-deferred or secondary source mapping | No | Policy-dependent | Policy-dependent | Yes | Governance decision then source/admin as chosen | Clarify semantics relative to `images`. |
| campaign_or_series | Governance-deferred marketing classification | No | Policy-dependent | Policy-dependent | Yes | Governance then source/admin | May drive campaign-specific publication logic. |
| activityTags / activity_tags | Governance-deferred structured taxonomy variant | No | Policy-dependent | Policy-dependent | Yes | Governance then likely Excel/CSV | Dual-column canonicalization policy still required. |

## 9. Practical recommendation

Recommended direction:
- Do not require all long-form content or operational fields to be completed in Excel before staging.
- Use staging to preserve and inspect source data.
- Fix obvious source-owned structured gaps before live insert/update where needed.
- Use admin/backend workflows for operational/editorial completion where appropriate.
- Use frontend publication gates to prevent incomplete products from being public.
- Use toggles for optional modules such as videos, featured merchandising, and sale pricing.

## 10. Immediate next operational direction

Immediate practical direction:
- Staging import has succeeded and should be treated as a raw local MySQL staging snapshot.
- The next decision is not whether staging can happen; it has happened.
- The next decision is which fields must be remediated before live insert/update or frontend publication.
- Do not proceed to live insert/update until source-owned critical fields and gating policy are settled.

## 11. Relationship to generated artifacts

Current artifacts supporting this policy include:
- `csv-excel-remediation-checklist.csv` identifies source fixes.
- `csv-admin-remediation-queue.csv` identifies admin-review candidates.
- `csv-frontend-readiness-summary.md` identifies publication readiness.
- `csv-governance-deferred-summary.md` identifies policy decisions.

## 12. Non-goals

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
- No Hero Manager/Hero Editor changes.
- No schema cleanup.
- No duplicate-column canonicalization execution.
- No `db_itemId` backfill execution.
- No `model_id` uniqueness enforcement execution.
- No changes to `tools/migration/csv_mysql_dry_run_importer.php`.
