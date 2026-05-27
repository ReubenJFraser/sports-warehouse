# Model ID vs `external_item_id` Historical Origin Audit (Documentation-Only)

Date: 2026-05-27  
Scope: Documentation and repository history audit only (no MySQL changes, no ProductDB edits, no runtime/admin/frontend/import code changes, no SQL regeneration).

## Why this revision exists

This audit replaces the prior incomplete version by adding both:

1. Current-tree reference search, and
2. Git history search (including earliest discoverable references in repo history).

---

## Commands executed (record)

### Current-tree search
- `rg -n "external_item_id|model_id|db_itemId|itemId" README docs tools scripts db .`
- `rg -n "external item|external id|external_item|supplier|source id|import id|staging|ProductDB|Excel" README docs tools scripts db .`

### Git history search
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

This should be treated as a **compatibility mapping in the current system state**, not by itself as proof of original naming intent.

---

## 2) Historical-origin findings from Git history

## Earliest discovered references in this repo history

- Earliest discoverable commit touching relevant tree paths and containing foundational docs/files in this repository snapshot:  
  `980f39b5604ee331174a678b120252bf0af97883` (2026-05-21, merge PR #93).

- Earliest discoverable `external_item_id` hit from `git log -S "external_item_id"` in scoped paths:  
  `acfd1bcb01d34e9b99e2d342909ae97a78a9c705` (2026-05-21).

- Earliest discoverable `model_id` hit from `git log -S "model_id"` in scoped paths:  
  `acfd1bcb01d34e9b99e2d342909ae97a78a9c705` (2026-05-21).

- Earliest discoverable `external_item_id`-specific SQL/schema planning evidence in generated ops docs:  
  `9041b1695629cde75f36d6ea2bfe937cfceaef46` (2026-05-24; widen `item.external_item_id` for `model_id`).

## What this implies about “predates model_id” inside available history

Within the repository history currently available, this audit **did not find evidence that `external_item_id` clearly predates `model_id`**. Earliest discoverable scoped hits for both terms appear at the same date window (2026-05-21).

Because of that, this audit cannot prove from this repo alone that:

- `external_item_id` was introduced earlier than `model_id`, or
- `model_id` was definitively mapped later onto a much older pre-existing column.

## Was `external_item_id` originally import/source/staging identity?

Inference from document language suggests `external_item_id` is often described in “external linkage/identity” terms and appears alongside staging/import planning vocabulary. However, no single explicit historical design note was found in available history that unambiguously states:

> “`external_item_id` was originally created specifically as supplier/platform/import identity and only later repurposed for `model_id`.”

So this point remains **inferred, not explicitly proven** by current evidence.

---

## 3) Evidence quality and limits

### Found
- Strong current-state evidence that operations map `model_id` -> `item.external_item_id`.
- Git-history evidence for when this mapping language appears in the present repo history window.

### Not found
- No explicit, older architectural rationale commit message or design note conclusively stating why the physical column was first named `external_item_id`.
- No discoverable earlier commit (before the currently available history window) proving `external_item_id` predated `model_id`.

### Quality assessment
- Current-state mapping conclusion: **high confidence**.
- Historical-origin intent (“why named external_item_id originally”): **inconclusive in explicit form**.
- Historical sequencing claim (“external_item_id definitively existed long before model_id”): **not proven from available Git history**.

---

## 4) Consequence for current Ryderwear SQL and docs

1. Do **not** rename columns in this phase.
2. SQL targeting current local MySQL should continue using `item.external_item_id` where that is the actual physical column.
3. This must be documented as a **compatibility mapping** (`model_id` logical -> `external_item_id` physical), not as proof of original schema intent.

---

## Final conclusion

- The prior “no rationale found” style conclusion is **now constrained** by actual Git-history search: the historical-origin rationale remains inconclusive in explicit wording, but the search was materially expanded and documented.
- Current operational behavior remains unchanged: use `item.external_item_id` for local MySQL compatibility when matching ProductDB/governance `model_id`.
