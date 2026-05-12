# Stage 3K - Top-Three Shortlist Data Readiness Audit

Date: 2026-05-12

Status: planning and audit only. No shortlist implementation was performed.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3K audits whether the current Hero Manager candidate endpoint and diagnostics data are sufficient to support a future AI-led top-three shortlist contract.

The intended future workflow is:

1. AI ranks candidates by default.
2. The product list or filtered list shows the best three hero-image candidates per product.
3. The administrator opens the full image set only when challenging the shortlist.
4. Manual editorial selection remains the final authority.

This audit does not implement the shortlist.

## Files Inspected

- `admin/hero-candidates.php`
- `inc/hero/candidates.php`
- `inc/hero/score.php`
- `inc/hero/diagnostics.php`
- `tools-dev/image-analysis/out/hero_candidates_stage1.json`

Also inspected for context:

- `admin/hero-manager.php`
- `admin/hero-edit.php`
- `tools/update-hero-images.php`
- `js/admin/hero.js`
- `README/V-AUDIT/POST-AUDIT/2026-05-11-Stage-3J-AI-Led-Top-Three-Hero-Candidate-Workflow.md`

No implementation files were changed during this audit.

## Current Endpoint Shape

Endpoint reviewed:

`admin/hero-candidates.php?item_id=...`

Current top-level response shape:

```json
{
  "item_id": 79,
  "candidates": []
}
```

Current candidate fields:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`
- `diagnostics`

The `diagnostics` field is additive. It is appended after `sw_enumerate_scored_candidates()` returns.

Diagnostics currently do not alter:

- `score`
- `rank`
- `analysis`
- `status`
- `actions`
- candidate order

## Current Ranking Basis

Candidate enumeration and ranking are owned by:

`inc/hero/candidates.php`

Ranking is produced by:

1. Collecting candidates from `chosen_image` and `thumbnails_json`.
2. Deduplicating by lowercase basename.
3. Loading legacy `image_headroom` analysis by basename.
4. Scoring with `sw_score_candidate()` from `inc/hero/score.php`.
5. Sorting candidates by `score` descending.
6. Assigning `rank`.

The current endpoint score is based on legacy headroom/image analysis:

- face count
- crop safety
- headroom percentage
- penalty for extra faces

The current endpoint score does not include:

- Stage 2D diagnostic metadata
- product type
- ROI specificity
- category-aware rules
- criteria profiles
- face handling preferences such as avoid, optional, prefer, require
- object-specific rules
- campaign/lifestyle intent

## What Current Rank Means

Current `rank` is best understood as:

`legacy technical candidate rank`

It can support a temporary shortlist display, but it is not yet a true AI hero recommendation rank.

It is useful because:

- It already orders candidates.
- It is stable enough for current candidate-panel review.
- It preserves existing Hero Manager behavior.
- It gives a low-risk starting point for showing a top-three slice.

It is limited because:

- It is not product-type aware.
- It can overvalue face/headroom for products where face is not the main target.
- It can undervalue object products.
- It does not know the active editorial intent.
- It does not use Stage 2D diagnostics to rank.

## Relationship Between Existing Score And Diagnostic Score

There are two independent score concepts.

### `candidate.score`

Source:

`inc/hero/score.php`

Scope:

Legacy Hero Manager candidate inspection score.

Used for:

- Current endpoint rank.
- Current candidate display order.

Based on:

- `image_headroom`
- face count
- crop safety
- headroom percentage

### `candidate.diagnostics.score.final_advisory_score`

Source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`, exposed through `inc/hero/diagnostics.php`.

Scope:

Diagnostic and category-scoped.

Explicit score scope:

`diagnostic_within_category_not_global_rank`

Used for:

- Explanation and diagnostic review only.

Not currently used for:

- endpoint rank
- candidate sorting
- current Hero Manager score
- automatic selection

### Comparison Verdict

The two scores are independent and should not be combined yet.

They are not globally comparable.

`final_advisory_score` should remain hidden by default for now. Using it directly for top-three ranking would be risky because it is category-scoped, generated from a controlled diagnostic test set, and not yet a formal Hero Manager ranking contract.

## Sample Endpoint Findings

### Item 79 - Booty Shorts

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=79`

Observed:

- Candidate count: `5`
- Candidate ranks: `1` through `5`
- Scores:
  - rank 1: `75`
  - rank 2: `70.38`
  - rank 3: `70`
  - rank 4: `70`
  - rank 5: `60.61`
- Diagnostics:
  - `available: false`
  - `status: no_record_for_image`

Interpretation:

The current endpoint can provide a temporary top-three slice for this item using legacy rank. It cannot explain those top three using Stage 2D diagnostics because no matching diagnostic records exist.

### Item 98 - 600ml Water Bottle

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=98`

Observed:

- Candidate count: `1`
- Candidate path:
  - `images/brands/nike/other/600ml_waterbottle.png`
- `candidate.score`: `0`
- Diagnostics:
  - `available: true`
  - `status: ready`
  - `product_type: object`
  - `roi.specificity: object_bbox`
  - `roi.is_garment_specific: false`
  - `diagnostics.score.final_advisory_score: 80.28`

Interpretation:

This item shows the gap clearly. The diagnostic system correctly recognizes an object product, while the legacy endpoint score gives `0` because the old score depends on headroom/face-style analysis. That confirms current rank is not yet a product-correct AI recommendation system.

## Diagnostic JSON Readiness

The Stage 2D JSON contains:

- schema: `active_layers.hero_candidates_stage2d.v1`
- image count: `33`
- product types:
  - `full_body: 5`
  - `lower_body: 8`
  - `object: 8`
  - `sports_bra: 8`
  - `upper_body: 4`
- ROI specificity values:
  - `alpha_subject_bbox: 10`
  - `body_region_band: 15`
  - `object_bbox: 8`
- score scope:
  - `diagnostic_within_category_not_global_rank`

The JSON is useful for diagnostics and explanation, but it is not complete catalogue coverage and is not yet a production ranking contract.

## Top-Three Feasibility Verdict

Current data can support:

`a temporary top-three shortlist slice based on existing legacy rank`

Current data cannot yet support:

`a criteria-aware AI hero recommendation shortlist`

Safest interpretation:

- Existing rank can be used as a placeholder top-three order.
- Diagnostics can help explain some candidates when matching records exist.
- Diagnostics should not yet determine rank.
- A new criteria-aware recommendation score or profile layer is needed before the shortlist can be considered product-correct.

This combines the likely conclusions:

- A. Existing rank can support a temporary top-three shortlist.
- B. Existing rank is not sufficient as the final basis.
- C. Diagnostics can explain candidates but should not yet determine shortlist ranking.
- D. A criteria-aware recommendation layer is needed before the shortlist is product-correct.

## Product-Type And Criteria-Profile Gap

Future criteria concepts include:

- `product_first`
- `body_region_first`
- `face_optional`
- `face_preferred`
- `face_required`
- `full_outfit`
- `object_only`
- `campaign_lifestyle`

Current endpoint status:

- Does not include `active_criteria_profile`.
- Does not include candidate-level `recommendation_reason`.
- Does not include `recommendation_confidence`.
- Does not include criteria-aware ranking.
- Does not re-rank when editorial intent changes.
- Does not know whether face should be avoided, optional, preferred, or required.

Current diagnostics status:

- Can expose sanitized `product_type`.
- Can expose ROI specificity and confidence.
- Can expose review warnings.
- Can expose diagnostic vocabulary.
- Can expose an advisory score with `display_score: false`.
- Does not expose raw face/pose/object data directly.
- Does not perform garment segmentation.

The endpoint can explain that a candidate is an object, sports bra, lower body, upper body, or full body when diagnostics are available. It cannot yet explain why a sports bra image was de-prioritized for showing or not showing face, because no active face-handling criteria profile exists.

## Shortlist Contract Requirements

A future shortlist contract should separate:

- recommended top-three candidates
- all candidates for challenge view
- criteria/profile context
- diagnostics/explanation

Recommended future shape:

```json
{
  "item_id": 98,
  "active_criteria_profile": "object_only",
  "shortlist_basis": "legacy_rank_placeholder",
  "recommended_candidates": [
    {
      "recommendation_rank": 1,
      "path": "images/brands/nike/other/600ml_waterbottle.png",
      "recommendation_reason": "Ranked from current legacy candidate score; diagnostics identify object product.",
      "recommendation_confidence": "placeholder",
      "candidate": {},
      "diagnostics": {}
    }
  ],
  "all_candidates": []
}
```

Recommended direction:

Use both:

- a top-level `recommended_candidates` array for the normal product-list shortlist
- the existing full `candidates` or `all_candidates` array for challenge/review mode

Do not rely on candidate-level flags only. A top-level shortlist array will be easier for future UI to consume and will make the normal vs challenge workflow clearer.

## Candidate-Level Shortlist Metadata

Future endpoint-only metadata could include:

- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `active_criteria_profile`
- `shortlist_basis`
- `shortlist_warning`
- `recommendation_confidence`

Needed immediately for a placeholder shortlist contract:

- `is_recommended_top_three`
- `recommendation_rank`
- `shortlist_basis`
- `shortlist_warning`

Should wait until Stage 3L or later:

- `active_criteria_profile`
- criteria-aware `recommendation_reason`
- criteria-aware `recommendation_confidence`

Reason:

The criteria profiles do not exist yet. Adding profile-style fields before defining profiles would make the contract look more mature than the ranking logic actually is.

## All-Candidates Challenge View Requirement

The future contract must preserve the full candidate set.

Normal product-list view may use only top three, but challenge/review mode still needs:

- all candidates
- existing rank
- existing score
- diagnostics
- warnings
- manual selection status
- rejection status
- current hero status
- action availability

The top-three shortlist should not replace the full candidate data. It should be an editorial entry point into the full candidate set.

## Risks Of Using Current Rank As Top Three

Risks:

- Current score may not reflect product category.
- Current score may overvalue face/headroom.
- Current score may not match sports bra or lower-body criteria.
- Current rank does not use Stage 2D diagnostics.
- Object products can score poorly under the legacy score.
- Diagnostic test set covers only 33 images, not the full catalogue.
- Missing diagnostics may produce inconsistent explanations.
- Top-three candidates may be mistaken for final selection authority.
- Criteria adjustment is not implemented.
- Current deduplication by basename may hide images if different paths share the same basename.

Safeguards:

- Treat current top-three as a temporary `legacy_rank_placeholder`.
- Keep manual override final.
- Preserve all candidates for challenge mode.
- Keep diagnostic score hidden by default.
- Do not merge `candidate.score` and `final_advisory_score` yet.
- Do not claim the shortlist is product-correct until criteria profiles exist.
- Define criteria profiles before finalizing a recommendation contract.

## Stage 3K Readiness Conclusion

Current endpoint data is sufficient to prototype a read-only placeholder top-three shortlist.

It is not sufficient for a proper AI-led, criteria-aware hero recommendation workflow.

The current rank can safely support a temporary top-three shortlist only if it is clearly labeled as legacy-rank based and not presented as final AI authority.

The current diagnostics can support explanation and review, but should not yet drive ranking.

## Recommended Stage 3L

Recommended next stage:

Stage 3L - Define hero-selection criteria profiles.

Stage 3L should formalize the selection profiles before designing the final shortlist endpoint contract.

Key Stage 3L outputs should include:

- Profile names.
- Intended product types.
- Face handling rule.
- ROI preference.
- Object handling rule.
- Campaign/lifestyle handling.
- What diagnostics each profile can safely use.
- What should remain manual/editorial.

Stage 3L should remain planning unless explicitly approved for implementation.
