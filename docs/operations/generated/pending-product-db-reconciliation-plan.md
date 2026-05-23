# Pending Product Database Reconciliation Plan (No-Execution)

Generated: 2026-05-23 (UTC)

## Purpose
This document defines a **documentation-only** plan for controlled database insertion/reconciliation of the **66 pending staging rows** currently classified as `new_products_pending_database_reconciliation`.

This plan is intentionally non-executable and does **not** perform any SQL writes, runtime changes, importer changes, or frontend publication.

## References and Current Reconciliation Context
- `docs/operations/generated/image-sync-reconciliation-summary.md`
- `docs/operations/generated/image-sync-reconciliation-report.csv`

These references establish that:
- `product_import_staging` is aligned to the current CSV taxonomy model.
- 66 rows have blank `db_itemId` and blank images and are classified as `new_products_pending_database_reconciliation`.
- Pending brand distribution is Ryderwear (62) + Adidas (4).

## Current Identifier Situation (Critical)
The local database currently contains two identifiers with different responsibilities:

1. **`item.itemId` (local DB primary key)**
   - Current active range: **70–123**
   - This is the local table key space for existing item rows.

2. **`item.db_itemId` (CSV/source-facing ID)**
   - Current numeric range: **1–54**
   - Current active rows all have numeric `db_itemId` values.

Implication for pending rows:
- The 66 pending rows will require a controlled `db_itemId` assignment phase later.
- Assignment likely continues after 54 to preserve continuity.
- **No `db_itemId` assignments are made in this plan.**

## Reconciliation Stages (Controlled Sequence)

### Stage 1 — Staging Validation (Read-Only)
Objective: Confirm pending-row scope and data shape before any insertion preparation.

- Reconfirm staging row counts and pending classification.
- Reconfirm taxonomy fields are present (`categoryName`, `subCategory`, `subCategoryParent`).
- Confirm the 3 blank `subCategoryParent` rows remain the known Set component exceptions.
- Confirm no unclassified pending rows outside the known Ryderwear/Adidas split.

### Stage 2 — Source Completeness Review
Objective: Evaluate content readiness for each pending product record.

- Review availability/quality of commercial fields (price/salePrice).
- Review text completeness policy fields (description/altText/ariaText).
- Review `assignment_source` policy for traceability.
- Record unresolved gaps by row (do not auto-fill).

### Stage 3 — `db_itemId` Assignment Policy Design (No Assignment Yet)
Objective: Define deterministic ID-assignment rules for later execution.

- Reserve a controlled post-54 numeric range for future assignments.
- Define deterministic ordering (e.g., brand → collection/model family → item variant) so reruns are stable.
- Define collision handling rules when candidate IDs or model keys conflict with existing records.
- Document rollback and audit logging expectations for the eventual execution step.

### Stage 4 — Image/Path Assignment or Synchronization Readiness
Objective: Ensure image linkage policy exists before insertion.

- Determine source-of-truth process for images/image paths.
- Validate naming/path conventions and fallback rules.
- Block insertion of frontend-intended rows lacking approved image-path mapping, unless explicitly marked as non-publishable seed rows.

### Stage 5 — Item Table Insertion Strategy (Design Only)
Objective: Define insertion grouping and guardrails.

- Insert in controlled batches, not all 66 at once.
- Use pre-insert conflict checks (model_id, brand+itemName, db_itemId uniqueness).
- Maintain per-batch audit records tied to reconciliation scope.
- Keep insertion and taxonomy/image remediation as distinct operational tracks.

### Stage 6 — Post-Insert Verification
Objective: Verify integrity after future insertion execution.

- Reconcile row-count deltas against expected batch size.
- Verify each inserted row has expected `db_itemId` policy compliance.
- Verify no duplicate `model_id` or brand+itemName collisions were introduced.
- Verify image/path and text policy conformance for publishable rows.

### Stage 7 — Frontend Readiness Gating
Objective: Prevent premature publication.

- Require completion of database reconciliation + image/path + content policy checks.
- Require explicit publishable-status signoff per batch/brand group.
- Keep non-ready rows out of frontend publication until all gates pass.

## Readiness Gates Required Before Any Insertion
All of the following gates should be satisfied prior to execution-phase insertion:

1. Required taxonomy fields present (`categoryName`, `subCategory`, `subCategoryParent`), with only known documented exceptions.
2. Product row classification is understood and documented (`new_products_pending_database_reconciliation`).
3. Price/salePrice policy resolved.
4. Images/image paths resolved per policy.
5. Description/altText/ariaText policy resolved.
6. `assignment_source` policy resolved.
7. No duplicate collision with existing `item` rows by:
   - `model_id`
   - `brand + itemName`

## Ryderwear as Showcase-Brand Priority Group (62 Rows)
Ryderwear should be treated as the primary controlled insertion program, not a blind bulk insert.

Recommended handling:
- Segment by collection/model_family/subCategory prior to execution.
- Prioritize representative showcase collections first, then expand incrementally.
- Enforce stricter review on image/text readiness due to showcase intent.
- Validate set/component relationships where relevant before batch approval.

## Adidas Pending Group (4 Rows) — Focused Review
Pending Adidas rows:
- Hyperglam Leggings
- Hyperglam Long Sleeve Crop Top
- Poly Linear Full-Zip Hoodie
- Poly Linear Track Pants

These may be component/product rows related to existing Adidas set products. Before insertion execution:
- Check model/set relationship assumptions.
- Confirm no duplicate overlap with existing Adidas catalog records.
- Approve them as a small, explicit batch only after all readiness gates pass.

## Read-Only SQL Checks for Future Use (No Writes)
The following are planning templates for future read-only validation and should be executed as `SELECT`-only checks when reconciliation moves to execution.

1. Duplicate checks by `model_id`.
2. Duplicate checks by `brand + itemName`.
3. Pending-row grouping by `brand`, collection/model family, and `subCategory`.
4. Checks against existing `db_itemId` values/ranges and gaps.
5. Missing-field checks for `price`, `salePrice`, images/image paths, `description`, `altText`, `ariaText`.

Note: This plan intentionally omits concrete executable SQL text to keep this PR documentation-only.

## Explicitly Out of Scope for This PR
The following are deferred to later execution PR(s):

- Actual `db_itemId` assignment.
- Image path completion.
- SQL insert/update execution scripts.
- Importer implementation changes.
- Frontend publication/release actions.

## Separation of Concerns
This plan explicitly distinguishes:

1. **Database insertion/reconciliation** (this document’s primary scope),
2. **Taxonomy remediation** (already tracked separately), and
3. **Image/path reconciliation** (tracked separately, prerequisite for publishability).

This separation avoids misclassifying legitimate new-product onboarding as generic missing-data defects.
