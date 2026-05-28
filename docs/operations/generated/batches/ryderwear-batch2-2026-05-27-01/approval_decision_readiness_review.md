# Ryderwear Batch 2 approval decision readiness review

## Scope and method
This review cross-checks the following five artifacts only:
- next_approval_decision.md
- approval_checklist.csv
- destination_collision_report.csv
- publication_gate_report.csv
- image_field_update_plan.csv

No source inventory, suspicious mapping report, or copy simulation artifact was created.

## 1) Cross-check: next worksheet vs checklist

### Result
- Checklist unresolved set count: 12 rows (dec-001 to dec-012).
- Worksheet unresolved set count: 12 rows (dec-001 to dec-012).
- Decision IDs, item keys, statuses, and actions match exactly, including itemId 184 as deferred_source_verification.

### Detail check
- dec-001,138,propose_split_destination,pending_human_approval: match.
- dec-002,153,propose_split_destination,pending_human_approval: match.
- dec-003,154,propose_split_destination,pending_human_approval: match.
- dec-004,157,propose_split_destination,pending_human_approval: match.
- dec-005,163,propose_split_destination,pending_human_approval: match.
- dec-006,158,propose_split_destination,pending_human_approval: match.
- dec-007,160,keep_existing_destination_owner,pending_human_approval: match.
- dec-008,162,propose_split_destination,pending_human_approval: match.
- dec-009,174,propose_split_destination,pending_human_approval: match.
- dec-010,166,propose_split_destination,pending_human_approval: match.
- dec-011,176,propose_split_destination,pending_human_approval: match.
- dec-012,184,needs_manual_review,deferred_source_verification: match.

## 2) Split-destination approval decisions

Allowed decision values for these rows:
- approve_split
- revise_split_path
- keep_existing_destination_owner
- defer_pending_source_verification

| key | current status | relevant source artifact | available evidence summary | unresolved question | recommended reviewer action | allowed decision values |
|---|---|---|---|---|---|---|
| dec-001 / itemId 138 | pending_human_approval | destination_collision_report.csv group-1-activate-vs-momentum | Duplicate destination path at activate black; proposed split adds activate/twist/black. | Is proposed split semantically correct for this model and naming policy? | Validate model-to-path semantics, then approve split or request revised token. | approve_split; revise_split_path; keep_existing_destination_owner; defer_pending_source_verification |
| dec-002 / itemId 153 | pending_human_approval | destination_collision_report.csv group-2-lift-pair | Duplicate lift path; proposed split uses collection token lift-2-0. | Should lift 2.0 own a distinct collection token vs shared lift token? | Confirm collection naming convention and approve or revise split. | same as above |
| dec-003 / itemId 154 | pending_human_approval | destination_collision_report.csv group-2-lift-pair | Same collision group as 153; proposed split rib-seamless-halter. | Is rib-seamless-halter the accepted discriminator for this model? | Review with dec-002 as a pair to avoid contradictory naming. | same as above |
| dec-004 / itemId 157 | pending_human_approval | destination_collision_report.csv group-3-nkd-staples-cut-split | Duplicate staples path; proposed split low-support/bandeau. | Confirm bandeau token is correct canonical cut token. | Approve split if canonical; otherwise revise split path. | same as above |
| dec-005 / itemId 163 | pending_human_approval | destination_collision_report.csv group-3-nkd-staples-cut-split | Same group as 157; proposed split low-support/one-shoulder. | Does one-shoulder token align with model taxonomy? | Decide with dec-004 jointly for coherent split policy. | same as above |
| dec-006 / itemId 158 | pending_human_approval | destination_collision_report.csv group-4-core-vs-embody | Duplicate embody blue path; proposed split to core/blue. | Should this model move to core or remain embody variant? | Validate model text and any known owner evidence before approving. | same as above |
| dec-007 / itemId 160 | pending_human_approval | destination_collision_report.csv group-4-core-vs-embody | Same group as 158; recommended keep existing destination owner; medium severity. | Is existing owner evidence strong enough to keep current ownership? | Confirm source ownership; keep owner only if provenance is clear. | same as above |
| dec-008 / itemId 162 | pending_human_approval | destination_collision_report.csv group-5-knot-vs-twist-cross-over | Duplicate cross-over white path; proposed split to tank/knot/white. | Is knot the correct structural discriminator vs cross-over family? | Validate fit to naming schema and approve or revise. | same as above |
| dec-009 / itemId 174 | pending_human_approval | destination_collision_report.csv group-5-knot-vs-twist-cross-over | Same group as 162; proposed split to tank/twist/white. | Is twist token supported and non-overlapping with knot decision? | Review with dec-008 together to avoid swapped ownership. | same as above |
| dec-010 / itemId 166 | pending_human_approval | destination_collision_report.csv group-6-scrunch-v-halter-vs-underwire-keyhole | Duplicate scrunch/bra espresso path; proposed split to scrunch-v-halter. | Is scrunch-v-halter accepted path grammar? | Approve if taxonomy-consistent; else revise tokenization. | same as above |
| dec-011 / itemId 176 | pending_human_approval | destination_collision_report.csv group-6-scrunch-v-halter-vs-underwire-keyhole | Same group as 166; proposed split to underwire-keyhole. | Should underwire-keyhole be separate branch from scrunch-v-halter? | Resolve pair with dec-010 in one reviewer pass. | same as above |

## 3) itemId 184 deferred source verification

| key | current status | relevant source artifact | available evidence summary | unresolved question | recommended reviewer action | allowed decision values |
|---|---|---|---|---|---|---|
| dec-012 / itemId 184 | deferred_source_verification | approval_checklist.csv, destination_collision_report.csv group-7-sculpt-multi-competitor, next_approval_decision.md section B | Collision is high severity and marked needs_manual_review with proposed sculpt-seamless-halter destination. Worksheet explicitly says provenance and competing ownership must be manually verified first. | Which source root and ownership evidence is authoritative for this sculpt seamless halter competitor set? | Keep deferred until provenance evidence is explicitly cited, then choose decision. | approve_split; keep_existing_destination_owner; defer_pending_source_verification |

## 4) Suspicious/remap manual-review cases

Allowed decision values:
- approved_remap
- rejected_remap
- deferred_source_verification
- resolved_no_change

| key | current status | relevant source artifact | available evidence summary | unresolved question | recommended reviewer action | allowed decision values |
|---|---|---|---|---|---|---|
| ryderwear_female_nkd_leggings_v_full_length_scrunch | suspicious_mapping_manual_review_required; manual_review_required | publication_gate_report.csv; image_field_update_plan.csv | Publication gate marks blocked for suspicious mapping. Image plan notes banner and collection-page campaign semantics in path and filename, indicating marketing asset risk. | Is this a true remap candidate to product images or should it be excluded pending better evidence? | Require manual provenance check; do not auto-remap. | approved_remap; rejected_remap; deferred_source_verification; resolved_no_change |
| ryderwear_unisex_gym_bag_accessories | suspicious_mapping_manual_review_required; manual_review_required | publication_gate_report.csv; image_field_update_plan.csv | Publication gate blocked. Image plan says row intentionally excluded per plan rules, and status is review_existing_images_present rather than update-ready. | Should this stay excluded or be remapped into scope with explicit reviewer approval? | Keep blocked until reviewer confirms exception policy and ownership. | approved_remap; rejected_remap; deferred_source_verification; resolved_no_change |
| ryderwear_female_nkd_shorts_v_scrunch | pending_human_approval with mismatch note | image_field_update_plan.csv | Path uses plus-size segment while model_id does not indicate plus sizing. Not gate-blocked yet, but semantic mismatch exists. | Is path truly correct for this model, or is model/path alignment wrong? | Treat as manual review before downstream outputs; decide remap or no-change with rationale. | approved_remap; rejected_remap; deferred_source_verification; resolved_no_change |

## 5) No-safe-candidate rows

Current evidence indicates at least two no-safe-candidate cases for automated progression:
- itemId 184 (explicit deferred source verification).
- ryderwear_female_nkd_leggings_v_full_length_scrunch (marketing/banner semantics, suspicious mapping block).

Allowed decision values for this category:
- defer_pending_source_verification
- request_additional_evidence
- exclude_from_simulation_scope

## 6) Recoverable mismatches

- ryderwear_female_nkd_shorts_v_scrunch appears recoverable if reviewer confirms whether plus-size path or model label should be normalized.
- Potentially recoverable taxonomy mismatches exist in split path token choices across grouped collisions (knot vs twist, core vs embody, scrunch-v-halter vs underwire-keyhole), but each still requires human approval.

Allowed decision values:
- approved_remap
- resolved_no_change
- request_path_revision

## 7) Publication blockers from gate report

- Batch is non-publishable across rows due to inactive_planning_scope and frontend_required_fields_ready=no.
- Two explicit suspicious mapping gate blockers:
  - ryderwear_female_nkd_leggings_v_full_length_scrunch
  - ryderwear_unisex_gym_bag_accessories

Implication: suspicious mapping adjudication is required before any safe suspicious report finalization.

## 8) Batch-level policy decisions still required

Before source inventory, suspicious mapping reporting, or copy simulation can safely proceed, reviewer/policy owner must confirm:
- approved source root policy.
- deterministic source_asset_id policy.
- checksum/bytes/mime normalization policy.
- provenance_note policy.

Allowed values (as stated in worksheet):
- approve_policy
- revise_policy
- defer_policy

## 9) Inconsistencies, mismatches, and clarity gaps

1. No count mismatch between worksheet and checklist for unresolved decision rows (12 vs 12).
2. Decision vocabulary differs by category and is spread across sections; this is correct but easy to misapply. A reviewer should use category-specific values only.
3. publication_gate_report.csv has blank itemId for all rows, while other artifacts use itemId for collisions. This can increase lookup friction and should be documented in reviewer notes when mapping keys.
4. image_field_update_plan.csv marks many rows pending_human_approval even when notes say no mismatch. This is not a contradiction, but it means pending status is not itself a risk signal.
5. For suspicious case 3 (nkd_shorts_v_scrunch), gate report does not mark blocked, but image plan flags semantic mismatch. This is a legitimate cross-artifact gap and should remain human-reviewed.

## 10) Blocked outputs status

The following remain blocked outputs and were not created by this review:
- source_asset_inventory.csv
- suspicious_mapping_report.csv
- copy_simulation.csv

## Recommended next human actions

1. Complete reviewer decisions for dec-001 to dec-011 in grouped collision order (groups 1 to 6) to preserve taxonomy consistency.
2. Resolve dec-012 itemId 184 with explicit provenance citation and approved source root before changing deferred status.
3. Adjudicate the three suspicious/remap manual-review cases, especially banner/campaign asset risk and plus-size path mismatch.
4. Record batch-level policy approvals (source root, source_asset_id, checksum/bytes/mime, provenance_note) before authorizing any downstream generated outputs.
5. Only after steps 1 to 4 are complete, authorize creation of source_asset_inventory.csv, suspicious_mapping_report.csv (report-only rules satisfied), and copy_simulation.csv.
