# Ryderwear Batch 2 proposed reviewer decisions draft

## Draft status and reviewer authority
This is a proposed decision draft only. It does not record final human approval.

Human reviewer action is required before downstream artifacts may proceed. The human reviewer must accept, revise, or reject these proposed decisions before any downstream source_asset_inventory.csv, suspicious_mapping_report.csv, or copy_simulation.csv can proceed.

Referenced artifacts used for this draft:
- approval_decision_readiness_review.md
- next_approval_decision.md
- approval_checklist.csv
- destination_collision_report.csv
- publication_gate_report.csv
- image_field_update_plan.csv
- source_evidence_strategy.md
- manifest_consistency_and_source_gap_audit.md

## 1. Executive summary
- Total rows or cases reviewed in this draft: 15.
  - Split-destination decisions: 11 (dec-001 through dec-011).
  - Separate deferred source verification decision: 1 (itemId 184, dec-012, handled separately).
  - Suspicious or remap manual-review cases: 3.
- Low-risk enough to propose for approval now: dec-004, dec-005, dec-011, and dec-007 as keep_existing_destination_owner with provenance confirmation.
- Must remain deferred or require additional evidence: itemId 184 (dec-012), suspicious-01, suspicious-02, and suspicious-03.
- Policy readiness: source-root policy remains blocked pending explicit approved_source_root; other policies can be proposed with revise or defer until reviewer confirms normalization details.

## 2. Split-destination decisions (dec-001 through dec-011)

| decision_id | itemId | current_status | proposed_action | evidence summary | unresolved risk | proposed_reviewer_decision | proposed_reviewer_notes | confidence level | follow_up_required |
|---|---:|---|---|---|---|---|---|---|---|
| dec-001 | 138 | pending_human_approval | propose_split_destination | Collision group-1 duplicate destination path; proposed destination adds activate/twist/black discriminator to reduce direct collision. | Token ordering and semantic ownership may still need taxonomy confirmation. | approve_split | Proposed split appears internally consistent with collision note and avoids shared destination. Keep note to verify token grammar in final review pass. | medium | yes |
| dec-002 | 153 | pending_human_approval | propose_split_destination | Collision group-2 pair uses shared lift path; proposed destination uses lift-2-0 token. | lift-2-0 token style may conflict with existing collection token conventions. | revise_split_path | Keep split intent but normalize token style against approved naming standard and paired dec-003 decision. | medium | yes |
| dec-003 | 154 | pending_human_approval | propose_split_destination | Same group-2 collision; proposed destination uses rib-seamless-halter collection discriminator. | Pair consistency with dec-002 and long token grammar may need reviewer normalization. | revise_split_path | Maintain split outcome but request normalized discriminator format jointly with dec-002 to avoid drift. | medium | yes |
| dec-004 | 157 | pending_human_approval | propose_split_destination | Group-3 NKD staples collision; proposed low-support/bandeau split is explicit and model aligned in notes. | Minimal risk if bandeau is canonical cut token. | approve_split | Candidate split aligns with modeled cut semantics and collision structure. | high | no |
| dec-005 | 163 | pending_human_approval | propose_split_destination | Same group-3 collision; proposed low-support/one-shoulder split tracks model descriptor. | Ensure one-shoulder token is canonical in taxonomy. | approve_split | Approve with same token-family check as dec-004 for consistency. | high | no |
| dec-006 | 158 | pending_human_approval | propose_split_destination | Group-4 core vs embody collision; proposed destination moves this row to core/blue. | Core vs embody ownership may be swapped without explicit provenance proof. | defer_pending_source_verification | Defer until reviewer confirms ownership evidence between competing model semantics in group-4. | low | yes |
| dec-007 | 160 | pending_human_approval | keep_existing_destination_owner | Group-4 paired row already recommends keep existing owner at embody/blue with medium severity. | Requires explicit confirmation that current owner provenance is stronger than competing candidate. | keep_existing_destination_owner | Keep existing owner, but require reviewer note citing provenance basis for retention. | medium | yes |
| dec-008 | 162 | pending_human_approval | propose_split_destination | Group-5 knot vs twist collision; proposed knot destination isolates one branch. | Risk of swapped knot vs twist attribution across pair. | revise_split_path | Request reviewer to confirm knot or twist attribution pairwise with dec-009 before finalizing token branches. | medium | yes |
| dec-009 | 174 | pending_human_approval | propose_split_destination | Same group-5 collision; proposed twist branch resolves duplicate destination if attribution is correct. | Possible branch inversion versus dec-008 if semantics are misassigned. | revise_split_path | Keep split strategy but require paired semantic confirmation and canonical token format with dec-008. | medium | yes |
| dec-010 | 166 | pending_human_approval | propose_split_destination | Group-6 collision proposes scrunch-v-halter branch for one competitor. | Compound token grammar may need normalization and pair integrity with dec-011. | revise_split_path | Request taxonomy check for compound token grammar and branch disambiguation against dec-011. | medium | yes |
| dec-011 | 176 | pending_human_approval | propose_split_destination | Same group-6 collision proposes underwire-keyhole branch; clear structural discriminator from scrunch-v-halter. | Low residual risk if paired branch logic is preserved. | approve_split | Approve with note that paired row dec-010 should be normalized to maintain deterministic split grammar. | high | yes |

## 3. itemId 184 deferred source verification (separate handling)

- itemId: 184
- decision reference: dec-012
- current_status: deferred_source_verification
- proposed_reviewer_decision: defer_pending_source_verification
- confidence level: high
- follow_up_required: yes

Evidence summary:
- Collision group-7 is high severity and already marked deferred_source_verification with needs_manual_review.
- Existing artifacts explicitly require manual provenance and competing ownership verification before advancement.
- No deterministic approved_source_root or deterministic source-asset proof is present in current reviewed artifacts.

Unresolved risk:
- Approving split or ownership without deterministic provenance could mis-assign destination ownership for sculpt seamless halter competitor rows.

Evidence needed to move itemId 184 forward:
1. Reviewer-approved approved_source_root scope for this batch.
2. Deterministic provenance evidence tying source assets to this item and competitor item(s).
3. Explicit reviewer note selecting either approve_split or keep_existing_destination_owner based on verified ownership.

## 4. Suspicious or remap manual-review cases

| key | current signal or status | evidence summary | unresolved risk | proposed_reviewer_decision | proposed_reviewer_notes | confidence level | follow_up_required |
|---|---|---|---|---|---|---|---|
| ryderwear_female_nkd_leggings_v_full_length_scrunch | suspicious_mapping_manual_review_required; blocked in publication gate; manual_review_required in image plan | Image plan notes banner and campaign semantics in path and filename; publication gate marks blocked. | High likelihood that mapped asset is marketing banner rather than product image set. | request_additional_evidence | Keep blocked. Require provenance evidence that candidate assets are product-safe and intended for product image fields, or reject remap. | high | yes |
| ryderwear_unisex_gym_bag_accessories | suspicious_mapping_manual_review_required; blocked in publication gate; review_existing_images_present in image plan | Publication gate blocked and image plan notes intentional exclusion per plan rules. | Scope exception may accidentally bypass policy and introduce non-approved remap. | resolved_no_change | Maintain current excluded stance unless reviewer explicitly approves exception with source evidence. | high | yes |
| ryderwear_female_nkd_shorts_v_scrunch | pending_human_approval with path-model mismatch note in image plan | Image plan indicates plus-size segment in path but model_id does not indicate plus sizing. | Semantic mismatch can cause incorrect destination mapping if left unverified. | request_additional_evidence | Request visual and metadata verification of model-to-path alignment; then choose approved_remap or resolved_no_change. | medium | yes |

## 5. Batch-level policy decisions

| policy | proposed_reviewer_decision | rationale |
|---|---|---|
| approved source root policy | defer_policy | approved_source_root is not yet finalized in current artifacts, so source-root approval remains blocked. |
| deterministic source_asset_id policy | revise_policy | Strategy suggests deterministic combined batch/path/hash approach, but reviewer should freeze exact canonical format before approval. |
| checksum/bytes/mime normalization policy | revise_policy | Policy direction is present, but toolchain and single-pass normalization details should be explicitly frozen before approval. |
| provenance_note policy | approve_policy | Recommended row-type guidance is concrete and can be approved as baseline pending source-root finalization. |

## 6. Recommended next human actions

### Actions that can be accepted now
- Accept proposed approve_split outcomes for dec-004, dec-005, dec-011 if taxonomy tokens are confirmed canonical.
- Accept proposed keep_existing_destination_owner for dec-007 only with explicit provenance citation.
- Accept resolved_no_change for ryderwear_unisex_gym_bag_accessories unless exception policy is intentionally opened.
- Approve baseline provenance_note policy guidance.

### Actions requiring source evidence or visual verification
- Keep itemId 184 deferred until approved_source_root and deterministic provenance are documented.
- Review and normalize split-token grammar for dec-002, dec-003, dec-008, dec-009, dec-010.
- Verify core vs embody ownership evidence before deciding dec-006.
- Obtain additional evidence for suspicious-01 and suspicious-03 before approving any remap.
- Freeze source-root, source_asset_id, and checksum or bytes or mime normalization policies before authorizing downstream artifact generation.

### Downstream artifact guardrail
Do not create source_asset_inventory.csv, suspicious_mapping_report.csv, or copy_simulation.csv until the human reviewer accepts, revises, or rejects these proposed decisions and records policy outcomes.
