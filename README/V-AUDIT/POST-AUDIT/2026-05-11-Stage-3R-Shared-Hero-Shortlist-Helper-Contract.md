# Stage 3R - Shared Hero Shortlist Helper Contract

Date: 2026-05-12

Status: planning and documentation only. No helper file was created.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3R designs a shared read-only shortlist helper contract that can later be used by both:

- `admin/hero-candidates.php?item_id=98&include_shortlist=1`
- future `admin/hero-shortlists.php`

The project now has a single-item shortlist contract and a planned batch shortlist endpoint. Without a shared helper, shortlist logic may be duplicated across endpoints.

The helper should centralize:

- active criteria profile inference
- shortlist basis
- top-three selection logic
- candidate-level shortlist metadata
- recommendation reason text
- recommendation confidence
- current hero summary
- status and warning handling

## Stage 3R Status

- Stage 3R is planning/documentation only.
- No helper file is created.
- No endpoint behavior is changed.
- No UI is implemented.
- No JavaScript or CSS is changed.
- No scoring/ranking formulas are changed.
- No database writes are introduced.
- Manual selection remains final.

## Proposed Helper File

Future helper file:

`inc/hero/shortlist.php`

Stage 3R does not create this file.

## Read-Only Boundary

The future helper must be read-only.

It must not:

- write to MySQL
- update `hero_image`
- update `hero_score`
- update `hero_override`
- update `hero_rejections`
- change scoring formulas
- change candidate ranking formulas
- call Python
- regenerate JSON
- mutate diagnostics JSON
- alter manual selection authority
- render HTML
- depend on JavaScript or CSS

It may:

- receive candidate arrays
- receive item/product metadata
- inspect sanitized diagnostics already attached to candidates
- infer placeholder `active_criteria_profile`
- build shortlist contract arrays
- return read-only metadata

## Relationship To Existing Files

### `admin/hero-candidates.php`

Current role:

- owns the single-item candidate endpoint
- owns the current opt-in shortlist output

Future direction:

- keep default behavior unchanged
- use shared helper only when `include_shortlist=1`
- move shortlist-building logic out of the endpoint into `inc/hero/shortlist.php`

### `inc/hero/candidates.php`

Current role:

- owns candidate enumeration
- owns current candidate score/rank order

Future helper should not replace candidate enumeration.

### `inc/hero/diagnostics.php`

Current role:

- owns sanitized diagnostics lookup

Future helper may consume diagnostics already attached to candidates. It may call diagnostics functions only if explicitly passed image paths, but it should avoid duplicating diagnostics logic.

### Future `admin/hero-shortlists.php`

Future role:

- batch endpoint for product-list top-three display
- should call the same shared helper for each product

## Proposed Function List

The following functions are proposed for the future helper. They are not implemented in Stage 3R.

### `sw_infer_hero_criteria_profile(array $candidates, array $item = []): ?string`

Purpose:

Infer placeholder `active_criteria_profile` from candidate diagnostics, product type, or item metadata.

Initial mapping:

- `object` -> `object_only`
- `lower_body` -> `body_region_first`
- `sports_bra` -> `body_region_first`
- `upper_body` -> `product_first`
- `full_body` -> `full_outfit`
- diagnostics unavailable -> `null`

### `sw_get_hero_criteria_profile_metadata(?string $profile): array`

Purpose:

Return profile metadata for the active profile.

Possible fields:

- `face_policy`
- `subject_emphasis`
- `crop_policy`
- `score_scope`
- `pose_logic_expected`
- `object_logic_required`

### `sw_build_hero_shortlist_contract(int $itemId, array $candidates, array $item = [], array $options = []): array`

Purpose:

Build the full single-item shortlist contract:

- `item_id`
- `active_criteria_profile`
- `criteria_profile_metadata`
- `shortlist_basis`
- `shortlist_status`
- `current_hero`
- `recommended_candidates`
- `all_candidates`

### `sw_select_recommended_hero_candidates(array $candidates, int $limit = 3): array`

Purpose:

Select top candidates using current temporary rules.

Stage 3R contract rules:

- use existing rank/order only
- exclude rejected candidates where possible
- do not recalculate score
- do not use `final_advisory_score`
- return up to three candidates

### `sw_enrich_hero_shortlist_candidate(array $candidate, ?int $recommendationRank, ?string $activeCriteriaProfile, string $shortlistBasis): array`

Purpose:

Add candidate-level shortlist metadata:

- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`
- `active_criteria_profile`

### `sw_build_hero_recommendation_reason(array $candidate, string $shortlistBasis): string`

Purpose:

Produce safe recommendation reason text.

### `sw_build_hero_recommendation_confidence(array $candidate, string $shortlistBasis): string`

Purpose:

Return:

- `high`
- `medium`
- `low`
- `unavailable`

Stage 3R caution:

- never return `high` while using `legacy_rank_placeholder`
- return `medium` only when diagnostics are available and no manual-review flag exists
- return `low` when diagnostics are unavailable or warnings/manual-review flags exist

### `sw_build_current_hero_summary(array $candidates, array $recommendedCandidates): ?array`

Purpose:

Return:

- `path`
- `rank`
- `is_in_recommended_candidates`
- `current_hero_outside_top_three`
- `is_manual_override` if available

### `sw_get_hero_shortlist_status(array $recommendedCandidates, array $allCandidates): string`

Purpose:

Return:

- `ready`
- `partial`
- `unavailable`

### `sw_build_hero_shortlist_warning(string $shortlistBasis): ?string`

Purpose:

Return safe warning text.

Example:

`Temporary shortlist uses existing Hero Manager rank, not criteria-aware AI ranking.`

## Input Assumptions

Candidate input should already include:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`
- `diagnostics`

The helper should not require raw database rows.

Optional item/product metadata may be passed separately:

- item ID
- item name
- brand
- category
- product type

## Output Contract

For a single item, the helper should return the Stage 3M/3N shape:

```json
{
  "item_id": 98,
  "active_criteria_profile": "object_only",
  "criteria_profile_metadata": {},
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "ready",
  "current_hero": {},
  "recommended_candidates": [],
  "all_candidates": []
}
```

## Candidate-Level Metadata Contract

The helper should append:

- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`
- `active_criteria_profile`

These fields are additive only.

Existing candidate fields must not be removed or renamed.

## Temporary Shortlist Rules

Current rule:

Use existing candidate rank/order as a legacy placeholder.

Rules:

- no score recomputation
- no diagnostics score usage for ranking
- no criteria-aware scoring yet
- `recommended_candidates` = first three non-rejected candidates where possible
- fewer than three candidates is acceptable
- `all_candidates` always preserves full set
- current hero is not forced into `recommended_candidates`
- rejected candidates remain in `all_candidates`
- rejected candidates are excluded from `recommended_candidates` where possible

Shortlist basis:

`legacy_rank_placeholder`

## Future Criteria-Aware Ranking

The helper contract must be able to evolve.

Future versions may:

- use `criteria_profile_rank`
- use product-specific profile defaults
- apply face policy
- re-rank based on `body_region_first`, `object_only`, `full_outfit`, or `campaign_lifestyle`
- use diagnostics as ranking inputs
- accept administrator profile overrides

Stage 3R does not implement any of this.

## Recommendation Wording Rules

Allowed wording:

- `Included in temporary top-three shortlist using existing Hero Manager rank.`
- `Temporary shortlist uses existing rank, not criteria-aware AI ranking.`
- `Diagnostics available: object_bbox with high ROI confidence.`
- `Diagnostics unavailable for this image.`

Avoid wording:

- `AI selected final hero.`
- `Best image guaranteed.`
- `Criteria-aware winner.`
- `Garment detected.`
- `AI-approved final image.`

## Diagnostics Relationship

The helper may use sanitized diagnostics fields already present on the candidate.

Allowed:

- read `diagnostics.available`
- use `diagnostics.available` to improve recommendation reason
- use `diagnostics.review.needs_manual_review` to lower recommendation confidence
- use `diagnostics.roi.specificity` and `diagnostics.roi.confidence` for explanation

Not allowed:

- expose raw diagnostics
- use `final_advisory_score` for ranking at this stage
- expose pose landmarks
- expose raw bbox coordinates
- claim garment segmentation

## Current Hero Handling

If `candidate.status.is_current_hero` is true:

- record `path`
- record `rank`
- determine whether candidate is in recommended candidates
- set `current_hero_outside_top_three`
- preserve manual override status if present

Do not force the current hero into `recommended_candidates`.

## Rejection And Manual Override Handling

Helper behavior:

- rejected candidates stay in `all_candidates`
- rejected candidates are excluded from `recommended_candidates` where possible
- manual override state is preserved
- helper does not change authority state
- helper does not write to database

## Batch Endpoint Readiness

The future batch endpoint could:

1. query products
2. enumerate candidates for each item
3. attach diagnostics
4. call `sw_build_hero_shortlist_contract()`
5. return compact product records

The helper should avoid endpoint-specific assumptions so it works for both single-item and batch contexts.

## Refactor Path From Current Stage 3N Code

Logic currently in `admin/hero-candidates.php` that should later move into `inc/hero/shortlist.php`:

- active criteria profile inference
- criteria profile metadata mapping
- recommended candidates construction
- current hero summary
- recommendation reason logic
- recommendation confidence logic
- shortlist status calculation
- shortlist warning text
- candidate-level shortlist metadata enrichment

Do not move this code in Stage 3R.

## Backward Compatibility

The future helper must allow:

`admin/hero-candidates.php?item_id=98`

to remain unchanged by default.

The helper should only be used when:

- `include_shortlist=1` is requested
- future batch endpoint requests shortlist records

## Minimal Future Test Plan

Future helper implementation should test:

- one-candidate object product, `item_id=98`
- multi-candidate product, `item_id=79`
- fewer than three candidates
- zero candidates
- current hero outside top three
- rejected candidates
- diagnostics unavailable
- diagnostics available
- invalid/missing candidate fields
- default endpoint remains unchanged

## Non-Goals

Stage 3R does not include:

- helper implementation
- batch endpoint implementation
- UI
- JavaScript changes
- CSS changes
- scoring formula changes
- criteria-aware ranking
- database writes
- automatic hero replacement
- garment segmentation claims

## Recommended Stage 3S

Recommended next stage:

Stage 3S - Implement shared read-only shortlist helper and refactor the existing opt-in single-item shortlist path to use it, without changing output shape and without adding the batch endpoint yet.

Rationale:

The current opt-in single-item shortlist path is working. Extracting its logic into a helper first will make the later batch endpoint safer and avoid duplicated contract logic.

Stage 3S should be tightly scoped:

- create `inc/hero/shortlist.php`
- move shortlist-building logic out of `admin/hero-candidates.php`
- preserve default endpoint behavior
- preserve opt-in response shape
- do not add batch endpoint yet
- do not change scoring or database behavior

## Stage 3R Verdict

Proposed helper file:

`inc/hero/shortlist.php`

The helper should become a read-only contract builder that centralizes shortlist metadata construction while leaving candidate enumeration, diagnostics lookup, scoring, authority, and manual selection untouched.
