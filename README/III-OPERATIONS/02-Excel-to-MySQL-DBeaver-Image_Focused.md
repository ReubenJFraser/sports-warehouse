# Excel → Local MySQL Update (Images Focused)

## Purpose

This README documents the **exact procedure that was actually executed** to update the local MySQL `item` table from the authoritative Excel source, with emphasis on image-path corrections and removal of legacy/banner rows.

The intent is to record **what was done in practice**, so the same update can be repeated safely without redesigning workflows or re-deriving decisions.

This procedure assumes familiarity with the general Excel → MySQL → Cloudways workflow documented in 01-Excel-to-MySQL-DBeaver.md.

---

## Scope

### In scope
- Updating the local MySQL database (`sportswh`) from Excel
- Use of DBeaver for backup, deletion, and import
- Table-level operations on `item`
- Verification of results locally and in the admin UI

### Out of scope
- Cloudways / production sync
- Automation or pipeline redesign
- Review of Excel data correctness
- Refactoring related tables or constraints

---

## System Roles

### Excel
- Authoritative source of truth
- Sheet used: `SportWarehouse_ProductDB`
- Already corrected prior to this procedure
- Legacy/banner rows already removed
- Image paths already fixed

### Local MySQL (`sportswh`)
- Target system updated in this procedure
- Enforces foreign keys (including references from `hero_override`)
- Must be brought into exact alignment with Excel

### DBeaver
- Sole tool used to interact with the local database
- Used for inspection, backup, deletion, and CSV import
- No external scripts or automation involved

---

## Procedure

### Step 1 — Prepare CSV from Excel

- Open `SportWarehouse_ProductDB_Authoritative.xlsx`
- Select the sheet `SportWarehouse_ProductDB`
- Copy the sheet to a new workbook
- Save the new workbook as **CSV UTF-8 (Comma delimited)**
  - Filename used: `SportWarehouse_ProductDB_item.csv`
- Close the temporary workbook

No edits are made to the data at this stage.

---

### Step 2 — Create an in-database backup of `item`

Before modifying the table, a snapshot backup is created inside the database.

In DBeaver, connected to the local `sportswh` database, execute:

- `CREATE TABLE item_backup AS SELECT * FROM item`

Verification is performed by comparing row counts:

- `SELECT 'item_backup' AS table_name, COUNT(*) AS row_count FROM item_backup`
- `UNION ALL`
- `SELECT 'item' AS table_name, COUNT(*) AS row_count FROM item`

At this point, both `item` and `item_backup` contained **57 rows**, confirming the backup is complete.

---

### Step 3 — Clear the target table (foreign-key safe)

Because `item` is referenced by foreign keys, truncation is not permitted.

All rows are removed using a delete operation:

- `DELETE FROM item`

Verification:

- `SELECT COUNT(*) FROM item`

Result is **0**, confirming the table is empty and ready for import.

Foreign key constraints remain enabled throughout.

---

### Step 4 — Import CSV into `item` using DBeaver

The CSV is imported into the now-empty table.

Import is initiated by right-clicking the `item` table in DBeaver and selecting **Import Data**, then choosing the CSV source.

#### CSV settings used
- Encoding: UTF-8
- Column delimiter: comma
- Header position: top
- Quote character: double quote
- Escape character: backslash
- Empty strings mapped to NULL
- Default date/time parsing enabled

#### Database settings used
- Use transactions: Yes
- Transfer auto-generated columns: Yes
- Truncate before load: No
- Referential integrity: enabled

Column mapping is automatic and one-to-one.

The import is then executed.

---

### Step 5 — Verification

#### Database-level verification
- Row count after import:
  - `SELECT COUNT(*) FROM item`
- Result: **54 rows**

The reduction from 57 to 54 confirms that legacy/banner rows removed from Excel did not survive the update.

Spot checks confirm:
- Corrected image paths match Excel exactly
- Removed legacy items no longer exist in the table

#### Application-level verification
- Admin → Hero Manager is refreshed
- Images render correctly
- Placeholders appear only where expected
- Legacy/banner items do not appear

---

## Outcome

- Local `item` table now matches the authoritative Excel sheet
- Image-path corrections are applied
- Legacy/banner rows are removed
- Previous state is preserved in `item_backup`

The local update objective is complete.

---

## Known Gaps and Open Questions

- Cloudways / production sync is not addressed here
- No formal diff tooling is documented
- Lifecycle and eventual removal of `item_backup` is not yet defined

These are intentionally deferred.

---

## Guiding Principles

- Excel is the source of truth
- Local updates must be observable and reversible
- Foreign key integrity is never disabled
- Execution precedes documentation
- Only actions that actually occurred are recorded
