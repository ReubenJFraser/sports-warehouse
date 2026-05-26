# Ryderwear Contract 24 Planning Queues Summary

Date: 2026-05-26 (UTC)

## Non-Execution Notice
This document defines planning queues only. It is **not an execution plan**.

No folder rename/move/copy/delete, import execution, ProductDB update, MySQL update, or runtime/admin/frontend/import code change is authorised by this deliverable.

## Queue Purposes
1. **Grandfather queue** (`ryderwear-contract-24-grandfather-queue.csv`)
   - Purpose: Rows currently operational (or temporarily grandfathered) that should remain unchanged for now.

2. **Recoverable manual-review queue** (`ryderwear-contract-24-recoverable-review-queue.csv`)
   - Purpose: Rows that may proceed only after manual reviewer confirmation of `approved_source_folder` and canonical folder planning in a separate reviewed execution task.

3. **No-safe-candidate/source-needed queue** (`ryderwear-contract-24-source-needed-queue.csv`)
   - Purpose: Rows lacking a safe candidate path and requiring source-image location, canonical folder creation/population planning, or reviewer hold.

4. **Later migration-planning queue** (`ryderwear-contract-24-later-migration-queue.csv`)
   - Purpose: Rows flagged in audit as requiring a separate later reviewed migration-planning PR before any normalization changes.

## Queue Counts
- Grandfather queue rows: **26**
- Recoverable manual-review queue rows: **27**
- Source-needed queue rows: **9**
- Later migration-planning queue rows: **39**
- Total audited rows: **62**

## Coverage & Integrity
- Immediate planning coverage (grandfather + recoverable + source-needed): **62/62** rows.
- Rows intentionally excluded from immediate execution queues: **0** (none; all rows accounted for).
- Overlap across the three immediate queues: **none by design** (mutually exclusive by `audit_recommended_action`).
- Later migration-planning queue overlap: expected and intentional, because this queue is a cross-cut flag view (`requires_later_reviewed_migration_plan=yes`).

## Recommended Next Manual Action
1. Complete reviewer adjudication fields (`reviewer_decision`, `approved_source_folder`, `reviewer_notes`, `reviewed_by`, `reviewed_date`) for recoverable and source-needed rows using the existing manual worksheet.
2. Draft a separate reviewed migration-planning PR that defines canonical target folders per `model_id` for all later-migration rows.
3. Only after approval of that separate PR, prepare an execution PR for any copy/import/MySQL/ProductDB actions.
