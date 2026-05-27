# Model ID vs `external_item_id` Historical Origin Audit (Documentation-Only)

Date: 2026-05-27  
Scope: Documentation and repository history audit only (no MySQL changes, no ProductDB edits, no runtime/admin/frontend/import code changes, no SQL regeneration).

## Why this revision exists

PR #197 expanded current-tree and Git-history searching, but the historical-origin question remains unresolved because the currently available repository history begins too late.

This revision explicitly corrects that limitation:

1. The audit only found earliest references within the currently available Git history window.
2. `external_item_id` is known to have existed before early February 2026.
3. Therefore, the current repository history available to this audit is insufficient to explain why `external_item_id` was originally created.
4. January 16-24 contract-writing evidence must be treated as origin-context evidence for identity architecture, while May 2026 files remain current-governance confirmation only.

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

## 4) January identity-architecture context

This section evaluates January 16-24 identity-architecture contracts as context evidence, not just string matches.

### Contract 17 (`17-Product_Model_and_Variant_Separation_Contract.md`)
Contract 17 establishes the model-vs-variant split:
- Product Model is separated from Product Variant.
- Colour is treated as variant-defining.
- ProductDB is framed as model-level storage.
- ProductVariants is framed as variant-level storage.
- `product_id` is introduced as a future ProductDB identifier in this architecture.

### Contract 18 (`18-ProductVariants_Sheet_Schema_Contract.md`)
Contract 18 establishes ProductVariants linking and variant identity rules:
- ProductVariants references `SportWarehouse_ProductDB` via `product_id`.
- `product_id + colour` is used as the variant uniqueness mechanism.
- This is a forward relational/SKU architecture for future schema alignment, not the legacy importer linkage pattern that used `db_itemId`/`itemId` row location with `external_item_id` updates.

### Contract 19 (`19-Model_ID_Generation_&_Identity_Governance_Contract.md`)
Contract 19 establishes model identity governance in Excel workflows:
- `model_id` is generated in Excel.
- `model_id` is defined as a structural identifier.
- `model_id` governs formula/identity-generation behavior in workbook governance.
- Contract 19 explicitly does **not** govern database ingestion logic.

### Architectural interpretation for `external_item_id`
From the January sequence plus importer behavior:
- `external_item_id` appears to predate the later `model_id` governance formalization.
- `external_item_id` aligns with earlier importer/reconciliation linkage usage.
- `product_id`, ProductVariants, and `model_id` represent a later formalized identity architecture layer.
- `model_id` did not originally create `external_item_id`.
- Later workflows mapped `model_id` values onto `item.external_item_id` for compatibility with the existing MySQL physical column.

### Unresolved point
- The original prose rationale for why the column was first named `external_item_id` is still not found.
- However, the January architecture sequence now supports the interpretation that `external_item_id` already existed as importer/external-linkage infrastructure before model/product governance contracts were formalized.

---

## 5) Evidence quality and limits

### Found
- Strong current-state evidence that operations map `model_id` -> `item.external_item_id`.
- Functional importer evidence showing `external_item_id` as imported linkage onto existing `item` rows keyed by `itemId`.
- January contract sequence evidence clarifying later formalization of `product_id`, ProductVariants, and `model_id` governance boundaries.

### Not found
- No explicit original prose rationale that conclusively states why the physical column was first named `external_item_id`.
- No available in-repo history before the observed 2026-05-21 window that can directly document pre-February implementation commits.

### Quality assessment
- Current-state mapping conclusion: **high confidence**.
- Importer-function conclusion: **high confidence**.
- January architecture sequencing conclusion: **high confidence** for governance layering.
- Original naming intent (exact wording/rationale): **still unresolved**.

---

## 6) Evidence still required to answer naming-origin prose fully

Likely evidence sources outside the currently available Git history include:

- Older local project folders from before February 2026.
- Older SQL exports/backups.
- DBeaver database creation/migration history (if available).
- Excel workbook versions from before `model_id` governance was created.
- Earlier README or planning documents outside the current Git history.
- Old ChatGPT handovers or saved notes from the period when `external_item_id` was first introduced.

---

## 7) Revised conclusion and operational decision

- **Direct January evidence:** Phase 7 (`2026-01-17-Phase-7-Enforcement_Readiness.md`) shows missing `external_item_id` was already an importer concern by **2026-01-17**.
- **Functional importer evidence:** `import_external_item_id.php` populated `item.external_item_id` on existing rows located by `db_itemId`/`itemId`.
- **January identity-architecture evidence:** Contracts 17-19 formalize later `product_id`/ProductVariants/`model_id` governance, with Contract 19 explicitly outside database ingestion logic.
- **Current operational decision:** Ryderwear SQL should use `item.external_item_id` as the current MySQL physical compatibility column.
- **Future schema-normalization:** any rename or separation of `model_id` / `external_item_id` / `product_id` belongs to a later migration project.
- **May 2026 material positioning:** May 2026 README/governance files are useful for current-state confirmation, but are not treated as origin evidence for January-era field creation intent.

