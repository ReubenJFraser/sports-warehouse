# Ryderwear Batch 2 Adjudication Split Review Guide

## Purpose
This guide explains how to use the split reviewer files generated from `ryderwear-batch-2-manual-adjudication-worksheet.csv`.

## Split files
- `ryderwear-batch-2-adjudication-no-safe-candidates.csv`
- `ryderwear-batch-2-adjudication-recoverable-mismatches.csv`

## How to interpret the splits

### 1) `no_safe_candidate` rows
These rows include the `no_safe_candidate` flag and represent items where no currently proposed source folder is considered safe to proceed.

Why they should not proceed yet:
- The candidate set is flagged as unsafe for automatic progression.
- Reviewers must **locate or confirm** a correct source folder manually first.
- Until that happens, these rows must remain blocked from any copy/import/reconciliation execution planning.

### 2) Recoverable mismatch rows
These rows are manual-validation candidates that **do not** include `no_safe_candidate`.
They include cases such as `collection_path_mismatch`-only patterns that may be recoverable when a reviewer visually confirms the source folder.

Why they may be recoverable:
- Path mismatch can happen even when an otherwise plausible source folder exists.
- If folder contents and naming are visually consistent with the model/item, reviewers can approve with a confirmed folder.
- Recovery still requires explicit reviewer confirmation and documentation in worksheet fields.

## Required reviewer fields before any later batch-2 copy/import/reconciliation plan
Every row that is reviewed must have these fields completed:
- `reviewer_decision`
- `approved_source_folder`
- `reviewer_notes`
- `reviewed_by`
- `reviewed_date`

Suggested `reviewer_decision` values:
- `approve`
- `reject`
- `hold`
- `needs_new_source_folder`

Important constraints:
- `approved_source_folder` must remain **blank** unless the folder is manually confirmed.
- Batch 2 must **not** proceed to file copying or MySQL reconciliation based on these split files alone.

## Counts
- Total manual adjudication rows: **22**
- `no_safe_candidate` rows: **9**
- Recoverable mismatch rows: **13**

### Rows by `candidate_confidence`
- `medium`: **13**
- `low`: **9**

### Rows by `primary_risk_reason`
- `collection_path_mismatch`: **13**
- `all_candidates_conflict_with_expected_product_type_path`: **9**
