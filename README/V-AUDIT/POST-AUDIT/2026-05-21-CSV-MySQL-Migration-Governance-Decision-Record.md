# CSV to MySQL Migration Governance Decision Record

Date: 2026-05-21
Status: Open governance decisions required before executable migration or import work

## 1. Purpose

This record captures unresolved governance decisions that block executable CSV to MySQL migration and import work.

This document is documentation only. It does not execute, authorize, or approve any database change.

## 2. Source references

- `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`
- `docs/operations/generated/2026-05-20-live-schema-verification-report.md`
- `docs/data/SportWarehouse_ProductDB.csv`

## 3. Governance blocker A - duplicate model_id

Current evidence to carry forward:

- Total CSV rows: 120.
- `model_id` is nonblank for all rows.
- Duplicate value detected: `nike_female_leggings` x 2.
- `UNIQUE(model_id)` must not be added yet.
- Migration or import planning may use `model_id` for validation, but not as an enforced unique key until this is resolved.

Possible resolution options:

1. Rename one `model_id` value in CSV so each row is unique.
2. Add variant specificity to the formula derived `model_id` logic.
3. Explicitly approve a temporary non-unique `model_id` policy.
4. Defer `UNIQUE(model_id)` until after manual CSV correction.

Required decision to unblock executable work:

- Which option is approved?
- Who or what authority approves it?
- Where is the corrected source value recorded?

## 4. Governance blocker B - CropAllowed and crop_allowed

Current evidence to carry forward:

- Both columns exist in live `item`.
- Values differ across inspected rows.
- `CropAllowed` contains meaningful Yes or No style source values.
- `crop_allowed` appears defaulted to `1`.
- No blind overwrite or canonicalization is approved.

Possible resolution options:

1. Treat `CropAllowed` as source authoritative and map future imports from CSV `CropAllowed`.
2. Treat `crop_allowed` as runtime boolean only after explicit conversion from `CropAllowed`.
3. Keep both columns temporarily and define read and write mapping.
4. Defer this decision and exclude `CropAllowed` and `crop_allowed` from the first import allowlist.

Required decision to unblock executable work:

- Which column is source authoritative?
- Which column, if any, should runtime code read?
- Should conversion `Yes` or `No` to `1` or `0` be performed later?
- Should either column be excluded from initial migration or import scope?

## 5. Confirmed naming governance decisions already supported by live evidence

The following naming pairs currently favor CSV camelCase value source:

- `ageGroup` and `age_group`
- `sizeType` and `size_type`
- `fitStyle` and `fit_style`
- `activityTags` and `activity_tags`

Current interpretation:

- camelCase columns are populated.
- snake_case counterparts are mostly or fully blank.
- Future migration or import should prefer CSV camelCase source values unless a later compatibility rollout is approved.
- No drop or rename action is approved in this document.

## 6. Protected runtime and editor fields

Future import and update allowlists must continue to exclude:

- `item.hero_image`
- `item.chosen_image`
- `hero_override.chosen_image`

## 7. Decision status table

| Issue | Current evidence | Risk if ignored | Current status | Required next decision | Blocks executable migration? |
| --- | --- | --- | --- | --- | --- |
| duplicate `model_id` `nike_female_leggings` | CSV has 120 rows, `model_id` is nonblank, one duplicate value appears twice | Unique key enforcement can fail or create incorrect merges | Open blocker | Approve resolution option and record corrected source value | Yes |
| `CropAllowed` and `crop_allowed` | Both columns exist and inspected values diverge; `CropAllowed` appears meaningful and `crop_allowed` appears defaulted | Data semantics can be overwritten or misread by runtime | Open blocker | Approve source authority, runtime read column, and conversion or exclusion policy | Yes |
| `ageGroup` and `age_group` | camelCase populated, snake_case mostly blank | Wrong source precedence can degrade imported value quality | Evidence confirmed | Reconfirm camelCase source precedence in import mapping | No, if current precedence is preserved |
| `sizeType` and `size_type` | camelCase populated, snake_case mostly blank | Wrong source precedence can degrade imported value quality | Evidence confirmed | Reconfirm camelCase source precedence in import mapping | No, if current precedence is preserved |
| `fitStyle` and `fit_style` | camelCase populated, snake_case mostly blank | Wrong source precedence can degrade imported value quality | Evidence confirmed | Reconfirm camelCase source precedence in import mapping | No, if current precedence is preserved |
| `activityTags` and `activity_tags` | camelCase populated, snake_case mostly blank | Wrong source precedence can degrade imported value quality | Evidence confirmed | Reconfirm camelCase source precedence in import mapping | No, if current precedence is preserved |
| protected hero and editor fields | Runtime and editor managed columns are known protected targets | Import overwrite can break editorial selections and runtime behavior | Policy required | Keep explicit exclusion in first and later import allowlists | Yes |
| `db_itemId` backfill for 66 new rows | 66 new CSV rows need policy for ID backfill behavior | Row matching and post import reconciliation can fail | Open blocker | Define and approve deterministic backfill policy before execution | Yes |

## 8. Impact on next migration stage

Executable migration or import work should not begin until all of the following are satisfied:

- duplicate `model_id` decision is recorded.
- `CropAllowed` and `crop_allowed` decision is recorded, or the fields are explicitly deferred out of the first import allowlist.
- protected field exclusions remain enforced.
- `db_itemId` backfill policy for 66 new rows is defined.

## 9. Non-goals

This document does not include any of the following:

- DB writes
- migrations
- `ALTER TABLE` execution
- importer execution
- repair SQL
- generated report changes
- PHP changes
- image edits
- Hero Manager or Hero Editor changes

## 10. Recommended next step after this document

Recommended follow-up planning task:

- either resolve the `model_id` duplicate in the CSV source plan,
- or draft a first pass import allowlist that explicitly excludes unresolved governance fields.
