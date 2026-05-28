# Ryderwear Batch 2 - Next Approval Decision Worksheet

## Purpose
This document is a documentation-only approval decision worksheet to unblock controlled source-evidence work for batch `ryderwear-batch2-2026-05-27-01`.

It references existing generated artifacts and does **not** create or simulate source/copy files.

## Referenced artifacts
- `approval_checklist.csv` (authoritative pending decision list)
- `destination_collision_report.csv` (collision context + proposed actions)
- `publication_gate_report.csv` (suspicious mapping gate blockers)
- `image_field_update_plan.csv` (recoverable mismatch notes)
- `source_evidence_strategy.md` (blocking policy + prerequisites)
- `manifest_consistency_and_source_gap_audit.md` (gap and blocker summary)

---

## A) Pending approval_checklist decisions (all unresolved rows)
Allowed decision values for this category (`destination_collision_split_path`):
- `approve_split`
- `revise_split_path`
- `keep_existing_destination_owner`
- `defer_pending_source_verification`

Reviewer fields to fill for each row:
- `reviewer_decision`
- `reviewer_notes`
- `approved_source_root`
- `approval_date`
- `follow_up_required`

| decision_id | itemId | current_status | proposed_action | reviewer_decision | reviewer_notes | approved_source_root | approval_date | follow_up_required |
|---|---:|---|---|---|---|---|---|---|
| dec-001 | 138 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-002 | 153 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-003 | 154 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-004 | 157 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-005 | 163 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-006 | 158 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-007 | 160 | pending_human_approval | keep_existing_destination_owner |  |  |  |  |  |
| dec-008 | 162 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-009 | 174 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-010 | 166 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-011 | 176 | pending_human_approval | propose_split_destination |  |  |  |  |  |
| dec-012 | 184 | deferred_source_verification | needs_manual_review |  |  |  |  |  |

---

## B) Deferred source verification decision (itemId 184)
**ItemId 184 must remain `deferred_source_verification` until a reviewer decides provenance ownership and approved source root scope.**

Decision needed before it can proceed:
1. Confirm which source root(s) are approved for this batch (`approved_source_root`).
2. Verify competing model ownership for the sculpt seamless halter collision context.
3. Choose one of:
   - `approve_split` (if ownership/provenance is validated),
   - `keep_existing_destination_owner` (if current owner is validated),
   - `defer_pending_source_verification` (if evidence is still insufficient).

Required reviewer fields:
- `reviewer_decision`
- `reviewer_notes` (must cite provenance evidence)
- `approved_source_root`
- `approval_date`
- `follow_up_required`

---

## C) Three suspicious/remap manual-review cases
These must be manually adjudicated prior to any safe suspicious report finalization.

Allowed decision values for suspicious/remap category:
- `approved_remap`
- `rejected_remap`
- `deferred_source_verification`
- `resolved_no_change`

| case_id | item / key | current signal | source artifact reference | reviewer_decision | reviewer_notes | approved_source_root | approval_date | follow_up_required |
|---|---|---|---|---|---|---|---|---|
| suspicious-01 | `ryderwear_female_nkd_leggings_v_full_length_scrunch` | suspicious_mapping_manual_review_required | `publication_gate_report.csv` blocked row |  |  |  |  |  |
| suspicious-02 | `ryderwear_unisex_gym_bag_accessories` | suspicious_mapping_manual_review_required | `publication_gate_report.csv` blocked row |  |  |  |  |  |
| suspicious-03 | `ryderwear_female_nkd_shorts_v_scrunch` | plus-size path token mismatch vs model_id | `image_field_update_plan.csv` note on path-model mismatch |  |  |  |  |  |

---

## D) Category separation and allowed decision values

### 1) No-safe-candidate rows
Definition: no deterministic source candidate can be approved from current evidence set.
- Allowed decisions: `defer_pending_source_verification`, `request_additional_evidence`, `exclude_from_simulation_scope`.

### 2) Recoverable mismatches
Definition: semantic/path mismatch appears correctable without destructive actions once reviewer confirms intent.
- Allowed decisions: `approved_remap`, `resolved_no_change`, `request_path_revision`.

### 3) Suspicious/remap cases
Definition: flagged manual-review mappings with unresolved evidence or semantic conflict.
- Allowed decisions: `approved_remap`, `rejected_remap`, `deferred_source_verification`, `resolved_no_change`.

### 4) Policy decisions
Definition: batch-level normalization/approval rules required before controlled scans/reports.
- Required decisions:
  - approved source root policy
  - deterministic `source_asset_id` policy
  - checksum/bytes/mime normalization policy
  - provenance_note policy
- Allowed decisions: `approve_policy`, `revise_policy`, `defer_policy`.

---

## E) Explicit blocked outputs (remain blocked)
The following artifacts remain blocked and must **not** be created until approvals and normalization decisions above are completed:
- `source_asset_inventory.csv`
- `suspicious_mapping_report.csv`
- `copy_simulation.csv`

Exception note: `suspicious_mapping_report.csv` may only be created as a **report-only** artifact after the strategy's reason-code/evidence/status normalization is explicitly approved and all three suspicious/remap cases have reviewer decisions.

