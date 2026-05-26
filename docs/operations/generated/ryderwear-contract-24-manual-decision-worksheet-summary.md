# Ryderwear Contract 24 Manual Decision Worksheet Summary

Date: 2026-05-26 (UTC)

## Non-Execution Notice
This worksheet is **not an execution plan**.

No folder/file rename/move/copy/delete, ProductDB change, MySQL change, import execution, or runtime/admin/frontend/import code change is authorised by this deliverable.

## Inputs Combined
- Recoverable manual-review queue rows: **27**
- Source-needed queue rows: **9**
- Total rows requiring manual decision: **36**

## Completion Requirement Before Any Execution Plan
Before any Ryderwear batch 2 execution plan is drafted, every worksheet row must have these fields completed:
- `reviewer_decision`
- `approved_source_folder` (when applicable to decision)
- `approved_canonical_target_path` (when applicable to decision)
- `reject_reason` (required for reject decisions)
- `reviewer_notes`
- `reviewed_by`
- `reviewed_date`

## Suggested Reviewer Decision Values
- `approve_existing_candidate`
- `approve_new_source_folder`
- `hold_needs_visual_review`
- `needs_new_source_images`
- `reject_for_batch_2`
- `defer_to_later_migration`

## Recommended Next Manual Action
Assign a human reviewer to adjudicate every row in `ryderwear-contract-24-manual-decision-worksheet.csv`, confirm source-image provenance and canonical target folder per `model_id`, and only then prepare a separate reviewed execution-planning PR.
