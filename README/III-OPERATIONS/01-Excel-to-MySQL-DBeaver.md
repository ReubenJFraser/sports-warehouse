# Excel → MySQL (via DBeaver) → Cloudways

## 1. Purpose of This README

This README documents the **actual, repeatable workflow** used to move structured data authored in **Excel** into a **MySQL database via DBeaver**, and then into the **Cloudways production environment**.

This document is intentionally **procedural**, not theoretical.

It exists to describe what is *actually done*, in a form that can be safely repeated, audited, and refined over time.

This is a **living README**. Some sections may initially be incomplete and will be tightened as the workflow is exercised.

---

## 2. Scope

### 2.1 What this README covers

- How Excel is used as a structured data authoring tool  
- How Excel data is prepared for database import  
- How Excel sheets are exported as CSV (UTF-8)  
- How CSV data is imported into MySQL using DBeaver  
- How the same process applies to:
  - local MySQL (Laragon)
  - Cloudways production MySQL (via SSH tunnel)

### 2.2 What this README does not cover (yet)

- Schema refactoring or redesign
- Admin UI tooling
- Automated migration frameworks (Flyway, Liquibase, etc.)
- Application-level validation logic

Those topics may be documented later if the workflow evolves.

---

## 3. Conceptual Roles

### 3.1 Excel

Excel is used as a **structured authoring and validation environment**, not as a database.

Its role is to:

- provide controlled data entry
- enforce consistency through:
  - dropdown lists
  - shared reference sheets
- support calculations and derived/helper values
- remain human-readable and editable

Excel is treated as an **editorial source**, not the runtime datastore.

---

### 3.2 MySQL (via DBeaver)

MySQL is the **authoritative runtime datastore** for the application.

DBeaver is used as:

- the primary database client
- the CSV import/export tool
- the inspection and verification interface
- the connection point for both local and production databases

---

### 3.3 Cloudways

Cloudways hosts the **production MySQL database**.

DBeaver connects to Cloudways using an **SSH tunnel**.

A confirmed and permanent prerequisite on Cloudways is that SSH TCP forwarding is enabled (`AllowTcpForwarding yes`). Cloudways has confirmed this setting persists across reboots, scaling, and server upgrades unless explicitly changed by the customer.

---

## 4. High-Level Workflow

1. Data is authored and validated in Excel  
2. Excel data is flattened and prepared for export  
3. Data is exported from Excel as CSV (UTF-8)  
4. CSV files are imported into MySQL using DBeaver  
5. Imported data is verified in MySQL  
6. The same workflow can target:
   - a local database
   - the Cloudways production database

---

## 5. Step 1 — Prepare Excel for Export

Before export, Excel data **must be flattened**.

Each Excel sheet intended for import must:

- represent **one logical database table**
- contain:
  - a single header row (column names)
  - one row per record
- avoid:
  - merged cells
  - blank columns within the table
  - totals or summary rows
  - notes or comments inside the data range

### 5.1 Recommended preparation practice

- Duplicate the working sheet  
- Paste **values only** into a clean sheet  
- Remove any non-data rows or columns  
- Name the sheet clearly, for example:
  - EXPORT_item
  - EXPORT_category

Dropdowns are acceptable. Only their **resolved values** are exported.

---

## 6. Step 2 — Export Excel to CSV (UTF-8)

For each logical table:

1. Open the prepared EXPORT_* sheet  
2. Use Save As  
3. Select the CSV UTF-8 (Comma delimited) format  
4. Name the file after the target table, for example:
   - item.csv
   - category.csv
   - taxonomy.csv

Rules:

- One CSV file per database table  
- Do not combine multiple tables into a single CSV  

---

## 7. Step 3 — Import CSV into MySQL Using DBeaver

### 7.1 Select the correct connection

In DBeaver, deliberately select the correct connection:

- local MySQL (Laragon), or  
- Cloudways MySQL (via SSH tunnel)

Importing into production is powerful and potentially destructive.

---

### 7.2 Run the import wizard

- In the Database Navigator:
  - expand the schema
  - right-click the target table
  - select Import Data
- Choose CSV as the data source
- Select the exported CSV file

---

### 7.3 Column mapping (critical)

During the column-mapping step:

- verify that each CSV column maps to the intended table column
- correct mismatches explicitly
- do not assume automatic mapping is correct

This step exists to prevent silent data corruption.

---

### 7.4 Import mode

Common import modes include:

- Insert — for initial or additive imports  
- Truncate before load — only when Excel is the complete source of truth  

Batch inserts may be enabled if available.

Execute the import only after reviewing all options.

---

## 8. Step 4 — Verify the Import

Verification is mandatory.

Typical checks include:

- row counts matching expectations  
- spot-checking a small number of rows  
- confirming category and relationship fields  
- checking for shifted columns or unexpected null values  

Verification should be performed immediately after each import.

---

## 9. Optional Pattern — Staging Tables

For repeated or higher-risk imports, a safer approach is to:

- import CSV data into a staging table (for example, item_import_stage)
- compare staging data with live tables
- apply controlled SQL operations to:
  - insert new rows
  - update changed rows

This pattern enables diff-based syncing and reduces the risk of accidental overwrites.

It can be formalised later if required.

---

## 10. Local vs Cloudways Imports

The procedure is identical in both environments.

### 10.1 Local database

- lower risk
- used for experimentation and validation
- preferred for first-pass imports

### 10.2 Cloudways production database

- authoritative production data
- verify the connection carefully
- double-check import options before execution

---

## 11. Known Gaps and Open Questions

The following items are intentionally left open and will be refined over time:

- the exact method used for the very first Excel → database import
- whether early imports used staging tables
- which calculated Excel fields should remain materialised in MySQL
- whether Excel remains part of the long-term workflow

---

## 12. Guiding Principle

This workflow prioritises:

- visibility  
- repeatability  
- reversibility  

Nothing is automated until it is fully understood.




