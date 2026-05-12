# Stage 3Q - Batch Shortlist Endpoint Plan

Date: 2026-05-12

Status: planning and documentation only. No batch endpoint was implemented.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3Q plans a future batch shortlist endpoint to support product-list and filtered-list display of top-three hero candidates across many products.

The existing single-item endpoint is useful for contract testing:

`admin/hero-candidates.php?item_id=98&include_shortlist=1`

But the future Hero Manager product-list UI needs shortlist data for many products efficiently. Calling the single-item endpoint separately for every product would likely be slow and harder to manage.

## Stage 3Q Status

- Stage 3Q is planning/documentation only.
- No batch endpoint is implemented.
- No UI is implemented.
- No JavaScript or CSS is changed.
- No scoring/ranking formula is changed.
- No database writes are introduced.
- Manual selection remains final.

## Why A Batch Endpoint Is Needed

The future scan workflow is:

1. Product list or filtered list loads.
2. Each product displays current hero and top-three recommended candidates.
3. Administrator scans quickly.
4. Administrator opens the full candidate set only when challenging the shortlist.

This requires shortlist summaries for many products at once.

A batch endpoint should support:

- one response for a product list page
- filtered subsets
- pagination
- compact candidate summaries
- challenge links back to single-item review

## Relationship To Existing Single-Item Endpoint

Existing endpoint:

`admin/hero-candidates.php?item_id=98&include_shortlist=1`

It should remain useful for:

- single product Hero Editor review
- challenge view
- debugging one product
- all-candidates inspection

The batch endpoint should not immediately replace the single-item endpoint.

Recommended relationship:

- batch endpoint for product-list scan view
- single-item endpoint for challenge/review mode

## Endpoint Location Options

### Option A - `admin/hero-shortlists.php`

Pros:

- clear purpose
- low risk to existing endpoint
- easy to test independently
- separates batch product-list contract from single-item candidate inspection
- leaves `admin/hero-candidates.php` backward-compatible

Cons:

- new endpoint file
- may require shared helper extraction to avoid duplicated logic

### Option B - `admin/hero-candidates.php?include_shortlists=1`

Pros:

- reuses existing endpoint family
- fewer endpoint names

Cons:

- risks overloading `hero-candidates.php`
- singular `item_id` and batch list behavior become mixed
- harder to preserve simple endpoint expectations

### Option C - `admin/hero-manager-shortlists.php`

Pros:

- explicit connection to Hero Manager

Cons:

- longer name
- less reusable if other admin surfaces need the same data

### Recommendation

Use a new endpoint later:

`admin/hero-shortlists.php`

Reason:

It is clear, low-risk, separate from the existing candidate endpoint, and compatible with future Hero Manager product-list UI.

## Proposed Batch Response Shape

Future shape:

```json
{
  "status": "ready",
  "shortlist_basis": "legacy_rank_placeholder",
  "active_scope": {
    "section": "women",
    "brand": "Nike",
    "category": null,
    "limit": 25,
    "offset": 0
  },
  "products": [],
  "summary": {
    "product_count": 25,
    "ready_count": 20,
    "partial_count": 3,
    "unavailable_count": 2,
    "legacy_placeholder_count": 25
  },
  "pagination": {
    "limit": 25,
    "offset": 0,
    "total_products": 120,
    "has_more": true,
    "next_offset": 25
  }
}
```

### `status`

Overall response status.

Suggested values:

- `ready`
- `partial`
- `unavailable`
- `error`

### `shortlist_basis`

Current basis for the batch response.

Initial value:

`legacy_rank_placeholder`

This must remain explicit until criteria-aware ranking exists.

### `active_scope`

Describes the filter/search context used by the request.

Possible fields:

- `section`
- `brand`
- `category`
- `gender`
- `size_type`
- `q`
- `limit`
- `offset`

### `products`

Array of product shortlist summaries.

### `summary`

Counts useful for UI status and audit:

- product count
- ready count
- partial count
- unavailable count
- legacy placeholder count
- diagnostics missing count if useful later

### `pagination`

Pagination metadata.

## Product Record Shape

Each product record should include:

- `item_id`
- `item_name`
- `brand`
- `category` or product type where available
- `current_hero`
- `active_criteria_profile`
- `criteria_profile_metadata`
- `shortlist_basis`
- `shortlist_status`
- `recommended_candidates`
- `candidate_count`
- `diagnostics_summary`
- `challenge_endpoint`
- `hero_edit_url`
- `warnings`

Avoid including `all_candidates` in the batch response unless specifically requested.

Reason:

The batch response is for scan view. Full candidate data belongs in the single-item challenge endpoint.

## Recommended Candidates In Batch Response

Each recommended candidate should include enough for display without carrying full raw data.

Suggested fields:

- `recommendation_rank`
- `path`
- `basename`
- `source`
- `existing_score`
- `existing_rank`
- `diagnostics_summary`
- `status_summary`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`

Do not include:

- raw pose landmarks
- raw bbox coordinates
- alpha geometry
- normalized tokens
- full diagnostic records
- all score components
- all candidates

## Scope And Filter Parameters

Possible future query parameters:

- `section`
- `brand`
- `category`
- `gender`
- `size_type`
- `assignment_source`
- `q`
- `limit`
- `offset`
- `include_rejected`
- `include_diagnostics_summary`
- `criteria_profile`

Example requests:

```text
admin/hero-shortlists.php?section=women&brand=Nike&limit=25&offset=0
admin/hero-shortlists.php?section=plus_size&size_type=plus&limit=25
admin/hero-shortlists.php?q=water%20bottle&limit=10
```

These parameters are contract planning only. Stage 3Q does not implement them.

## Pagination

Pagination is important because the catalogue may grow.

The batch endpoint should not return every product and every image at once.

Suggested pagination fields:

- `limit`
- `offset`
- `total_products`
- `has_more`
- `next_offset`

Recommended defaults:

- `limit`: 25
- maximum `limit`: to be decided during implementation planning

## Performance Considerations

### Risks

- Enumerating candidates for many products may be expensive.
- Current candidate enumeration was designed for one item.
- Diagnostics lookup should not reload JSON for every product.
- Returning full candidate data could slow the admin UI.
- Image filesystem checks could become costly if added later.

### Safeguards

- Use pagination.
- Load diagnostics payload once per batch request.
- Avoid returning `all_candidates` in batch response.
- Return compact candidate summaries.
- Keep single-item endpoint for challenge view.
- Consider caching or precomputation later.

## Data Source For Product List

Future implementation should inspect these files before coding:

- `admin/hero-manager.php`
- `admin/db.php`
- `inc/hero/candidates.php`
- `admin/hero-candidates.php`

Possibly relevant if present:

- `inc/catalog-query.php`
- admin product listing/query helpers

Likely data source:

- the existing `item` table query used by Hero Manager, adapted to support filters and pagination

The batch endpoint should avoid duplicating complex product-filtering logic if reusable query helpers already exist.

## Relationship To Current Hero Manager List

Future flow:

```text
Hero Manager product list loads products
  -> batch shortlist endpoint returns top-three summaries for those products
  -> UI renders current hero plus top three
  -> "Review all images" links to single-item review/challenge view
```

The batch endpoint should support the scan view. It should not replace manual review.

## Challenge Endpoint Linkage

Each product record should include links or IDs for full review.

Possible fields:

- `challenge_endpoint`
- `hero_edit_url`
- `item_id`
- `has_full_candidate_set`

Examples:

```json
{
  "challenge_endpoint": "admin/hero-candidates.php?item_id=79&include_shortlist=1",
  "hero_edit_url": "admin/hero-edit.php?id=79",
  "has_full_candidate_set": true
}
```

Full review should use the single-item contract where `all_candidates` are available.

## Current Hero Handling

Batch response should include:

- `current_hero.path`
- `current_hero.rank` if known
- `current_hero.is_in_recommended_candidates`
- `current_hero.current_hero_outside_top_three`
- `current_hero.is_manual_override` if known

The UI needs this to show when the current hero differs from the top-three shortlist.

Do not force the current hero into `recommended_candidates`.

## Rejected And Manual Override Handling

Recommended batch behavior:

- rejected candidates normally excluded from `recommended_candidates`
- rejected candidates not returned in batch unless `include_rejected=1` or summary requires it
- manual override status surfaced in `current_hero` if applicable
- batch endpoint must not write or alter any state

Manual selection remains final.

## Diagnostics Handling

Recommended batch diagnostics:

Include compact diagnostics summary only.

Candidate-level diagnostic summary may include:

- `available`
- `product_type`
- `roi.specificity`
- `roi.confidence`
- `review.warning_count`
- `review.needs_manual_review`

Do not include:

- raw pose landmarks
- raw bbox coordinates
- alpha geometry
- normalized tokens
- raw diagnostic records
- detailed score components

Full diagnostics can remain available through the single-item challenge endpoint if needed.

## Criteria Profile Handling

For the first implementation:

- profile may be inferred from `diagnostics.product_type`
- mapping may reuse Stage 3N placeholder logic
- `shortlist_basis` remains `legacy_rank_placeholder`
- criteria-aware scoring is not active

Future:

- profile may come from product type defaults
- administrator may override profile
- endpoint may accept `criteria_profile`
- `criteria_profile_metadata` should explain profile intent

The response must not imply criteria-aware ranking until the ranking actually uses criteria.

## Safe Wording And Status Copy

Safe wording:

- Temporary shortlist using existing Hero Manager rank.
- Criteria-aware ranking not yet active.
- Diagnostics unavailable; manual review remains available.
- Current hero is outside the temporary top-three shortlist.

Avoid:

- AI selected final hero.
- Best image guaranteed.
- Garment detected.
- Criteria-aware winner.
- AI-approved final hero.

## Error And Failure Behavior

### No Matching Products

Return:

- `status: ready`
- `products: []`
- summary counts as zero

### Invalid Filters

Return a safe error or normalized empty result.

Do not emit PHP warnings/notices into JSON.

### Database Query Failure

Return:

- `status: error`
- safe message

Do not expose credentials or SQL internals.

### Missing Diagnostics

Return products and candidates where possible.

Use diagnostics summary:

- `available: false`
- safe message

### Unsupported Diagnostics Schema

Return shortlist using legacy rank if possible.

Mark diagnostics unavailable.

### No Candidates For Product

Return product with:

- `shortlist_status: unavailable`
- `recommended_candidates: []`
- `candidate_count: 0`

### Fewer Than Three Candidates

Return available candidates.

Use:

- `shortlist_status: partial`

### All Candidates Rejected

Return:

- `recommended_candidates: []`
- `shortlist_status: unavailable` or `partial`
- warning requiring review

Do not change rejection state.

## Backward Compatibility

The batch endpoint should not break:

- `admin/hero-candidates.php` default behavior
- `admin/hero-candidates.php?include_shortlist=1`
- existing `js/admin/hero.js`
- manual Hero Editor workflows

The batch endpoint should be additive.

## Security And Robustness

Future implementation should:

- return JSON only
- validate `limit` and `offset`
- validate filter parameters
- avoid exposing filesystem paths beyond existing project-relative image paths
- avoid running Python from PHP
- avoid database writes
- avoid exposing raw diagnostics
- escape output in UI later

## Implementation Options For Later Stage 3R

### Option A - Loop Over Products And Reuse Candidate Enumeration

Pros:

- fastest to implement
- reuses existing behavior

Cons:

- may duplicate shortlist-building logic
- may be slow if not paginated

### Option B - Refactor Shared Shortlist Helper First

Pros:

- avoids duplication
- lets single-item and batch endpoints share contract logic
- easier to test

Cons:

- one additional planning/implementation step

### Option C - Create `inc/hero/shortlist.php`

Pros:

- clear shared read-only helper
- can contain shortlist response construction
- can serve both `hero-candidates.php` and future `hero-shortlists.php`

Cons:

- must be carefully scoped to avoid touching scoring/write paths

### Option D - Keep Stage 3R Minimal And Endpoint-Only

Pros:

- moves toward working output quickly

Cons:

- higher risk of duplicated logic and later cleanup

### Recommendation

Design a shared read-only helper before implementing the batch endpoint.

Likely future file:

`inc/hero/shortlist.php`

Do not create it in Stage 3Q.

## Example A - Object Product

Compact example for 600ml Water Bottle:

```json
{
  "item_id": 98,
  "item_name": "600ml Water Bottle",
  "brand": "Nike",
  "active_criteria_profile": "object_only",
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "partial",
  "current_hero": {
    "path": "images/brands/nike/other/600ml_waterbottle.png",
    "rank": 1,
    "is_in_recommended_candidates": true
  },
  "recommended_candidates": [
    {
      "recommendation_rank": 1,
      "path": "images/brands/nike/other/600ml_waterbottle.png",
      "existing_rank": 1,
      "existing_score": 0,
      "diagnostics_summary": {
        "available": true,
        "product_type": "object",
        "roi_specificity": "object_bbox",
        "roi_confidence": "high"
      },
      "recommendation_confidence": "medium",
      "shortlist_warning": "Temporary shortlist uses existing rank, not criteria-aware AI ranking."
    }
  ],
  "candidate_count": 1,
  "challenge_endpoint": "admin/hero-candidates.php?item_id=98&include_shortlist=1"
}
```

## Example B - Multi-Candidate Apparel Product

Compact example for Booty Shorts:

```json
{
  "item_id": 79,
  "item_name": "Booty Shorts (3-Stripes)",
  "brand": "Adidas",
  "active_criteria_profile": "body_region_first",
  "shortlist_basis": "legacy_rank_placeholder",
  "shortlist_status": "ready",
  "current_hero": {
    "path": "images/brands/adidas/women/3-stripes/booty_shorts/05.png",
    "rank": 4,
    "is_in_recommended_candidates": false,
    "current_hero_outside_top_three": true
  },
  "recommended_candidates": [
    {
      "recommendation_rank": 1,
      "path": "images/brands/adidas/women/3-stripes/booty_shorts/02.png",
      "existing_rank": 1,
      "existing_score": 75,
      "diagnostics_summary": {
        "available": false
      },
      "recommendation_confidence": "low",
      "shortlist_warning": "Temporary shortlist uses existing rank, not criteria-aware AI ranking."
    }
  ],
  "candidate_count": 5,
  "challenge_endpoint": "admin/hero-candidates.php?item_id=79&include_shortlist=1"
}
```

## Non-Goals

Stage 3Q does not include:

- batch endpoint implementation
- UI implementation
- JavaScript changes
- CSS changes
- criteria-aware scoring
- scoring/ranking formula changes
- database writes
- automatic hero replacement
- garment segmentation claims

## Recommended Stage 3R

Recommended next stage:

Stage 3R - Design shared shortlist helper contract.

Rationale:

The single-item opt-in endpoint and future batch endpoint should share shortlist construction logic. A read-only helper, likely `inc/hero/shortlist.php`, would reduce duplication and keep the future batch endpoint safer.

Stage 3R should remain planning/documentation unless explicitly approved for code.

## Stage 3Q Verdict

Recommended batch endpoint name:

`admin/hero-shortlists.php`

Recommended architecture:

- create a shared read-only shortlist helper later
- keep single-item endpoint for challenge/review
- use batch endpoint for product-list scan view
- return compact summaries, not full candidate sets
- preserve manual editorial authority
