# Model ID vs `external_item_id` Historical Origin Audit (Documentation-Only)

Date: 2026-05-27  
Scope: Documentation and repository history audit only (no MySQL changes, no ProductDB edits, no runtime/admin/frontend/import code changes, no SQL regeneration).

## Why this revision exists

PR #197 expanded current-tree and Git-history searching, but the historical-origin question remains unresolved because the currently available repository history begins too late.

This revision explicitly corrects that limitation:

1. The audit only found earliest references within the currently available Git history window.
2. `external_item_id` is known to have existed before early February 2026.
3. Therefore, the current repository history available to this audit is insufficient to explain why `external_item_id` was originally created.

---

## Commands executed (record)

### Current-tree search
- `rg -n "external_item_id|model_id|db_itemId|itemId" README docs tools scripts db .`
- `rg -n "external item|external id|external_item|supplier|source id|import id|staging|ProductDB|Excel" README docs tools scripts db .`

### Git history search (available repository history only)
- `git log --all --oneline --decorate -- README docs tools scripts db`
- `git log --all -S "external_item_id" -- README docs tools scripts db`
- `git log --all -S "model_id" -- README docs tools scripts db`
- `git log --all -G "external_item_id|model_id|db_itemId|itemId" -- README docs tools scripts db`
- `git log --all --follow -- tools/importers/import_external_item_id.php`
- `git log --all --follow -- tools/importers/diagnostic_external_item_id.php`
- `git log --all --follow -- README/II-CONTRACTS/01-Excel-Database_Contract.md`

### Supporting chronology checks
- `git log --all --reverse -S "external_item_id" --format='%H %ad %s' --date=short -- README docs tools scripts db | head -n 20`
- `git log --all --reverse -S "model_id" --format='%H %ad %s' --date=short -- README docs tools scripts db | head -n 20`
- `git log --all --reverse -G "external_item_id|model_id|db_itemId|itemId" --format='%H %ad %s' --date=short -- README docs tools scripts db | head -n 30`
- `git log --all --format='%H %ad %s' --date=short --reverse -- tools/importers/import_external_item_id.php`
- `git log --all --format='%H %ad %s' --date=short --reverse -- tools/importers/diagnostic_external_item_id.php`
- `git log --all --format='%H %ad %s' --date=short --reverse -- README/II-CONTRACTS/01-Excel-Database_Contract.md`
- `git log --all --format='%H %ad %s' --date=short --reverse -- db/sportswh_dump.sql`

---

## 1) Current operational mapping (what is true now)

For current local MySQL operations in this repository, ProductDB/governance `model_id` is mapped to MySQL `item.external_item_id` in planning and migration artifacts.

This is a current compatibility mapping in present operations, not proof of original naming intent.

---

## 2) Historical-origin findings from available repository history

### Earliest discovered references in currently available history

- Earliest discoverable commit touching relevant tree paths and containing foundational docs/files in this repository snapshot:  
  `980f39b5604ee331174a678b120252bf0af97883` (2026-05-21, merge PR #93).

- Earliest discoverable `external_item_id` hit from `git log -S "external_item_id"` in scoped paths:  
  `acfd1bcb01d34e9b99e2d342909ae97a78a9c705` (2026-05-21).

- Earliest discoverable `model_id` hit from `git log -S "model_id"` in scoped paths:  
  `acfd1bcb01d34e9b99e2d342909ae97a78a9c705` (2026-05-21).

- Earliest discoverable `external_item_id`-specific SQL/schema planning evidence in generated ops docs:  
  `9041b1695629cde75f36d6ea2bfe937cfceaef46` (2026-05-24; widen `item.external_item_id` for `model_id`).

### What this does and does not answer

Within the current repository history available to this audit, earliest scoped hits for `external_item_id` and `model_id` appear in the same 2026-05-21 window.

This does **not** answer the true origin question, because `external_item_id` is known to have existed before early February 2026.

The current repository history available to this audit begins too late to answer the origin question.

---


## 3) Importer evidence from local source search

### Command executed
- `Select-String -Path ".\tools\importers\*.php" -Pattern "external_item_id","external item","external id","source id","import id","staging" -SimpleMatch -Context 8,8`

### Evidence found
- `tools/importers/diagnostic_external_item_id.php` requires Excel headers including both `db_itemId` and `external_item_id`.
- `tools/importers/import_external_item_id.php` requires Excel headers including both `db_itemId` and `external_item_id`.
- `tools/importers/import_external_item_id.php` resolves both the `db_itemId` column and the `external_item_id` column from the Excel input.
- `tools/importers/import_external_item_id.php` prepares row-location validation SQL using `SELECT itemId FROM item WHERE itemId = :itemId`.
- `tools/importers/import_external_item_id.php` updates with `UPDATE item SET external_item_id = :external_item_id WHERE itemId = :itemId`.

### Interpretation
- `external_item_id` functioned as an imported external identity field populated onto existing `item` rows.
- `db_itemId`/`itemId` remained the row-location mechanism.
- `external_item_id` was a secondary identity/reconciliation field, not the row primary key.
- Later ProductDB `model_id` values can be matched through `external_item_id` in current local MySQL as a compatibility mapping.
- The original prose rationale for the name `external_item_id` is still not found.

---
## 4) Evidence quality and limits

### Found
- Strong current-state evidence that operations map `model_id` -> `item.external_item_id`.
- Evidence for when this mapping language appears in the currently available repository history window.

### Not found
- No explicit, older architectural rationale commit message or design note (within available repo history) that conclusively states why the physical column was first named `external_item_id`.
- No available in-repo history before the observed 2026-05-21 window that can test the pre-February-2026 origin period.

### Quality assessment
- Current-state mapping conclusion: **high confidence**.
- Historical-origin intent (why originally named `external_item_id`): **unknown from current repo history**.
- Historical sequencing claim (`external_item_id` predates governance `model_id`): **plausible externally, but not provable from currently available repository history alone**.

The origin of `external_item_id` likely lies in earlier local project files, older database exports, earlier Excel/database design work, or project history not preserved in the current Git repository.

The current audit can only document current operational mapping, not original naming intent.

---

## 5) Evidence still required to answer historical origin

Likely evidence sources outside the currently available Git history include:

- Older local project folders from before February 2026.
- Older SQL exports/backups.
- DBeaver database creation/migration history (if available).
- Excel workbook versions from before `model_id` governance was created.
- Earlier README or planning documents outside the current Git history.
- Old ChatGPT handovers or saved notes from the period when `external_item_id` was first introduced.

---

## 6) Operational decision despite incomplete history

- The historical origin is not yet known.
- DBeaver has confirmed `item.external_item_id` is the current physical identity column in local MySQL.
- Therefore, Ryderwear SQL regeneration may proceed using `external_item_id` as a compatibility match column while the historical-origin question remains unresolved.
- A future schema-normalization project can decide whether to rename `external_item_id` to `model_id`.

---

## 7) Consequence for current Ryderwear SQL and docs

1. Do **not** rename columns in this phase.
2. SQL targeting current local MySQL should continue using `item.external_item_id` where that is the actual physical column.
3. Document this as a compatibility mapping (`model_id` logical -> `external_item_id` physical), not as proof of original schema intent.

---

## Final conclusion

- Historical origin rationale: still not fully documented in prose.
- Functional origin/evidence: stronger evidence now supports an importer/reconciliation purpose for `external_item_id`.
- Current operational decision: use `item.external_item_id` for Ryderwear SQL matching, and do not rename columns in this phase.
