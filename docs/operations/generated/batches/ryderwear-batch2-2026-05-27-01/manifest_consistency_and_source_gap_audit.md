# Ryderwear Batch 2 Manifest Consistency and Source-Evidence Gap Audit

## 1) Scope
This document is a **report-only** internal consistency and evidence-gap audit for the standardized Ryderwear Batch 2 manifest folder:

- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/`

No DB/ProductDB/code/image/SQL/admin/runtime/frontend changes were made as part of this audit.

## 2) Created manifest inventory
### Existing standardized manifests
- `batch_manifest.csv`
- `product_identity_snapshot.csv`
- `asset_mapping_plan.csv`
- `destination_collision_report.csv`
- `approval_checklist.csv`
- `image_field_update_plan.csv`
- `hero_field_prep_plan.csv`
- `publication_gate_report.csv`
- `backfill_summary.md`

### Expected manifests intentionally absent
- `source_asset_inventory.csv` (intentionally deferred)
- `suspicious_mapping_report.csv` (intentionally deferred)
- `copy_simulation.csv` (intentionally deferred)

## 3) Header validation summary
Each existing generated CSV was checked against the corresponding template in:
- `docs/operations/templates/batch-media-ingestion/*.csv`

Result: **all existing generated CSV headers match their template headers exactly**.

Missing-by-design templates (`source_asset_inventory.csv`, `suspicious_mapping_report.csv`, `copy_simulation.csv`) are not yet instantiated in the batch folder.

## 4) Row-count summary
- `batch_manifest.csv`: 1
- `product_identity_snapshot.csv`: 25
- `asset_mapping_plan.csv`: 25
- `destination_collision_report.csv`: 12
- `approval_checklist.csv`: 12
- `image_field_update_plan.csv`: 25
- `hero_field_prep_plan.csv`: 21
- `publication_gate_report.csv`: 25

## 5) Approval-state summary (`approval_checklist.csv`)
### By `approval_status`
- `pending_human_approval`: 11
- `deferred_source_verification`: 1

### By `proposed_human_decision`
- `review_then_approve_or_revise`: 7
- `approve_split`: 3
- `approve_existing_owner_if_source_matches`: 1
- `defer_pending_source_verification`: 1

## 6) Publication-gate summary (`publication_gate_report.csv`)
### By `media_status`
- `ready`: 23
- `blocked`: 2

### By `visibility_status`
- `not_publishable`: 25

### By `media_status + visibility_status`
- `ready + not_publishable`: 23
- `blocked + not_publishable`: 2

## 7) Reconcile 21 vs 23 ready count
### Observed discrepancy
- `backfill_summary.md` states **21 image-ready Ryderwear rows**.
- `publication_gate_report.csv` shows **23 rows with `media_status=ready`**.

### Evidence-based reconciliation
The manifest set indicates this is a **definition mismatch**, not a raw counting error:

1. `hero_field_prep_plan.csv` has exactly **21** rows (all pending hero-manager recalc), which aligns with the reported “image-ready” count of 21.
2. `publication_gate_report.csv` has **25** total rows, where 23 are `media_status=ready` and 2 are `media_status=blocked`.
3. The two blocked publication-gate rows are suspicious-mapping rows with `blocked_reasons=suspicious_mapping_manual_review_required` (female NKD leggings full-length scrunch, unisex gym bag accessories).

### Conclusion on the two “extra” ready rows
- The 23 “ready” rows in publication gate appear to represent **media-readiness at publication gate level** (i.e., not blocked by suspicious mapping), while the 21 figure in backfill summary appears tied to **hero-field preparation coverage** (rows represented in `hero_field_prep_plan.csv`).
- Therefore, the +2 delta is most likely attributable to rows counted as media-ready in publication-gate logic but not represented in hero-field prep scope.
- This should be treated as a **classification/terminology issue** to normalize in a later cleanup task (e.g., explicitly distinguishing `image_ready_for_hero_prep` vs `media_ready_for_publication_gate`).

No data was modified during this audit.

## 8) Source evidence gap (blocking honest `source_asset_inventory.csv` creation)
Before `source_asset_inventory.csv` can be created with defensible provenance, the following evidence is missing or not yet normalized:

1. **Source root/path evidence**
   - A formally approved source root (or roots) for Ryderwear Batch 2, with explicit canonical path policy.
2. **Per-file source paths**
   - Deterministic per-asset absolute/relative source path capture for every mapped asset candidate.
3. **`source_asset_id` strategy**
   - A stable, collision-resistant source asset identifier rule (path-based hash, content hash, or curated ID policy).
4. **Checksum availability**
   - Verified checksums (algorithm specified, e.g., SHA-256) for each source asset.
5. **Byte size / MIME type availability**
   - Machine-readable size and MIME values per asset, captured from source-of-truth file metadata.
6. **Provenance notes**
   - Row-level provenance fields explaining how each source file was selected and validated (especially for split/remap/suspicious decisions).

## 9) Suspicious mapping gap (blocking honest `suspicious_mapping_report.csv` creation)
Before `suspicious_mapping_report.csv` can be created honestly:

1. **Normalized `reason_code` vocabulary**
   - Controlled value set for suspicious scenarios (e.g., filename-model mismatch, ambiguous ownership, duplicate destination conflicts, remap-required).
2. **Evidence field mapping**
   - Explicit mapping from observed artifacts/columns to suspicious-report evidence columns (what evidence text originates from which file/field).
3. **Status values**
   - Lifecycle/status taxonomy for suspicious entries (open, triaged, approved, rejected, deferred, resolved), with transition semantics.
4. **Inclusion criteria**
   - Precise rule for which rows are included (only blocked publication rows vs also warning-level/remap-required rows), including the reported 3 suspicious/remap manual-review rows.

## 10) Copy simulation readiness
`copy_simulation.csv` remains blocked by the following unresolved gates:

- 11 `pending_human_approval` rows in approval/collision workflow.
- itemId **184** `deferred_source_verification`.
- 3 suspicious/remap rows requiring manual review.
- Missing `source_asset_inventory.csv` with deterministic per-file provenance/checksum metadata.
- Unresolved destination collision approvals (12 collision-report rows still awaiting final human approval/defer resolution).

Given these blockers, generating copy simulation now would risk non-reproducible or non-defensible copy intent rows.

## 11) Recommended next safest task
Safest next task after this audit:

1. Complete human decisions for all 12 approval checklist rows (resolve the 11 pending + 1 deferred path).
2. Resolve source verification for itemId 184 with documented provenance.
3. Normalize suspicious/remap reason-code and evidence schema, then produce `suspicious_mapping_report.csv`.
4. Build deterministic `source_asset_inventory.csv` (including path, checksum, size, MIME, and source_asset_id policy).
5. Only then generate non-destructive `copy_simulation.csv` for approved rows.

This sequence preserves auditability and prevents speculative copy simulation records.
