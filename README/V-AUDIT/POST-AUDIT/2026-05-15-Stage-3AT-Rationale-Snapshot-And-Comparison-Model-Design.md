# Stage 3AT — Rationale Snapshot and Comparison Model Design

## 1. Purpose

This document defines the rationale comparison and snapshot model that should exist before any rationale storage/schema change is implemented. The goal is to remove ambiguity in future rationale records by ensuring each record captures the decision context at the time it was made.

The core purpose is to ensure future rationale records compare:
- the human-selected image;
- the system-ranked #1 image at the time of decision;
- the criteria profile and shortlist basis used at that time.

## 2. Core lesson from the audit

The exploratory Hero Manager audit showed that many notes focused on why the old/current hero was weak. That can be useful operationally, but it is not always useful for criteria refinement.

The highest-value criteria-refinement rationale is the case where:
- the system ranked an image as #1;
- the human selected a different image;
- the reviewer explains why the selected image should beat the ranked #1 image.

This is the clearest evidence that criteria behavior should potentially change.

## 3. Why the ranked #1 image must be snapshotted

The system-ranked top candidate is not stable over time. It can change because of:
- criteria-profile changes;
- scoring/ranking logic changes;
- metadata/category corrections;
- image-path corrections;
- restoration of previously missing images;
- manual rejection/exclusion state changes;
- source image updates.

Because of this, a rationale record must store the actual ranked #1 image path at decision time, not a later recalculation.

## 4. Recommended comparison model

A future rationale record should be explicitly modeled as a comparison among:

A. Human-selected image  
B. System-ranked #1 image  
C. Optional displaced existing/current hero  
D. Criteria profile / shortlist basis snapshot

For criteria refinement, the primary comparison pair is always:
- selected image vs system-ranked #1 image.

The displaced current hero can be helpful context, but is secondary unless explicitly relevant.

## 5. Recommended future fields

Proposed future fields for rationale storage (design only in Stage 3AT):

- `item_id`: Product/item identifier.
- `product_name_snapshot`: Product name string at decision time.
- `brand_snapshot`: Brand string at decision time.
- `selected_image_path`: Snapshot path of human-selected image.
- `selected_image_rank_snapshot`: Selected image rank position at decision time (if available).
- `selected_image_score_snapshot`: Selected image score at decision time (if available).
- `selected_image_role`: Semantic role of selected image (for example hero/detail/model/campaign context).
- `ranked_1_image_path_snapshot`: Snapshot path of system-ranked #1 image.
- `ranked_1_image_score_snapshot`: Score of ranked #1 image at decision time.
- `ranked_1_image_role`: Semantic role of ranked #1 image.
- `ranked_1_reason_snapshot`: Optional future snapshot of system explanation for why #1 ranked first.
- `displaced_current_hero_path_snapshot`: Optional snapshot path of pre-existing/current hero that was displaced.
- `displaced_current_hero_rank_snapshot`: Rank of displaced current hero at decision time (if present in candidates).
- `displaced_current_hero_role`: Semantic role of displaced current hero.
- `criteria_profile_snapshot`: Snapshot identifier or serialized representation of criteria profile used.
- `shortlist_basis_snapshot`: Snapshot of shortlist generation basis/rules used.
- `decision_type`: Canonical decision category.
- `comparison_target_role`: Indicates whether rationale compares against ranked #1, old hero, or another explicit target role.
- `product_specific_reason_codes`: Product-specific reason code array.
- `cross_cutting_signal_codes`: Cross-cutting signal code array.
- `reviewer_note`: Free-form reviewer explanation.
- `counts_toward_criteria_refinement`: Boolean for analytics/training eligibility.
- `data_quality_only`: Boolean for data-quality classification.
- `candidate_snapshot_json`: Optional structured snapshot of relevant candidate set (paths/ranks/scores/roles) used at decision time.
- `created_at` / `updated_at`: Record lifecycle timestamps.
- reviewer metadata (future): Optional reviewer identity/role/source fields if/when added later.

## 6. Decision types and comparison meaning

How the comparison model applies to decision types:

- `accepted_top_candidate`: **Central comparison but no override**. Selected image equals ranked #1; usually no override rationale is required beyond minimal confirmation.
- `manual_override_against_top_candidate`: **Central and high-value**. Selected image differs from ranked #1; this is primary criteria-challenge evidence.
- `corrected_old_stored_hero`: **Secondary**. Often a cleanup correction where selected image may already align with ranked #1; typically lower criteria-refinement value.
- `paired_product_differentiation`: **Central** when ranked #1 favors near-duplicate framing that harms differentiation.
- `product_detail_closeup_preferred`: **Central** when selected close-up is judged superior to ranked #1 in product communication.
- `model_personality_hero_preferred`: **Central** when reviewer intentionally favors personality/pose expression over ranked #1.
- `campaign_background_context_preferred`: **Central** when contextual campaign composition should beat ranked #1.
- `missing_image_data_failure`: **Not applicable for criteria refinement**. Ranked #1 may be missing/broken; classify as data-quality-only.
- `temporary_best_available_image`: **Secondary**. Operational stopgap while better assets are unavailable; comparison may exist but should rarely drive criteria change.

Important distinctions:
- `accepted_top_candidate`: selected image equals ranked #1; usually no override rationale needed.
- `manual_override_against_top_candidate`: selected image differs from ranked #1; high-value criteria evidence.
- `corrected_old_stored_hero`: selected image may be ranked #1; mostly cleanup, lower criteria value.
- `missing_image_data_failure`: ranked #1 may be broken/missing; data-quality record, not criteria-refinement record.

## 7. Legacy audit records

Rationale records produced during the exploratory/manual audit should be treated as legacy/exploratory data.

They may contain useful editorial notes, but:
- they may not clearly identify the ranked #1 image being challenged;
- checkbox selections may describe the rejected old hero, not the ranked #1 candidate;
- they should not be treated as clean criteria-refinement evidence unless manually re-entered under the future snapshot comparison model.

## 8. Future UI implication

Future rationale capture UI should be designed as an explicit comparison panel:

System ranked #1:  
`[image/path/rank/score]`

Human selected:  
`[image/path/rank/score]`

Optional displaced current hero:  
`[image/path/rank/role]`

Prompt:  
**Why should the human-selected image be used instead of the system-ranked #1 image?**

Then capture:
- decision type;
- product-specific reason checkboxes;
- cross-cutting signals;
- reviewer note;
- counts-toward-criteria-refinement toggle.

This structure directly addresses and prevents the ambiguity identified in the audit.

## 9. Backend/source of image paths

Image file paths should be stored as snapshot strings in the rationale record at save time.

Likely current sources for these paths include existing product image fields, `thumbnails_json`, selected hero/manual override fields, and shortlist/candidate payloads.

Stage 3AT does not require a final extraction implementation path. The principle is to store the exact file path snapshot used in the decision.

## 10. Data quality distinction

Missing-image cases require explicit separation from criteria-refinement records:
- if ranked #1 is missing or broken, mark the rationale as `data_quality_only`;
- missing-image records should not count toward criteria refinement;
- missing-image records should feed an Image Integrity Report pipeline rather than editorial criteria tuning.

## 11. Recommended next implementation stage

Recommended next stage after this design checkpoint:

### Stage 3AU: Add Rationale Snapshot Fields Storage Migration

Stage 3AU should:
- add nullable snapshot/comparison fields to `hero_override_rationale`;
- preserve existing records;
- avoid automatic backfill for legacy/exploratory records;
- avoid UI changes until snapshot storage exists.

Then sequence later stages:
- Stage 3AV: Update rationale save/read API for snapshot fields.
- Stage 3AW: Update rationale UI to compare selected image vs ranked #1.
- Stage 3AX: Product-specific dynamic checkbox groups.
- Stage 3AY: Image Integrity Report.

## 12. Non-goals

Explicit non-goals for Stage 3AT:
- no migration in Stage 3AT;
- no schema change;
- no runtime code;
- no UI change;
- no API change;
- no scoring/ranking change;
- no data backfill;
- no browser verification.

## 13. Acceptance criteria

This README is complete when it:
- defines the rationale comparison problem;
- explains why ranked #1 must be snapshotted;
- defines selected image vs ranked #1 as the core comparison;
- defines proposed future fields;
- distinguishes true criteria-challenge records from cleanup/data-quality records;
- explains why legacy audit records require caution;
- provides a roadmap to a later storage migration.
