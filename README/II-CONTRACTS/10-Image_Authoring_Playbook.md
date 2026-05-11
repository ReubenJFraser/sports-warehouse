# Image Authoring Playbook — Editorial Workflow

## Purpose

This README defines the **authoritative editorial workflow** for authoring and maintaining the `images` column in `SportWarehouse_ProductDB`.

Its purpose is to ensure that:

- image paths are authored deliberately, not inferred
- folder-level image-set roots are used consistently
- Power Query remains a validator, not an editor
- future contributors can follow the same process without rediscovering it

This document exists to prevent accidental reintroduction of filename-level paths, hidden normalization logic, or contract drift.

---

## Scope

This README covers:

- when the `images` column must be edited
- how image-set paths are authored
- validation checks that must pass before changes are considered complete
- the role of the `Images_Normalized_by_Product` query as an audit tool

This README does **not** cover:

- how images are rendered in the frontend
- how images are ingested into MySQL
- how files are uploaded or deployed to servers
- how image filenames themselves are chosen

Those concerns are governed by separate contracts.

---

## Conceptual Roles

### Excel (SportWarehouse_ProductDB)

- Editorial source of truth
- Human-authored
- Explicit and inspectable
- Owns semantic intent

### Power Query

- Read-only normalizer and validator
- Used to reveal inconsistencies
- Must not silently repair or author data
- Safe to refresh at any time

---

## When the `images` Column Must Be Edited

The `images` column must be reviewed and edited in the following cases:

- A **new product** is added
- A **new colourway** introduces a new image-set folder
- A **new variant** requires a distinct image-set
- A **new collection** reorganizes image folders

If none of the above apply, the `images` column should not change.

---

## How to Author the `images` Column

The `images` column is authored directly in Excel.

Follow these rules exactly:

- Start from the **filesystem folder structure**
- Use **folder-level image-set roots only**
- Do **not** include filenames
- Do **not** include file extensions
- Use **semicolon-delimited paths** only when multiple image-set roots are genuinely required for a single product

Examples (illustrative only):

images/brands/adidas/kids/boys/marvel-spider_man/tracksuit/
images/brands/reebok/men/training_shoes/nano_X3/black_court_brown/;images/brands/reebok/men/training_shoes/nano_X3/core_black-neon_cherry/

---

## Validation Checklist (Must All Pass)

Before considering an edit complete, confirm the following:

- The path begins at a **canonical brand root**
- Gender or age segment is present where required by the Image Path Contract
- The base product category appears **exactly once**
- No placeholder or catch-all brand (such as `other`) is used
- All referenced folders **exist on disk** or will be created to match the editorial path

If any item fails, fix the Excel value before proceeding.

---

## Audit Step (Power Query)

After editing the `images` column:

1. Save the workbook
2. Refresh all queries
3. Open `Images_Normalized_by_Product`
4. Confirm:
   - one row per `db_itemId`
   - no filename remnants
   - no unexpected duplication
   - no structural anomalies

If anomalies appear, **fix Excel**, not Power Query.

---

## Known Gaps and Non-Goals

This playbook does not currently define:

- automated enforcement rules
- CI-style validation
- blocking ingestion on path violations

These may be introduced later, but are intentionally excluded from the current workflow.

---

## Guiding Principles

- Excel remains the editorial authority
- Power Query validates, it does not decide
- Image paths encode meaning, not convenience
- Silent normalization is considered a defect
- Boring, repeatable workflows are preferred over clever ones
