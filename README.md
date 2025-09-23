# Sports Warehouse – Category Sync Runbook (Agent‑Safe)

This repository contains the **safe, automatable workflow** to keep `sportswh.item.categoryId` in sync with `sportswh.item_import.categoryId` using strict matching rules and **hard safety rails**. It is designed so a VS Code agent (ChatGPT) can run it repeatably without risking bulk data loss.

---

## ✅ What this does

- **Joins** `item` ↔ `item_import` by normalized brand + name.
- **Normalization** for matching:
  - `LOWER(TRIM(...))`
  - Remove non‑alphanumerics: `REGEXP_REPLACE(name,'[^a-z0-9]+','')`
  - Remove audience suffixes: `mens | womens | girls | boys`
  - Treat the word **`scoop`** as ignorable for long‑sleeve “scoop” styles.
- **Matching logic** (any one is enough):
  1) Exact normalized match  
  2) Match after removing `scoop` from the import side  
  3) One normalized name is a **substring** of the other (to catch e.g. `"Second Left Seamless: Mini Biker Shorts"` vs `"Mini Biker Shorts"`)
- If multiple imports match an item, we pick the **shortest import name** (closest match).
- **Updates only** rows where `item.categoryId` differs from the picked import `categoryId`.
- Includes **dry‑run**, **limited batch**, **transaction**, and **backup/rollback** steps.

> **Requires MySQL 8+** (uses `REGEXP_REPLACE` and window functions like `ROW_NUMBER()`).
>
> Database name used below: **`sportswh`**.

---

## 📁 Repo structure (proposed)

```
.
├── README.md
├── .env.example
├── site-structure.txt
├── scripts/
│   ├── run-sql.sh
│   ├── run-sql.ps1
│   └── sql/
│       ├── 00_preview_diffs.sql
│       ├── 01_apply_update.sql
│       ├── 02_backup_targets.sql
│       ├── 03_rollback_from_backup.sql
│       └── 04_list_unmatched_items.sql
└── .vscode/
    └── tasks.json
```

> You can copy the file templates from the **Appendix: File templates** section at the end of this README.

---

## 🔧 Prerequisites

- MySQL **8.0+**
- MySQL client (`mysql`) available on PATH
- VS Code (optional, but recommended for Tasks)
- Fill out a project‑local **`.env`** file (see `.env.example`):
  - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`

---

## 🛡️ Safety rails (must keep)

- **Always run a dry‑run preview** (`00_preview_diffs.sql`) before apply.
- **Back up** the exact target rows (`02_backup_targets.sql`) *within the same transaction* before updating.
- **Limit the batch size** in the apply script (default 2,000 rows). Run multiple batches if needed.
- Use a **transaction**; **rollback** if affected rows are unexpectedly high.
- **Never** remove the `WHERE COALESCE(i.categoryId,-1) <> COALESCE(p.import_cat,-1)` guard.
- Only update with the **picked** import match (`ROW_NUMBER() = 1`).

---

## ▶️ Typical run (human or agent)

1) **Dry run**: count and preview the rows that would change.  
   `scripts/sql/00_preview_diffs.sql`

2) **Backup** the would‑change rows (creates `sportswh.tmp_item_cat_backup`).  
   `scripts/sql/02_backup_targets.sql`

3) **Apply** in a transaction with batch limit.  
   `scripts/sql/01_apply_update.sql`

4) **Verify** zero diffs remain.  
   Re‑run `00_preview_diffs.sql` (should return no rows).

5) If anything looks wrong, **rollback** from the temp backup.  
   `scripts/sql/03_rollback_from_backup.sql`

6) Investigate any **unmatched items** (optional).  
   `scripts/sql/04_list_unmatched_items.sql`

> In VS Code, use the provided **Tasks** to run these in order.

---

## 🧪 Matching logic (shared CTE)

All core scripts build matches the same way. The key CTEs are:

```sql
WITH
i_norm AS (
  SELECT
    i.itemId, i.itemName, i.categoryId,
    LOWER(TRIM(i.brand)) AS brand,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(i.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS i_n
  FROM sportswh.item i
),
ii_norm AS (
  SELECT
    LOWER(TRIM(ii.brand)) AS brand,
    ii.itemName, ii.categoryId,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(ii.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS ii_n
  FROM sportswh.item_import ii
),
ranked AS (
  SELECT
    i.itemId,
    ii.itemName  AS import_name,
    ii.categoryId AS import_cat,
    ROW_NUMBER() OVER (PARTITION BY i.itemId ORDER BY LENGTH(ii.itemName)) AS rn
  FROM i_norm i
  JOIN ii_norm ii
    ON ii.brand = i.brand
   AND (
        ii.ii_n = i.i_n
     OR REPLACE(ii.ii_n,'scoop','') = i.i_n
     OR i.i_n LIKE CONCAT('%', ii.ii_n, '%')
     OR ii.ii_n LIKE CONCAT('%', i.i_n, '%')
   )
),
pick AS (
  SELECT * FROM ranked WHERE rn = 1
)
```

All diff/backup/apply/rollback scripts rely on the `pick` CTE above.

---

## 🧭 VS Code Tasks

We include **`.vscode/tasks.json`** so a human or agent can run the workflow with Command Palette → “Run Task”. Tasks call the cross‑platform runners (`run-sql.sh` / `run-sql.ps1`) with one of the SQL files.

**Tasks available**

- **DB: Preview diffs** → `00_preview_diffs.sql`
- **DB: Backup targets** → `02_backup_targets.sql`
- **DB: Apply update (batch)** → `01_apply_update.sql`
- **DB: Rollback from backup** → `03_rollback_from_backup.sql`
- **DB: List unmatched** → `04_list_unmatched_items.sql`

> The apply task is **non‑interactive** but includes a **batch LIMIT** and a **row‑count assertion**. Increase `@BATCH_LIMIT` if needed and re‑apply until no diffs remain.

---

## 🔁 Hand‑off to VS Code Agent (ChatGPT)

- Agent must run **Preview → Backup → Apply (batch) → Verify** in that order.
- If preview shows **0** diffs, **do nothing**.
- If preview > 0:
  - Ensure `tmp_item_cat_backup` is (re)created in the same session before updating.
  - Apply in batches (default 2,000), **re‑preview** after each batch.
- Agent must not change **matching rules** or **guards** without human approval.
- Agent must post the **affected row count** and a **sample of updates** after each batch.

---

## 🧷 Rollback strategy

- On every apply, we **stage** the affected rows into `sportswh.tmp_item_cat_backup (itemId, old_categoryId, new_categoryId, backed_up_at)` inside the same session/transaction before updating.
- `03_rollback_from_backup.sql` restores the prior `categoryId` from that table.
- You can snapshot to a dated table if you want a longer retention (manual).

---

## 🧩 Appendix: File templates

> Copy these into your repo. Paths assume the structure listed above.

### `.env.example`

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sportswh
DB_USER=your_user
DB_PASSWORD=your_password
```

---

### `scripts/run-sql.sh` (Unix/macOS)

```bash
#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 path/to/script.sql"
  exit 1
fi

SCRIPT="$1"

# Load .env key=val pairs
if [[ -f ".env" ]]; then
  export $(grep -v '^[# ]' .env | xargs -d '\n')
else
  echo "WARNING: .env not found; relying on environment variables"
fi

: "${DB_HOST:?missing}"
: "${DB_PORT:?missing}"
: "${DB_NAME:?missing}"
: "${DB_USER:?missing}"
: "${DB_PASSWORD:?missing}"

mysql --protocol=TCP \
  -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" \
  --default-character-set=utf8mb4 \
  < "$SCRIPT"
```

> Make executable: `chmod +x scripts/run-sql.sh`

---

### `scripts/run-sql.ps1` (Windows PowerShell)

```powershell
param([Parameter(Mandatory=$true)][string]$ScriptPath)

# Load .env into $env:*
$envPath = ".env"
if (Test-Path $envPath) {
  Get-Content $envPath | Where-Object {$_ -and $_ -notmatch '^\s*#'} | ForEach-Object {
    $k,$v = $_.Split('=',2)
    if ($k) { Set-Item -Path Env:$k.Trim() -Value $v.Trim() }
  }
} else {
  Write-Host "WARNING: .env not found; relying on environment variables"
}

@('DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD') | ForEach-Object {
  if (-not $env:$_) { throw "Missing environment variable: $_" }
}

# Assumes mysql is on PATH
cmd /c "mysql --protocol=TCP -h $env:DB_HOST -P $env:DB_PORT -u $env:DB_USER -p$env:DB_PASSWORD --default-character-set=utf8mb4 < `"$ScriptPath`""
```

---

### `.vscode/tasks.json`

```json
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "DB: Preview diffs",
      "type": "shell",
      "command": "${env:ComSpec}",
      "windows": {
        "command": "powershell",
        "args": ["-ExecutionPolicy","Bypass","-File","scripts/run-sql.ps1","scripts/sql/00_preview_diffs.sql"]
      },
      "linux": {
        "command": "bash",
        "args": ["scripts/run-sql.sh", "scripts/sql/00_preview_diffs.sql"]
      },
      "osx": {
        "command": "bash",
        "args": ["scripts/run-sql.sh", "scripts/sql/00_preview_diffs.sql"]
      },
      "problemMatcher": []
    },
    {
      "label": "DB: Backup targets",
      "type": "shell",
      "windows": { "command": "powershell", "args": ["-ExecutionPolicy","Bypass","-File","scripts/run-sql.ps1","scripts/sql/02_backup_targets.sql"] },
      "linux":   { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/02_backup_targets.sql"] },
      "osx":     { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/02_backup_targets.sql"] },
      "problemMatcher": []
    },
    {
      "label": "DB: Apply update (batch)",
      "type": "shell",
      "windows": { "command": "powershell", "args": ["-ExecutionPolicy","Bypass","-File","scripts/run-sql.ps1","scripts/sql/01_apply_update.sql"] },
      "linux":   { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/01_apply_update.sql"] },
      "osx":     { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/01_apply_update.sql"] },
      "problemMatcher": []
    },
    {
      "label": "DB: Rollback from backup",
      "type": "shell",
      "windows": { "command": "powershell", "args": ["-ExecutionPolicy","Bypass","-File","scripts/run-sql.ps1","scripts/sql/03_rollback_from_backup.sql"] },
      "linux":   { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/03_rollback_from_backup.sql"] },
      "osx":     { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/03_rollback_from_backup.sql"] },
      "problemMatcher": []
    },
    {
      "label": "DB: List unmatched",
      "type": "shell",
      "windows": { "command": "powershell", "args": ["-ExecutionPolicy","Bypass","-File","scripts/run-sql.ps1","scripts/sql/04_list_unmatched_items.sql"] },
      "linux":   { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/04_list_unmatched_items.sql"] },
      "osx":     { "command": "bash", "args": ["scripts/run-sql.sh", "scripts/sql/04_list_unmatched_items.sql"] },
      "problemMatcher": []
    }
  ]
}
```

---

### `scripts/sql/00_preview_diffs.sql`

```sql
-- Preview rows that WOULD change (no data modifications)

WITH
i_norm AS (
  SELECT
    i.itemId, i.itemName, i.categoryId,
    LOWER(TRIM(i.brand)) AS brand,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(i.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS i_n
  FROM sportswh.item i
),
ii_norm AS (
  SELECT
    LOWER(TRIM(ii.brand)) AS brand,
    ii.itemName, ii.categoryId,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(ii.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS ii_n
  FROM sportswh.item_import ii
),
ranked AS (
  SELECT
    i.itemId,
    ii.itemName  AS import_name,
    ii.categoryId AS import_cat,
    ROW_NUMBER() OVER (PARTITION BY i.itemId ORDER BY LENGTH(ii.itemName)) AS rn
  FROM i_norm i
  JOIN ii_norm ii
    ON ii.brand = i.brand
   AND (
        ii.ii_n = i.i_n
     OR REPLACE(ii.ii_n,'scoop','') = i.i_n
     OR i.i_n LIKE CONCAT('%', ii.ii_n, '%')
     OR ii.ii_n LIKE CONCAT('%', i.i_n, '%')
   )
),
pick AS ( SELECT * FROM ranked WHERE rn = 1 )
SELECT
  i.itemId, i.itemName,
  i.categoryId AS current_cat,
  p.import_cat AS new_cat,
  p.import_name
FROM sportswh.item i
JOIN pick p ON p.itemId = i.itemId
WHERE COALESCE(i.categoryId,-1) <> COALESCE(p.import_cat,-1)
ORDER BY i.itemId;
```

---

### `scripts/sql/02_backup_targets.sql`

```sql
-- Back up ONLY rows that would change, then show how many were staged

START TRANSACTION;

WITH
i_norm AS (
  SELECT
    i.itemId, i.itemName, i.categoryId,
    LOWER(TRIM(i.brand)) AS brand,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(i.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS i_n
  FROM sportswh.item i
),
ii_norm AS (
  SELECT
    LOWER(TRIM(ii.brand)) AS brand,
    ii.itemName, ii.categoryId,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(ii.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS ii_n
  FROM sportswh.item_import ii
),
ranked AS (
  SELECT
    i.itemId,
    ii.itemName  AS import_name,
    ii.categoryId AS import_cat,
    ROW_NUMBER() OVER (PARTITION BY i.itemId ORDER BY LENGTH(ii.itemName)) AS rn
  FROM i_norm i
  JOIN ii_norm ii
    ON ii.brand = i.brand
   AND (
        ii.ii_n = i.i_n
     OR REPLACE(ii.ii_n,'scoop','') = i.i_n
     OR i.i_n LIKE CONCAT('%', ii.ii_n, '%')
     OR ii.ii_n LIKE CONCAT('%', i.i_n, '%')
   )
),
pick AS ( SELECT * FROM ranked WHERE rn = 1 )
,
targets AS (
  SELECT i.itemId, i.categoryId AS old_cat, p.import_cat AS new_cat
  FROM sportswh.item i
  JOIN pick p ON p.itemId = i.itemId
  WHERE COALESCE(i.categoryId,-1) <> COALESCE(p.import_cat,-1)
)
-- Create/replace a temp backup table with just the targets
DROP TABLE IF EXISTS sportswh.tmp_item_cat_backup;
CREATE TABLE sportswh.tmp_item_cat_backup (
  itemId INT PRIMARY KEY,
  old_categoryId INT,
  new_categoryId INT,
  backed_up_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sportswh.tmp_item_cat_backup (itemId, old_categoryId, new_categoryId)
SELECT itemId, old_cat, new_cat FROM targets;

-- Show staged count
SELECT COUNT(*) AS staged_rows FROM sportswh.tmp_item_cat_backup;

COMMIT;
```

---

### `scripts/sql/01_apply_update.sql`

```sql
-- Apply in a transaction with batch LIMIT and sanity checks.
-- Assumes tmp_item_cat_backup exists from the backup step.

SET @BATCH_LIMIT := 2000;     -- adjust as needed
SET @MAX_EXPECTED := 50000;   -- fail-safe: abort if larger than this

START TRANSACTION;

-- How many rows are still pending change?
WITH
i_norm AS (
  SELECT
    i.itemId, i.itemName, i.categoryId,
    LOWER(TRIM(i.brand)) AS brand,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(i.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS i_n
  FROM sportswh.item i
),
ii_norm AS (
  SELECT
    LOWER(TRIM(ii.brand)) AS brand,
    ii.itemName, ii.categoryId,
    REPLACE(REPLACE(REPLACE(REPLACE(
      LOWER(REGEXP_REPLACE(TRIM(ii.itemName),'[^a-z0-9]+','')),
      'mens',''),'womens',''),'girls',''),'boys','') AS ii_n
  FROM sportswh.item_import ii
),
ranked AS (
  SELECT
    i.itemId,
    ii.itemName  AS import_name,
    ii.categoryId AS import_cat,
    ROW_NUMBER() OVER (PARTITION BY i.itemId ORDER BY LENGTH(ii.itemName)) AS rn
  FROM i_norm i
  JOIN ii_norm ii
    ON ii.brand = i.brand
   AND (
        ii.ii_n = i.i_n
     OR REPLACE(ii.ii_n,'scoop','') = i.i_n
     OR i.i_n LIKE CONCAT('%', ii.ii_n, '%')
     OR ii.ii_n LIKE CONCAT('%', i.i_n, '%')
   )
),
pick AS ( SELECT * FROM ranked WHERE rn = 1 )
SELECT COUNT(*) INTO @pending
FROM sportswh.item i
JOIN pick p ON p.itemId = i.itemId
WHERE COALESCE(i.categoryId,-1) <> COALESCE(p.import_cat,-1);

-- Abort if suspiciously large
IF @pending > @MAX_EXPECTED THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Aborting: pending updates exceed @MAX_EXPECTED';
END IF;

-- Apply a limited batch
UPDATE sportswh.item i
JOIN pick p ON p.itemId = i.itemId
LEFT JOIN sportswh.tmp_item_cat_backup b ON b.itemId = i.itemId
SET i.categoryId = p.import_cat
WHERE COALESCE(i.categoryId,-1) <> COALESCE(p.import_cat,-1)
ORDER BY i.itemId
LIMIT @BATCH_LIMIT;

-- Report how many were affected in this batch
SELECT ROW_COUNT() AS batch_applied;

COMMIT;
```

---

### `scripts/sql/03_rollback_from_backup.sql`

```sql
-- Restore from last tmp backup

START TRANSACTION;

UPDATE sportswh.item i
JOIN sportswh.tmp_item_cat_backup b ON b.itemId = i.itemId
SET i.categoryId = b.old_categoryId;

SELECT ROW_COUNT() AS rolled_back;

COMMIT;
```

---

### `scripts/sql/04_list_unmatched_items.sql`

```sql
-- Items that do not currently find any import match

SELECT i.itemId, i.brand, i.itemName
FROM sportswh.item i
LEFT JOIN (
  WITH
  i_norm AS (
    SELECT
      i.itemId, i.itemName,
      LOWER(TRIM(i.brand)) AS brand,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(i.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS i_n
    FROM sportswh.item i
  ),
  ii_norm AS (
    SELECT
      LOWER(TRIM(ii.brand)) AS brand,
      ii.itemName, ii.categoryId,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(ii.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS ii_n
    FROM sportswh.item_import ii
  )
  SELECT DISTINCT i.itemId
  FROM i_norm i
  JOIN ii_norm ii
    ON ii.brand = i.brand
   AND (
        ii.ii_n = i.i_n
     OR REPLACE(ii.ii_n,'scoop','') = i.i_n
     OR i.i_n LIKE CONCAT('%', ii.ii_n, '%')
     OR ii.ii_n LIKE CONCAT('%', i.i_n, '%')
   )
) m ON m.itemId = i.itemId
WHERE m.itemId IS NULL
ORDER BY i.itemId;
```

---

## 🗂️ Updating `site-structure.txt`

Once you add the files above, append entries for:

```
README.md
.env.example
.vscode/tasks.json
scripts/run-sql.sh
scripts/run-sql.ps1
scripts/sql/00_preview_diffs.sql
scripts/sql/01_apply_update.sql
scripts/sql/02_backup_targets.sql
scripts/sql/03_rollback_from_backup.sql
scripts/sql/04_list_unmatched_items.sql
```

This helps the VS Code agent “see” the repository layout.

---

## 📝 Notes & pitfalls

- **Don’t use `QUALIFY`** (BigQuery). We already filter `ROW_NUMBER()` in the outer query with `WHERE rn = 1` for MySQL.
- Keep **consistent normalization** across all scripts; diverging logic will create phantom diffs.
- If you see pending diffs but your earlier run updated, confirm you are connected to the **same database & schema** the data comes from.
- If your MySQL client enforces `SQL_SAFE_UPDATES`, our apply uses a primary‑key join (`i.itemId`) which satisfies the safeguard. If your client still blocks, temporarily `SET SQL_SAFE_UPDATES=0` inside the transaction, then restore it.

---

## 📣 License / Ownership

Internal operational runbook. Distribute within your organization only unless cleared.

Blank






