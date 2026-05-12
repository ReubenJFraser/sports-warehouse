# Stage 3M - Shortlist Endpoint Contract

Date: 2026-05-12

Status: planning and documentation only. No endpoint behavior was changed.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3M designs the future shortlist endpoint contract for AI-led top-three hero candidate review.

The contract must support two related workflows:

### A. Normal Scan Workflow

The Hero Manager product list or filtered product list shows the top three recommended candidates per product.

The administrator can scan quickly across products without opening every image set.

### B. Challenge Workflow

When the administrator challenges the shortlist, the full candidate set remains available with:

- existing rankings
- diagnostics
- warnings
- manual selection status
- rejection status
- current hero status
- action availability

The shortlist is not a replacement for manual review. It is the first-pass review surface.

## Stage 3M Status

- Stage 3M is planning/documentation only.
- No endpoint behavior is changed.
- No shortlist logic is implemented.
- No UI rendering is added.
- No scoring formulas are changed.
- No database writes are introduced.
- Manual selection remains final.

## Proposed Endpoint Response Shape

Future response shape:

```json
{
  "item_id": 98,
  "active_criteria_profile": "object_only",
  "criteria_profile_metadata": {},
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "legacy_placeholder",
  "current_hero": {},
  "recommended_candidates": [],
  "all_candidates": []
}
```

### `item_id`

The product/item ID being reviewed.

### `active_criteria_profile`

The profile used to interpret "best hero image" for the current item.

Examples:

- `object_only`
- `body_region_first`
- `full_outfit`
- `product_first`
- `campaign_lifestyle`

The first implementation may use inferred or placeholder values. The contract should still include this field because criteria-aware ranking depends on it.

### `criteria_profile_metadata`

Metadata describing the active profile.

Future shape:

```json
{
  "face_policy": "optional",
  "subject_emphasis": "object",
  "crop_policy": "strict",
  "score_scope": "criteria_profile_specific",
  "pose_logic_expected": false,
  "object_logic_required": true
}
```

This metadata is not implemented yet, but the contract should leave room for it.

### `shortlist_basis`

Explains how the shortlist was produced.

Initial temporary value:

`legacy_rank_placeholder`

Possible future values:

- `legacy_rank_placeholder`
- `diagnostics_supported_rank`
- `criteria_profile_rank`
- `manual_override`
- `unavailable`

### `shortlist_status`

Explains whether the shortlist is usable and under what conditions.

Suggested values:

- `ready`
- `partial`
- `unavailable`
- `legacy_placeholder`
- `criteria_profile_missing`
- `diagnostics_missing`

### `current_hero`

Optional top-level current hero summary.

Purpose:

Show whether the current hero differs from the shortlist without forcing the current hero into the recommended ranking.

Possible fields:

```json
{
  "path": "images/example/01.png",
  "is_in_top_three": false,
  "current_hero_outside_top_three": true,
  "source": "manual_override"
}
```

### `recommended_candidates`

The top-three shortlist for normal product-list scanning.

### `all_candidates`

The full candidate set for challenge/review mode.

## Recommended Candidates Array

`recommended_candidates` should contain the top-three shortlist.

Each recommended candidate should include:

- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `path`
- `basename`
- `source`
- `existing_score`
- `existing_rank`
- `status`
- `actions`
- `diagnostics`

Recommended candidates should not remove or rename existing candidate data. They present a shortlist view of selected candidate records.

Example candidate:

```json
{
  "recommendation_rank": 1,
  "recommendation_reason": "Included in temporary top-three shortlist using existing Hero Manager rank.",
  "recommendation_confidence": "low",
  "shortlist_basis": "legacy_rank_placeholder",
  "path": "images/brands/example/product/01.png",
  "basename": "01.png",
  "source": "chosen",
  "existing_score": 75,
  "existing_rank": 1,
  "status": {},
  "actions": {},
  "diagnostics": {}
}
```

## All Candidates Array

`all_candidates` should preserve the full candidate set for challenge/review mode.

It should preserve:

- all original candidates from `sw_enumerate_scored_candidates()`
- existing rank
- existing score
- analysis
- status
- actions
- diagnostics
- current hero status
- manual override status
- rejection status

This is essential because the top-three shortlist is not a replacement for manual review.

## Candidate-Level Shortlist Metadata

Recommended additive fields:

- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`
- `active_criteria_profile`

Recommended direction:

Use both:

- `recommended_candidates` as the normal-view shortlist
- `all_candidates` with additive shortlist flags for challenge-view continuity

This lets the product-list UI consume a clear top-three array while the challenge view can still show which full-set candidates were shortlisted.

## Temporary Shortlist Basis

Stage 3K concluded that current rank is only a legacy-rank placeholder.

Therefore the first shortlist contract must use:

`shortlist_basis: "legacy_rank_placeholder"`

Meaning:

- the first shortlist may use existing rank as temporary ordering
- this must not be described as final criteria-aware AI ranking
- UI wording must avoid overclaiming
- criteria-aware ranking still needs later work

Safe wording:

- "Included in temporary top-three shortlist using existing Hero Manager rank."
- "Ranked #1 by existing Hero Manager score."
- "Diagnostics available: object_bbox, high ROI confidence."
- "Diagnostics unavailable; included by existing candidate rank only."

Unsafe wording:

- "AI selected final hero."
- "Garment detected."
- "Best image guaranteed."
- "AI-approved hero image."

## Active Criteria Profile

The contract should include `active_criteria_profile` even if the first implementation uses a placeholder or inferred value.

Suggested inferred defaults:

- object products: `object_only`
- water bottles, balls, helmets, shoes, backpacks, gloves: `object_only`
- leggings, shorts, sports bras, crop tops: `body_region_first`
- tracksuits, sets, playsuits, bodysuits: `full_outfit`
- hoodies, jackets, tops: `product_first`
- campaign/editorial contexts: `campaign_lifestyle`, only when deliberately selected later

If no profile can be inferred safely, use:

`active_criteria_profile: null`

and:

`shortlist_status: "criteria_profile_missing"`

## Criteria Profile Metadata

Future metadata may include:

```json
{
  "face_policy": "optional",
  "subject_emphasis": "object",
  "crop_policy": "strict",
  "score_scope": "criteria_profile_specific",
  "pose_logic_expected": false,
  "object_logic_required": true
}
```

This should eventually support explanation, UI wording, and criteria-aware ranking.

For a first placeholder shortlist, metadata may be empty or marked as not implemented.

## Recommendation Reason

`recommendation_reason` should explain why a candidate appears in the shortlist.

Temporary reasons should stay modest:

- "Ranked #1 by existing Hero Manager score."
- "Included in temporary top-three shortlist using legacy rank."
- "Diagnostics available: object_bbox, high ROI confidence."
- "Diagnostics unavailable; included by existing candidate rank only."

Do not use wording that suggests final authority or unsupported detection.

Avoid:

- "AI selected final hero."
- "Garment detected."
- "Best image guaranteed."

## Recommendation Confidence

Suggested values:

- `high`
- `medium`
- `low`
- `unavailable`

Meaning:

`recommendation_confidence` reflects confidence in the shortlist recommendation under the current basis. It does not mean absolute visual truth.

For `legacy_rank_placeholder`:

- use `medium` if diagnostics are available and no warnings exist
- use `low` if diagnostics are unavailable
- use `medium` or `low` if product type clearly requires criteria-aware interpretation that does not exist yet

Do not present confidence as final editorial correctness.

## Shortlist Status Behavior

### Fewer Than Three Candidates

Return all available candidates in `recommended_candidates`.

Use:

`shortlist_status: "partial"`

or:

`shortlist_status: "legacy_placeholder"`

with a `shortlist_warning`.

### Diagnostics Missing

Still return candidates if legacy rank exists.

Use:

`shortlist_basis: "legacy_rank_placeholder"`

and candidate-level warning:

`Diagnostics unavailable; included by existing candidate rank only.`

### Active Criteria Profile Unavailable

Return a placeholder shortlist only if legacy rank exists.

Use:

`shortlist_status: "criteria_profile_missing"`

### All Candidates Rejected

Preserve all candidates in `all_candidates`.

Recommended behavior:

- exclude rejected candidates from `recommended_candidates` by default
- if all candidates are rejected, return `recommended_candidates: []`
- set `shortlist_status: "unavailable"` or `partial`
- include a warning that all candidates are rejected

### Current Hero Outside Top Three

Do not force current hero into `recommended_candidates`.

Instead include:

```json
{
  "current_hero_outside_top_three": true
}
```

inside top-level `current_hero`, or include a top-level shortlist warning.

This allows the UI to show that the current hero differs from the AI shortlist without corrupting ranking.

## Manual Override And Rejection Handling

The contract must preserve:

- `is_manual_override`
- `is_rejected`
- `rejection_count`
- `can_select`
- `can_reject`

Recommended behavior:

- rejected candidates remain in `all_candidates`
- rejected candidates are normally excluded from `recommended_candidates`
- manual override is surfaced clearly
- manual override is not overwritten by recommendation metadata
- manual selection remains final authority

## Relationship To Existing Diagnostics

Sanitized diagnostics remain useful inside the shortlist contract.

Useful fields:

- `diagnostics.available`
- `diagnostics.product_type`
- `diagnostics.roi.specificity`
- `diagnostics.roi.confidence`
- `diagnostics.review.needs_manual_review`
- `diagnostics.review.warning_count`
- `diagnostics.review.category_warning_count`
- `diagnostics.score.display_score`

Clarifications:

- diagnostics may support recommendation reasons
- diagnostics should not replace existing score yet
- `final_advisory_score` should remain hidden by default
- diagnostics must not imply garment segmentation

## Backward Compatibility

Recommended direction:

Do not replace the current endpoint response shape immediately.

Safer future options:

### Option A - Opt-In Query Parameter

Add optional shortlist output to `admin/hero-candidates.php`, for example:

`admin/hero-candidates.php?item_id=98&include_shortlist=1`

Pros:

- keeps existing endpoint
- avoids breaking current `js/admin/hero.js`
- easy to compare old and enriched output
- useful for incremental testing

Cons:

- endpoint may become overloaded if batch/list workflow is added later

### Option B - New Single-Item Endpoint

Create a new endpoint later, such as:

`admin/hero-shortlist.php?item_id=98`

Pros:

- clean contract boundary
- avoids changing existing candidate endpoint behavior

Cons:

- duplicates some candidate-loading concerns
- still solves only one product at a time

### Option C - Batch Shortlist Endpoint

Create a batch endpoint later for product-list use.

Pros:

- better fit for product list / filtered list workflow
- avoids many per-product endpoint requests

Cons:

- larger implementation
- needs stronger performance planning

Stage 3M recommendation:

Use an opt-in query parameter for the first single-item contract prototype, but plan a batch endpoint before product-list UI implementation.

## Product-List Versus Single-Item Endpoint

The intended product-list workflow eventually needs efficient access to top-three candidates across many products.

Calling `hero-candidates.php` once per product may become inefficient.

Future architecture may need:

- a single-item shortlist endpoint for Hero Editor and targeted testing
- a batch shortlist endpoint for Hero Manager list view or filtered list view

Recommended sequence:

1. Prototype single-item shortlist contract behind an opt-in parameter.
2. Review the contract shape.
3. Design batch shortlist endpoint before product-list UI rendering.

## Example A - Water Bottle, Object-Only

Example context:

- item: 600ml Water Bottle
- profile: `object_only`
- candidates: one
- diagnostics available

```json
{
  "item_id": 98,
  "active_criteria_profile": "object_only",
  "criteria_profile_metadata": {
    "face_policy": "avoid",
    "subject_emphasis": "object",
    "crop_policy": "strict",
    "score_scope": "criteria_profile_specific",
    "pose_logic_expected": false,
    "object_logic_required": true
  },
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "legacy_placeholder",
  "current_hero": {
    "path": "images/brands/nike/other/600ml_waterbottle.png",
    "is_in_top_three": true,
    "current_hero_outside_top_three": false,
    "source": "auto_or_existing"
  },
  "recommended_candidates": [
    {
      "recommendation_rank": 1,
      "recommendation_reason": "Included in temporary shortlist by existing rank. Diagnostics identify object_bbox with high ROI confidence.",
      "recommendation_confidence": "medium",
      "shortlist_basis": "legacy_rank_placeholder",
      "path": "images/brands/nike/other/600ml_waterbottle.png",
      "basename": "600ml_waterbottle.png",
      "source": "chosen",
      "existing_score": 0,
      "existing_rank": 1,
      "status": {},
      "actions": {},
      "diagnostics": {
        "available": true,
        "product_type": "object",
        "roi": {
          "specificity": "object_bbox",
          "confidence": "high",
          "is_garment_specific": false
        },
        "score": {
          "display_score": false
        }
      }
    }
  ],
  "all_candidates": []
}
```

Note:

The existing score is `0` under the legacy headroom ranking, so UI wording must avoid pretending this is final criteria-aware object ranking.

## Example B - Booty Shorts, Body-Region Placeholder

Example context:

- item: Booty Shorts
- profile: `body_region_first`
- candidates: five
- diagnostics unavailable
- shortlist basis: legacy rank placeholder

```json
{
  "item_id": 79,
  "active_criteria_profile": "body_region_first",
  "criteria_profile_metadata": {},
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "legacy_placeholder",
  "current_hero": {
    "path": null,
    "is_in_top_three": null,
    "current_hero_outside_top_three": false,
    "source": null
  },
  "recommended_candidates": [
    {
      "recommendation_rank": 1,
      "recommendation_reason": "Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics unavailable.",
      "recommendation_confidence": "low",
      "shortlist_basis": "legacy_rank_placeholder",
      "path": "images/brands/adidas/women/3-stripes/booty_shorts/02.png",
      "basename": "02.png",
      "source": "thumbnail",
      "existing_score": 75,
      "existing_rank": 1,
      "status": {},
      "actions": {},
      "diagnostics": {
        "available": false,
        "status": "no_record_for_image",
        "message": "Diagnostics unavailable. Manual selection remains available."
      }
    }
  ],
  "all_candidates": []
}
```

Note:

This is a placeholder body-region shortlist. It is not yet criteria-aware ranking.

## Risks And Safeguards

### Risks

- Legacy rank mistaken for final AI ranking.
- Diagnostics missing for many candidates.
- Current Hero Manager score overvalues face/headroom.
- Criteria profiles are not implemented.
- UI treats recommendation as final authority.
- Endpoint response shape breaks existing JavaScript.
- Batch use may be inefficient.
- Current hero may be outside shortlist.
- Rejected images may accidentally appear in shortlist.
- `final_advisory_score` may be confused with current score.

### Safeguards

- Use explicit `shortlist_basis`.
- Use careful `recommendation_reason` wording.
- Keep `all_candidates`.
- Add fields append-only.
- Avoid UI rendering until later.
- Do not write to the database.
- Do not change scoring.
- Preserve manual override.
- Keep `final_advisory_score` hidden by default.

## Recommended Stage 3N

Recommended next stage:

Stage 3N - Implement endpoint-only shortlist metadata behind an opt-in query parameter, without JavaScript or UI rendering.

Suggested first implementation:

`admin/hero-candidates.php?item_id=98&include_shortlist=1`

This keeps the existing endpoint backward-compatible and allows the contract to be tested on single items.

Before product-list UI work, plan a future batch shortlist endpoint to avoid one request per product.

## Non-Goals

Stage 3M does not include:

- implementation
- UI rendering
- JavaScript changes
- CSS changes
- criteria engine
- scoring formula changes
- database writes
- automatic hero replacement
- garment segmentation claims

## Stage 3M Verdict

The shortlist contract should preserve both normal scan and challenge workflows.

Recommended contract direction:

- add a top-level `recommended_candidates` array
- keep a full `all_candidates` array
- include `active_criteria_profile`
- include explicit `shortlist_basis`
- treat first implementation as `legacy_rank_placeholder`
- use an opt-in parameter for initial single-item testing
- plan a batch endpoint before product-list UI rendering
