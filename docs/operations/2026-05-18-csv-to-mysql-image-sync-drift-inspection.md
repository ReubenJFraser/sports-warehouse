# CSV → MySQL Product Image Sync Drift Inspection (Planning Only)

Date: 2026-05-18

## Scope and safeguards

This document is inspection/planning only.

- No CSV import.
- No MySQL updates.
- No DBeaver repair SQL generation.
- No image file edits.
- No Hero Manager/Hero Editor behavior changes.
- No automatic importer implementation.

## Current blocker

The expected comparison source file is not present in this repository checkout:

- Expected path: `docs/data/SportWarehouse_ProductDB.csv`
- Status: missing

Because the CSV is missing, a true drift comparison against local MySQL cannot yet be executed.

## Pre-repair drift inspection plan

Once `docs/data/SportWarehouse_ProductDB.csv` is present, run the following **read-only** steps in DBeaver/MySQL.

### 1) Confirm table shape and row count (read-only)

```sql
DESCRIBE item;
SELECT COUNT(*) AS mysql_row_count FROM item;
```

Purpose: verify target schema and baseline row count before comparison.

### 2) Build a temporary read-only CSV staging view (outside MySQL)

Use a local script/notebook to parse the CSV and normalize these fields for comparison:

- `itemId` (comparison key)
- `images` (raw)
- normalized image list (trimmed, de-duplicated, order-preserving)

No DB writes; this is file parsing only.

### 3) Compare key presence drift

Compute:

- Present in CSV but missing in MySQL (`new_in_csv`)
- Present in MySQL but missing in CSV (`stale_in_mysql`)
- Present in both (`shared`)

### 4) Compare image-path drift for shared rows

For shared `itemId` rows, compare:

- exact `images` string mismatch
- normalized image-set mismatch
- first image (hero fallback path) mismatch

Bucket severity:

- `HIGH`: first image missing/moved or empty where CSV is non-empty
- `MEDIUM`: set mismatch (added/removed images)
- `LOW`: ordering-only differences

### 5) Produce a repair-input artifact (no SQL yet)

Generate a **human-review CSV/Markdown report** only, including:

- `itemId`
- `drift_type` (`missing_in_mysql`, `stale_in_mysql`, `images_mismatch`)
- `mysql_images`
- `csv_images`
- `severity`
- `notes`

This report becomes the basis for a later, separate DBeaver repair SQL drafting step.

## Read-only DBeaver helper queries for spot checks

Use these only for manual verification of candidate rows after drift is computed externally:

```sql
SELECT itemId, itemName, images
FROM item
WHERE itemId IN (/* paste sampled ids from report */)
ORDER BY itemId;
```

```sql
SELECT itemId, itemName, images
FROM item
WHERE images IS NULL OR TRIM(images) = '';
```

## Acceptance criteria for this inspection stage

- CSV file is present and readable from repository path.
- Drift categories are enumerated with counts.
- Candidate mismatch rows are listed for review.
- No data mutation has occurred.
- No repair SQL has been generated yet.
