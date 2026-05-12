# Stage 3O - Shortlist Endpoint Output Review

Date: 2026-05-12

Status: review/audit only. No UI rendering was implemented.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3O reviews the opt-in shortlist endpoint output added in Stage 3N before any UI planning or rendering begins.

Stage 3N added endpoint-only shortlist metadata behind:

`include_shortlist=1`

The default endpoint response must remain backward-compatible.

## Files Reviewed

- `admin/hero-candidates.php`

No JavaScript, CSS, scoring, ranking formula, diagnostics adapter, JSON, Python, MySQL, authority, override, rejection, import/update, or generated diagnostic files were modified in this review stage.

## Syntax Check

Command:

```powershell
& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -l 'admin\hero-candidates.php'
```

Result:

No syntax errors detected.

## Test Case 1 - Default Backward-Compatible Endpoint

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=98`

Observed top-level keys:

- `item_id`
- `candidates`

Observed:

- HTTP status: `200`
- candidate count: `1`
- no top-level `recommended_candidates`
- no top-level `all_candidates`
- no top-level `active_criteria_profile`
- first candidate still contains:
  - `basename`
  - `path`
  - `source`
  - `score`
  - `analysis`
  - `status`
  - `rank`
  - `actions`
  - `diagnostics`
- candidate diagnostics remain present from Stage 3H

Verdict:

Default endpoint behavior remains backward-compatible and safe for existing `js/admin/hero.js`.

## Test Case 2 - Opt-In Object Product Endpoint

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=98&include_shortlist=1`

Observed top-level keys:

- `item_id`
- `active_criteria_profile`
- `criteria_profile_metadata`
- `shortlist_basis`
- `shortlist_status`
- `current_hero`
- `recommended_candidates`
- `all_candidates`

Observed values:

- `active_criteria_profile`: `object_only`
- `shortlist_basis`: `legacy_rank_placeholder`
- `shortlist_status`: `partial`
- `recommended_candidates`: `1`
- `all_candidates`: `1`
- candidate path: `images/brands/nike/other/600ml_waterbottle.png`
- `diagnostics.available`: `true`
- `diagnostics.product_type`: `object`
- `diagnostics.roi.specificity`: `object_bbox`
- `diagnostics.roi.is_garment_specific`: `false`
- `diagnostics.score.display_score`: `false`

Current hero summary:

```json
{
  "path": "images/brands/nike/other/600ml_waterbottle.png",
  "rank": 1,
  "is_in_recommended_candidates": true,
  "current_hero_outside_top_three": false
}
```

Recommendation wording:

`Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics available: object_bbox with high ROI confidence.`

Shortlist warning:

`Temporary shortlist uses existing rank, not criteria-aware AI ranking.`

Verdict:

The opt-in object-product contract is shaped correctly and preserves the diagnostic safety boundary.

## Test Case 3 - Opt-In Multi-Candidate Product Endpoint

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=79&include_shortlist=1`

Observed:

- HTTP status: `200`
- `active_criteria_profile`: `null`
- `shortlist_basis`: `legacy_rank_placeholder`
- `shortlist_status`: `ready`
- `recommended_candidates`: `3`
- `all_candidates`: `5`
- recommended ranks: `1,2,3`
- recommended rejected flags: `False,False,False`

First recommended candidate includes:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`
- `diagnostics`
- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`
- `active_criteria_profile`

Diagnostics fallback:

- `diagnostics.available`: `false`
- `diagnostics.status`: `no_record_for_image`
- recommendation confidence: `low`

Recommendation wording:

`Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics unavailable for this image.`

Current hero summary:

```json
{
  "path": "images/brands/adidas/women/3-stripes/booty_shorts/05.png",
  "rank": 4,
  "is_in_recommended_candidates": false,
  "current_hero_outside_top_three": true
}
```

Verdict:

The opt-in multi-candidate response behaves correctly. It uses the first three non-rejected candidates by existing rank/order, preserves all five candidates, handles missing diagnostics safely, and reports that the current hero is outside the top three without forcing it into `recommended_candidates`.

## Test Case 4 - Invalid Item Handling

Requests:

- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php`
- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=0`
- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=0&include_shortlist=1`
- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=not-a-number&include_shortlist=1`

Observed response for all:

```json
{"error":"Invalid item"}
```

HTTP status:

`200`

Verdict:

Invalid-item behavior remains acceptable. Shortlist processing does not run in a way that causes warnings or errors.

## Backward Compatibility Verdict

Verdict: accepted.

Normal endpoint calls still return:

```json
{
  "item_id": 98,
  "candidates": []
}
```

No top-level shortlist fields are added unless `include_shortlist=1` is present.

Existing `js/admin/hero.js` should not be broken by this change.

## Opt-In Contract Shape Verdict

Verdict: accepted for later UI planning.

The opt-in response includes the Stage 3M contract fields:

- `item_id`
- `active_criteria_profile`
- `criteria_profile_metadata`
- `shortlist_basis`
- `shortlist_status`
- `current_hero`
- `recommended_candidates`
- `all_candidates`

The contract clearly marks its basis as:

`legacy_rank_placeholder`

## Candidate Preservation Verdict

Verdict: accepted.

Original candidate fields are preserved:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`
- `diagnostics`

Shortlist fields are additive.

## Shortlist Derivation Verdict

Verdict: accepted with limitation.

The shortlist is derived from existing candidate order/rank only.

The code does not:

- recalculate scores
- combine `candidate.score` with `diagnostics.score.final_advisory_score`
- use `final_advisory_score` for ranking
- implement criteria-aware scoring prematurely
- change candidate rank/order

Limitation:

This is still a temporary legacy-rank placeholder, not final criteria-aware AI ranking.

## Current Hero Verdict

Verdict: accepted.

For `item_id=98`, the current hero is the only candidate and is reported as inside recommended candidates.

For `item_id=79`, the current hero is rank 4 and is reported as outside the top three. It is not forced into `recommended_candidates`.

This preserves ranking clarity.

## Rejection And Manual Override Safety Verdict

Verdict: accepted at output-review level.

Rejected candidates remain available in `all_candidates`.

Recommended candidates exclude rejected candidates where possible.

Manual override status remains part of existing candidate `status` data and is not overwritten by shortlist metadata.

No database writes occur.

## Diagnostics Preservation Verdict

Verdict: accepted.

The existing sanitized `diagnostics` object is preserved.

Endpoint output does not expose:

- raw pose landmarks
- raw bounding boxes
- alpha geometry
- `normalized_tokens`
- raw diagnostic JSON records
- implementation-only debug fields

## Garment-Segmentation Boundary Verdict

Verdict: accepted.

Output does not imply:

- garment segmentation
- garment masks
- garment-specific bounding boxes
- automated final hero selection
- AI-approved final image

Observed diagnostic object product output keeps:

`roi.is_garment_specific: false`

## Wording Safety Verdict

Verdict: accepted.

Observed wording is honest and temporary:

- `Included in temporary top-three shortlist using existing Hero Manager rank.`
- `Temporary shortlist uses existing rank, not criteria-aware AI ranking.`

No unsafe wording was found, such as:

- `AI selected final hero`
- `Best image guaranteed`
- `Criteria-aware winner`
- `Garment detected`

## Defects Found

No blocking defects were found.

## Corrections Made

No corrections were made during Stage 3O.

## Final Verdict

The opt-in shortlist endpoint output is safe for later UI planning.

The response is backward-compatible by default, opt-in only for the shortlist contract, additive to candidate records, explicit about `legacy_rank_placeholder`, and preserves the full candidate set for challenge/review mode.

## Recommended Stage 3P

Recommended next stage:

Stage 3P - Plan product-list UI for top-three display.

Stage 3P should be planning-only unless explicitly approved for code. It should decide how the Hero Manager product list or filtered list will display the top three recommended candidates per product without weakening manual editorial authority.
