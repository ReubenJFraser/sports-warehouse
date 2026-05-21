# 2026-05-21 model_id Duplicate Resolution Plan

Date: 2026-05-21
Status: Documentation-only planning record

## 1. Purpose

This document resolves and frames the duplicate `model_id` blocker before any executable CSV-to-MySQL migration or import work.

This plan is documentation only. It does not execute, authorize, or approve CSV edits, database writes, migrations, or importer actions.

## 2. Source references

Primary references used for this duplicate analysis:

- `docs/data/SportWarehouse_ProductDB.csv`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-CSV-MySQL-Migration-Governance-Decision-Record.md`
- `README/V-AUDIT/POST-AUDIT/2026-05-21-Illustrative-MySQL-Migration-SQL-Plan-Not-Executable.md`

## 3. Duplicate evidence

CSV evidence confirms:

- Total rows: 120.
- `model_id` populated for all rows.
- Duplicate `model_id` value: `nike_female_leggings` appears 2 times.

The two duplicate rows are shown below.

### Row A

- row number: 32
- brand: blank
- gender: Female
- itemName: Zenvy: Flared Leggings
- itemName_fully_derived: Yes
- model_id: nike_female_leggings
- product_domain: blank
- collection: blank
- model_family: blank
- variant: blank
- usage_category: blank
- usage_subtype: blank
- categoryName: Pants
- parentCategory: blank
- images: `images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/`
- thumbnails_json: `images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/01.mp4;images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/02.png;images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/03.png;images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/04.png;images/brands/nike/women/zenvy/leggings-high-waisted-flared/green/05.png`
- external_item_id: nike-zenvy-flared-leggings
- db_itemId: 27

### Row B

- row number: 33
- brand: blank
- gender: Female
- itemName: Zenvy: High-Waisted Leggings
- itemName_fully_derived: Yes
- model_id: nike_female_leggings
- product_domain: blank
- collection: blank
- model_family: blank
- variant: blank
- usage_category: blank
- usage_subtype: blank
- categoryName: Pants
- parentCategory: blank
- images: `images/brands/nike/women/zenvy/leggings-high_waisted-full_length/purple/`
- thumbnails_json: `images/brands/nike/women/zenvy/leggings-high_waisted-full_length/purple/01.png;images/brands/nike/women/zenvy/leggings-high_waisted-full_length/purple/02.png`
- external_item_id: nike-zenvy-high-waisted-leggings
- db_itemId: 28

## 4. Likely cause of duplication

The duplicate does not look like a literal duplicate row. It appears to be two distinct products or at least two distinct variants within the same Nike Zenvy leggings family:

- Different `itemName` values (Flared vs High-Waisted).
- Different image paths and media sets.
- Different `external_item_id` values.
- Different existing `db_itemId` values.

Most likely cause: insufficient `model_id` formula specificity for this product family. The current value (`nike_female_leggings`) is too broad and does not include differentiating terms such as model family and shape or variant subtype.

A secondary contributing factor is missing normalization columns (`collection`, `model_family`, `variant`, `usage_category`, `usage_subtype`) being blank for both rows, which reduces formula inputs.

## 5. Resolution options

Option 1: Update one `model_id` value to include variant specificity.
- Example strategy: keep one existing value and make the second explicitly specific.
- Pros: immediate uniqueness; simple governance unblock.
- Cons: can be inconsistent if formula policy is not updated.

Option 2: Revise the `model_id` formula rule for this product family and regenerate both values in source governance.
- Example strategy: include a normalized subtype token from `itemName` or `external_item_id` for leggings.
- Pros: deterministic and repeatable for future rows.
- Cons: requires clear formula policy update before execution.

Option 3: Approve temporary non-unique `model_id` policy.
- Pros: no immediate source correction.
- Cons: weakens import matching confidence and delays `UNIQUE(model_id)`.

Option 4: Defer `UNIQUE(model_id)` until manual CSV correction is approved and recorded.
- Pros: lowest immediate operational risk.
- Cons: blocker remains open until correction is done.

## 6. Recommended resolution

Recommended option: Option 2 with explicit source-level correction record before executable migration work.

Recommended policy:

1. Revise the `model_id` formula to include leggings subtype specificity derived from stable descriptors (`itemName` or `external_item_id`).
2. Assign unique values for both duplicate rows in the source governance correction step.
3. Keep `UNIQUE(model_id)` deferred until corrected values are present and revalidated.

Proposed replacement values for planning discussion:

- Row 32 proposed `model_id`: `nike_female_zenvy_flared_leggings`
- Row 33 proposed `model_id`: `nike_female_zenvy_high_waisted_leggings`

These proposed values are documentation only and are not applied in this task.

## 7. Migration impact

Impact on `UNIQUE(model_id)`:
- Constraint can only be enabled after duplicate resolution is reflected in source data and staging validation confirms zero duplicates.

Impact on staging validation:
- Duplicate check must remain a required gate in pre-import validation.
- Validation should fail if any duplicate `model_id` remains unresolved unless a formal waiver is approved.

Impact on 66 new-row insert candidates:
- Deterministic unique `model_id` values improve insert matching and reduce accidental merges when backfilling identifiers.

Impact on `db_itemId` backfill planning:
- Unique `model_id` improves safe reconciliation when paired with `external_item_id` and other stable fields.
- Duplicate `model_id` increases ambiguity risk for mapping new rows.

Impact on import matching confidence:
- Resolving this duplicate increases confidence in record identity, idempotent imports, and post-import auditability.

## 8. Non-goals

This document explicitly does not perform any of the following:

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
