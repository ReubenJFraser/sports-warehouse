# 2026-05-21 CropAllowed Governance Decision Plan

Date: 2026-05-21
Status: Documentation-only planning record

## 1. Purpose

This document frames the `CropAllowed` and `crop_allowed` governance blocker before any executable CSV-to-MySQL migration or import work.

This plan is documentation only. It does not execute, authorize, or approve any CSV edit, database write, migration, importer run, or schema change.

## 2. Source references

Primary references for this decision plan:

- `docs/data/SportWarehouse_ProductDB.csv`
- `docs/operations/generated/2026-05-20-live-schema-verification-report.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`

## 3. Current evidence

Current evidence to carry forward:

- Both columns exist in live `item`: `CropAllowed` and `crop_allowed`.
- `CropAllowed` behaves like a CSV or source-style field.
- `crop_allowed` behaves like a runtime-style boolean field.
- Inspected values differ across rows.
- `CropAllowed` contains meaningful Yes/No-style values.
- `crop_allowed` appears defaulted to `1`.
- No drop, rename, overwrite, or automatic canonicalization is approved.

## 4. Interpretation

Likely interpretation at this stage:

- `CropAllowed` appears to preserve meaningful source or editorial semantics.
- `crop_allowed` appears to be a technical runtime boolean or default-driven field.
- Evidence is not sufficient to approve automatic conversion, overwrite, drop, or rename without a recorded governance decision.

## 5. Resolution options

Decision options to review:

1. Treat `CropAllowed` as source authoritative and import from CSV `CropAllowed`.
2. Treat `crop_allowed` as runtime boolean only after explicit Yes/No to `1`/`0` conversion.
3. Keep both fields temporarily and define explicit mapping rules for read and write behavior.
4. Exclude both fields from the first import allowlist until migration foundation steps are stable.

## 6. Recommended first-pass decision

Conservative first-pass recommendation:

- Do not drop or rename either column.
- Treat `CropAllowed` as source-authoritative for CSV comparison and governance review.
- Exclude `CropAllowed` and `crop_allowed` from the first executable import allowlist unless explicitly approved.
- Defer conversion into `crop_allowed` to a later compatibility step.
- Record any later conversion as a separate reviewed and approved task.

## 7. Proposed conversion rule for later review

Possible future conversion for discussion only:

- `Yes` -> `1`
- `No` -> `0`
- blank or null -> `NULL` or an explicitly approved default

This conversion rule is proposed only and is not approved for execution in this plan.

## 8. Impact on migration/import

Impact of this decision path:

- Import allowlist: first executable import should omit both fields unless explicit approval is recorded.
- Runtime field mapping: read behavior must be explicitly documented before runtime dependency changes.
- Dry-run update reports: reports should show these fields as deferred or excluded in first-pass scope.
- Protected data semantics: preserving `CropAllowed` source semantics avoids silent meaning loss.
- Future schema cleanup: drop or rename actions must be deferred to a later reviewed compatibility or cleanup stage.

## 9. Decision table

| Question | Recommended answer | Status | Blocks first import? | Notes |
| --- | --- | --- | --- | --- |
| Which column is source-authoritative? | `CropAllowed` | Recommended (pending formal approval) | Yes, until approved or deferred in allowlist | Preserves meaningful Yes/No-style source semantics. |
| Which column should runtime code read? | Keep current runtime behavior for now; document explicit read policy later | Open | No, if both fields are excluded from first import allowlist | Avoid runtime behavior changes during first migration foundation pass. |
| Should Yes/No be converted to 1/0? | Yes, but only in a later approved compatibility task | Proposed only | No, if conversion is deferred | Proposed rule: Yes->1, No->0, blank/null->NULL or approved default. |
| Should either field be included in first import allowlist? | No, exclude both `CropAllowed` and `crop_allowed` initially | Recommended (pending formal approval) | No, after exclusion is recorded | Conservative scope control for first executable import pass. |
| Should either column be dropped or renamed? | No | Recommended | No | Explicitly deferred until a separate reviewed schema cleanup decision. |

## 10. Non-goals

This plan explicitly does not include any of the following:

- no CSV edits
- no DB writes
- no migrations
- no ALTER TABLE execution
- no importer execution
- no repair SQL
- no generated report changes
- no PHP changes
- no image edits
- no Hero Manager or Hero Editor changes
