# Model ID vs external_item_id Schema-Normalisation Decision Plan

Date: 2026-05-27  
Scope: Decision-planning and audit only (no SQL execution, no MySQL/ProductDB/code changes).

## 1) Current state

### Identity field location snapshot
- **`model_id` exists in ProductDB/governance artifacts** (contracts and CSV schema), and is treated as canonical logical model identity in current contracts and image-governance docs.
- **`external_item_id` exists in local MySQL `item` table usage and importer scripts** as the current physical compatibility identity column.
- **`db_itemId` exists in ProductDB CSV and importer/reconciliation workflows** as linkage back to MySQL row identity.
- **`itemId` exists as MySQL internal row identity (PK)** and is used for row-location operations in importer evidence.
- **`product_id` exists as a future/proposed model-level identifier** in Product Model/ProductVariants architecture contracts, not yet the current physical MySQL matching mechanism for the Ryderwear SQL task.

### Explicit yes/no state
- MySQL currently has `model_id`: **No** (per audited evidence and DBeaver checklist context; no `item.model_id` in current local schema used by this workflow).
- MySQL currently has `external_item_id`: **Yes**.
- ProductDB currently has both `model_id` and `external_item_id`: **Yes** (CSV header includes both).
- `external_item_id` vs `model_id` relationship in current operations: **conceptually distinct historical roles, but often operationally equivalent values in current compatibility mapping workflows**.

### Practical interpretation of value relationship
- In present local operations, ProductDB `model_id` values are being matched through MySQL `item.external_item_id` as a compatibility bridge.
- This supports a **current operational equivalence pattern** for many rows, but does **not** prove original naming intent or total lifetime equivalence for all historical data without dedicated verification queries/checks.

---

## 2) Conceptual roles (distinguished)

- **`itemId`**: MySQL internal primary key / row identity.
- **`db_itemId`**: Excel/ProductDB linkage reference pointing back to existing MySQL `itemId` rows.
- **`external_item_id`**: earlier importer/reconciliation identity field written onto existing MySQL rows (secondary identity field, not PK).
- **`model_id`**: newer Excel-generated structural/canonical model identity under governance contracts.
- **`product_id`**: future/proposed model-level relational identifier in Product Model / ProductVariants architecture.

---

## 3) Evidence-based interpretation

### Proven evidence
1. Importer behavior shows `external_item_id` populated onto existing rows located by `db_itemId`/`itemId` (row-location by internal key, then set external field).
2. Current governance materials define `model_id` as canonical logical model identity for ProductDB/image identity workflows.
3. Current local MySQL compatibility planning maps ProductDB `model_id` values through `item.external_item_id`.
4. Current local schema context for this workflow includes `item.external_item_id` and does not include `item.model_id`.

### Inferred evidence
1. `external_item_id` appears to be an earlier/legacy-named field serving substantially the same stable product-identity role now formalised more precisely as `model_id` in governance.
2. Later governance layered `product_id`/ProductVariants/model architecture on top of earlier importer-era linkage patterns.

### Unresolved evidence
1. The original prose rationale for why the column was named `external_item_id` remains unresolved.
2. Full historical origin cannot be proven from currently available repository history alone.

**Decision relevance:** enough evidence exists for operational policy selection, even though original naming prose remains unresolved.

---

## 4) Codex governance impact (README/IV-CODEX accounted)

### Behavioural Rules impact
- Excel remains authority; DB is execution mirror.
- Identifier semantics must not be invented/reinterpreted ad hoc.
- Importers must remain constrained and explicit.
- Schema discipline applies: identity-field changes require deliberate audited migration, not opportunistic inline edits.
- Therefore, do not force a risky rename during Ryderwear image-update execution scope.

### Architecture Invariants impact
- Identity-column normalisation (`external_item_id` -> `model_id` physical adoption) crosses data-architecture boundaries.
- This requires a separate migration design/validation sequence, not a side-effect of image updates.

### Routing Invariants impact
- This decision does **not** require routing changes now.
- No routing modification is part of this task.

### GitHub PR workflow impact
- Schema-normalisation must be separated into its own reviewed PR sequence.
- It must not be folded into Ryderwear image-update PR scope.

---

## 5) Options

## Option A — Keep `external_item_id` as MySQL physical column indefinitely

**Pros**
- Zero schema disruption.
- Preserves compatibility with existing importer/scripts/runtime assumptions.
- Lowest immediate execution risk.

**Cons**
- Ongoing naming mismatch between canonical logical identity (`model_id`) and physical DB column name.
- Continued cognitive overhead and documentation complexity.

**Risks**
- Long-term technical debt and repeated compatibility explanations.

**Effect on Ryderwear SQL**
- Immediate compatibility: proceed with `external_item_id` matching.

**Effect on Codex governance**
- Compatible short-term, but perpetuates dual-term ambiguity.

## Option B — Rename `external_item_id` to `model_id` in MySQL now

**Pros**
- Terminology alignment between governance and physical schema.
- Cleaner future mental model.

**Cons**
- High blast radius across importers/scripts/docs/runtime assumptions.
- Requires coordinated updates across workflows and validations.

**Risks**
- Breakage during active Ryderwear image-update window.
- Potential silent mismatches if any code still expects `external_item_id`.

**Required compatibility work**
- Update importers, diagnostics, reports, generated SQL conventions, documentation, and any runtime/admin query paths.
- Validate all consumers before and after change.

**Why too risky during Ryderwear image update**
- Mixes schema migration risk with already-audited 21-row image correction scope.
- Violates separation-of-concerns expectation in governance/PR workflow.

**Effect footprint**
- Impacts importers, scripts, docs, ProductDB mapping assumptions, and runtime expectations.

## Option C — Add `model_id` to MySQL, keep `external_item_id` temporarily as compatibility alias

**Pros**
- Enables gradual migration.
- Reduces abrupt breakage risk.
- Allows controlled equivalence validation before cutover.

**Cons**
- Temporary dual-column complexity.
- Requires strong sync rules to avoid drift.

**Risks**
- Divergence risk if both columns are writable without governance.

**Migration sequence (staged)**
1. Add `item.model_id`.
2. Backfill from verified mapping (`ProductDB model_id` and/or existing `external_item_id`).
3. Run equivalence/NULL/duplicate checks.
4. Migrate scripts/importers/read paths to `model_id`.
5. Keep `external_item_id` as read-only legacy alias period.
6. Deprecate/remove `external_item_id` only after full migration + testing.

**How to verify equivalence**
- Compare nonblank value equality counts, duplicate distributions, orphan sets, and coverage against ProductDB model set.

**How long to keep `external_item_id`**
- Until every importer/script/runtime dependency is migrated and regression-tested in reviewed PR sequence.

**Required updates**
- Importers, diagnostics, reporting scripts, docs contracts/ops guides, and compatibility notes.

## Option D — Defer schema-normalisation; use `external_item_id` for immediate Ryderwear SQL

**Pros**
- Safest for immediate audited image-update objective.
- Keeps schema migration risk out of current operation.

**Cons**
- Defers naming alignment.

**Risks**
- Adds short-term technical debt if deferred too long.

**Technical debt impact**
- Manageable if paired with explicit follow-up migration plan and time-boxed governance task.

**Later decision still required**
- Select long-term path (likely staged Option C then eventual deprecation decision).

---

## 6) Recommended policy

### Recommendation
Adopt **Option D now**, with **Option C as the planned migration path**.

### Policy statement
1. Do **not** rename/delete `external_item_id` during Ryderwear image-update workflow.
2. Treat `model_id` as canonical logical identity name in governance going forward.
3. Treat `item.external_item_id` as current physical compatibility column in local MySQL.
4. Run schema-normalisation as a separate reviewed migration initiative.
5. Prefer staged compatibility migration, not abrupt rename/delete.

### Likely future path
1. Add `item.model_id` (if absent).
2. Populate `item.model_id` from verified ProductDB `model_id` / existing `item.external_item_id` mapping.
3. Update importers/scripts/docs to primary `model_id` usage.
4. Keep `external_item_id` as temporary legacy alias.
5. Deprecate/remove `external_item_id` only after full reference migration + testing sign-off.

---

## 7) Immediate Ryderwear SQL decision (explicit)

**Decision: proceed now using `item.external_item_id` as compatibility match column for the 21 kept-safe rows; do not wait for schema-normalisation completion.**

Rationale:
- 21-row kept-safe set already separated from 4 suspicious rows excluded by audit.
- `item.model_id` is not currently present in local MySQL for this workflow.
- Mixing schema-normalisation with Ryderwear image update would compound risk and violate clean PR-scope separation.

---

## 8) Required verification before any future schema migration (read-only)

1. If both DB columns exist later, count rows where `item.model_id != item.external_item_id` (trimmed, null-safe).
2. Count NULL/blank identity values in each identity column.
3. Detect duplicate values in each identity column.
4. Inventory scripts/docs referencing `external_item_id`.
5. Compare ProductDB `model_id` vs ProductDB `external_item_id` for identical/divergent/conceptually-different patterns.
6. Verify ProductDB `model_id` coverage in MySQL identity column(s).
7. Identify MySQL identity values lacking ProductDB `model_id` equivalents.
8. Produce explicit exception lists for manual review before any cutover.

---

## 9) Non-goals (this task)

This task does **not**:
- execute SQL;
- rename columns;
- add columns;
- drop columns;
- modify ProductDB;
- modify MySQL;
- modify runtime/admin/frontend/import code;
- regenerate Ryderwear SQL;
- alter copied Ryderwear image files.

---

## Files and evidence reviewed (record)

- Primary audit: `docs/operations/model-id-external-item-id-identity-audit.md`
- Contracts: `README/II-CONTRACTS/01, 17/18/19, 22/23/24` (plus current filename variant for 17).
- Codex governance: `README/IV-CODEX/01-04`.
- Post-audit evidence set listed in task prompt.
- Scripts/schema/data artifacts listed in task prompt, including importer, dry-run importer, schema dump, ProductDB CSV, and Ryderwear SQL + summary.
- Repository-wide search performed for: `external_item_id`, `model_id`, `db_itemId`, `itemId`, `product_id`.

